<?php

class SuperAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }
    public function index()
    {
        // Load pinned internal posts for news feed
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Models/User.php';
        $pinned = InternalPost::getPinned(6);
        // expand each to include images (getById adds images) and author name
        $pinnedFull = [];
        foreach ($pinned as $p) {
            $full = InternalPost::getById((int)$p['id']);
            if ($full) {
                $author = null;
                if (!empty($full['user_id'])) {
                    $author = User::findById((int)$full['user_id']);
                }
                $full['author_name'] = $author['ho_ten'] ?? $author['name'] ?? 'Big Housing Land';

                $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
                $authorAvatar = $author['avatar'] ?? null;
                if (!empty($authorAvatar)) {
                    if (stripos($authorAvatar, 'http') === 0) {
                        $avatarSrc = $authorAvatar;
                    } else {
                        $p = ltrim($authorAvatar, '/');
                        if (stripos($p, 'uploads/') === 0) {
                            $avatarSrc = rtrim(BASE_URL, '/') . '/' . $p;
                        } else {
                            $avatarSrc = rtrim(BASE_URL, '/') . '/uploads/' . $p;
                        }
                    }
                }
                $full['author_avatar_src'] = $avatarSrc;

                $pinnedFull[] = $full;
            }
        }

        $this->view('superadmin/home', ['pinnedPosts' => $pinnedFull]);
    }

    public function logout()
    {
        unset($_SESSION['user']);
        header('Location: ' . BASE_URL . '/login');
        exit;
    }


    public function profile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $this->view('superadmin/profile', ['user' => $user]);
    }

    public function detailprofile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        $userId = $_GET['id'] ?? null;
        $user = null;

        if ($userId) {
            // viewing another user's profile
            $user = User::findById((int)$userId);
        } else {
            // default to session user
            if (!empty($sessionUser['id'])) {
                $user = User::findById($sessionUser['id']);
            }
            // Fallback to session user if DB lookup fails
            if (!$user) $user = $sessionUser;
        }

        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/home'));
            exit;
        }

        // Determine if session user has already rated (usually viewing own profile; disallow self-rating)
        $hasRated = false;
        $sessionId = $sessionUser['id'] ?? 0;
        $viewedId = $user['id'] ?? 0;
        if ($sessionId && $viewedId && $sessionId !== $viewedId) {
            $db = \Database::connect();
            $chk = $db->prepare('SELECT id FROM user_ratings WHERE rater_id = ? AND rated_user_id = ? LIMIT 1');
            $chk->execute([$sessionId, $viewedId]);
            if ($chk->fetch()) $hasRated = true;
        }

        $this->view('superadmin/detailprofile', ['user' => $user, 'sessionUser' => $sessionUser, 'has_rated' => $hasRated]);
    }

    public function editprofile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        if (empty($sessionUser['id'])) {
            // $_SESSION['error'] = 'Người dùng không tồn tại';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $id = $sessionUser['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'nam_sinh' => trim($_POST['nam_sinh'] ?? ''),
                'so_cccd' => trim($_POST['so_cccd'] ?? ''),
                'dia_chi' => trim($_POST['dia_chi'] ?? ''),
            ];

            if (empty($data['so_dien_thoai'])) {
                // $_SESSION['error'] = 'Số điện thoại là bắt buộc';
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }

            $existing = User::findByPhone($data['so_dien_thoai']);
            if ($existing && !empty($existing['id']) && $existing['id'] != $id) {
                // $_SESSION['error'] = 'Số điện thoại đã được sử dụng';
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }

            // Handle avatar upload if present
            $avatarSavedPath = null;
            if (!empty($_FILES['avatar']) && isset($_FILES['avatar']['error']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $tmpName = $file['tmp_name'];
                $orig = basename($file['name']);
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed)) {
                    $uploadDir = 'uploads/avatars/' . $id . '/';
                    $absDir = __DIR__ . '/../../public/' . $uploadDir;
                    if (!is_dir($absDir)) mkdir($absDir, 0755, true);
                    $newName = uniqid('av_') . '.' . $ext;
                    if (move_uploaded_file($tmpName, $absDir . $newName)) {
                        $avatarSavedPath = 'avatars/' . $id . '/' . $newName;
                    }
                }
            }

            $ok = User::update($id, $data);
            if ($ok) {
                if ($avatarSavedPath) {
                    User::updateAvatar($id, $avatarSavedPath);
                }
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                header('Location: ' . BASE_URL . '/superadmin/detailprofile');
                exit;
            } else {
                if ($avatarSavedPath) {
                    $f = __DIR__ . '/../../public/uploads/' . $avatarSavedPath;
                    if (file_exists($f)) @unlink($f);
                }
                header('Location: ' . BASE_URL . '/superadmin/editprofile');
                exit;
            }
        }

        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        $this->view('superadmin/editprofile', ['user' => $user]);
    }

    public function changepassword()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        if (empty($sessionUser['id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $id = $sessionUser['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($current) || empty($new) || empty($confirm)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ các trường';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            if ($new !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            $user = User::findById($id);
            if (!$user || !password_verify($current, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $ok = User::updatePasswordById($id, $hash);

            if ($ok) {
                // Refresh session user
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu mật khẩu mới';
                header('Location: ' . BASE_URL . '/superadmin/changepassword');
                exit;
            }
        }

        // Prepare user data for the view
        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        // Map role to human readable label
        $roleRaw = strtolower($user['loai_tai_khoan'] ?? $user['quyen'] ?? '');
        $roleMap = [
            'nhan_vien' => 'Nhân viên',
            'quan_ly' => 'Cấp quản lý',
            'admin' => 'Quản trị',
            'super_admin' => 'Quản trị'
        ];
        $displayRole = $roleMap[$roleRaw] ?? $roleRaw;

        // Office badge - prefer 'phong_ban' then 'dia_chi'
        $officeBadge = $user['phong_ban'] ?? $user['dia_chi'] ?? '';

        $this->view('superadmin/changepassword', [
            'user' => $user,
            'displayRole' => $displayRole,
            'officeBadge' => $officeBadge
        ]);
    } 
    public function delete()
    {
        // Đặt header trả về JSON
        header('Content-Type: application/json');

        // Chỉ chấp nhận phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
            return;
        }

        // Lấy ID từ request
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        // --- BẮT ĐẦU PHẦN XỬ LÝ DATABASE ---
        // Bạn cần thay thế phần này bằng code thực tế của dự án bạn
        // Ví dụ với CodeIgniter 4:
        /*
        $reportModel = new \App\Models\ReportModel();
        if ($reportModel->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa báo cáo này']);
        }
        */

        // Ví dụ với PHP thuần / PDO:
        /*
        global $db; // Biến kết nối CSDL của bạn
        $stmt = $db->prepare("DELETE FROM reports WHERE id = ?");
        if ($stmt->execute([$id])) {
             echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Lỗi CSDL']);
        }
        */
        
        // Giả lập thành công để bạn test giao diện (Xóa dòng này khi code thật)
        $deleted = true; 
        // --- KẾT THÚC PHẦN XỬ LÝ DATABASE ---

        if ($deleted) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại']);
        }
        exit;
    }
}
