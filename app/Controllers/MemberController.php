<?php

class MemberController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Chỉ Super Admin mới có quyền truy cập các chức năng này
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function owner()
    {
        // fetch query params for pagination / search
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $status = null;
        if (isset($_GET['status']) && $_GET['status'] !== 'all' && $_GET['status'] !== '') {
            $s = $_GET['status'];
            if ($s === 'hoạt động') $status = 1;
            elseif ($s === 'tạm dừng') $status = 2;
            elseif ($s === 'chờ duyệt') $status = 0;
            else $status = (int)$s; // Fallback nếu truyền số trực tiếp
        }

        $total = User::countByRole('admin', $search, $status);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $users = User::getByRole('admin', $perPage, $offset, $search, $status);

        $this->view('superadmin/management-owner', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status
        ]);
    }

    public function addpersonnel()
    {
        // If POST, process create
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // simple CSRF check
            $token = $_POST['_csrf'] ?? null;
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            $data = [];
            $data['ho_ten'] = trim($_POST['ho_ten'] ?? '');
            $data['so_dien_thoai'] = trim($_POST['so_dien_thoai'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['nam_sinh'] = trim($_POST['nam_sinh'] ?? null);
            $data['so_cccd'] = trim($_POST['so_cccd'] ?? null);
            $data['phong_ban'] = trim($_POST['phong_ban'] ?? null);
            $data['quyen'] = ($_POST['quyen'] ?? 'user') === 'admin' ? 'admin' : 'user';

            // ===== TỰ ĐỘNG TẠO MÃ NHÂN SỰ (DC.../DK...) THEO THỨ TỰ TĂNG DẦN =====
            // Prefix: 'DC' cho 'admin' (Đầu chủ), 'DK' cho 'user' (Đầu khách)
            $prefix = ($data['quyen'] === 'admin') ? 'DC' : 'DK';
            require_once __DIR__ . '/../Models/User.php';
            if ($prefix) {
                $db = \Database::connect();
                // Use SQL to compute the numeric max suffix to avoid ordering issues
                $sql = "SELECT MAX(CAST(SUBSTRING(ma_nhan_su, CHAR_LENGTH(:pr)+1) AS UNSIGNED)) AS maxnum FROM users WHERE ma_nhan_su LIKE :prlike";
                $stmt = $db->prepare($sql);
                $stmt->execute([':pr' => $prefix, ':prlike' => $prefix . '%']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $maxnum = $row && $row['maxnum'] !== null ? (int)$row['maxnum'] : 0;
                $nextNum = $maxnum + 1;
                // Zero-pad to 6 digits: DC000001, DK000123, etc.
                $data['ma_nhan_su'] = $prefix . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            } else {
                $data['ma_nhan_su'] = null;
            }
            $data['dia_chi'] = trim($_POST['dia_chi'] ?? null);
            // Map referral input to DB field `ma_gioi_thieu` (form uses 'nguoi_gioi_thieu')
            $ref = trim($_POST['ma_gioi_thieu'] ?? $_POST['nguoi_gioi_thieu'] ?? '');
            $data['ma_gioi_thieu'] = $ref !== '' ? $ref : null;
            // Vị trí (vi_tri) mapping: map string value from form to integer stored in DB
            $data['vi_tri'] = $_POST['vi_tri'] ?? null;
            switch ($data['vi_tri']) {
                case 'kho_nha_dat':
                    $data['vi_tri'] = 0;
                    break;
                case 'kho_nha_cho_thue':
                    $data['vi_tri'] = 1;
                    break;
                case 'ca_hai':
                    $data['vi_tri'] = 2;
                    break;
                default:
                    $data['vi_tri'] = null;
            }
            $password = $_POST['password'] ?? '';

            // Basic validation
            if (empty($data['ho_ten']) || empty($data['so_dien_thoai']) || empty($password)) {
                $_SESSION['error'] = 'Vui lòng điền tên, số điện thoại và mật khẩu.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // check phone uniqueness
            require_once __DIR__ . '/../Models/User.php';
            if (User::findByPhone($data['so_dien_thoai'])) {
                $_SESSION['error'] = 'Số điện thoại đã được sử dụng.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // Normalize so_cccd: empty -> NULL and check uniqueness
            if (isset($data['so_cccd'])) {
                $data['so_cccd'] = trim($data['so_cccd']);
                if ($data['so_cccd'] === '') {
                    $data['so_cccd'] = null;
                } else {
                    if (User::findByCCCD($data['so_cccd'])) {
                        $_SESSION['error'] = 'Số CCCD đã được sử dụng.';
                        header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                        exit;
                    }
                }
            }

            // Handle uploaded CCCD image (optional)
            if (!empty($_FILES['anh_cccd']) && isset($_FILES['anh_cccd']['error']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anh_cccd'];
                $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
                if ($file['size'] > 3 * 1024 * 1024) {
                    $_SESSION['error'] = 'Kích thước ảnh CCCD không được vượt quá 3MB.';
                    header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                    exit;
                }
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowedMime, true)) {
                    $_SESSION['error'] = 'Định dạng ảnh CCCD không hợp lệ (chỉ JPG/PNG/WEBP).';
                    header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                    exit;
                }

                $uploadsDir = realpath(__DIR__ . '/../../public');
                if ($uploadsDir === false) $uploadsDir = __DIR__ . '/../../public';
                $uploadsDir = $uploadsDir . '/uploads';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploadsDir . '/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $_SESSION['error'] = 'Không lưu được ảnh CCCD. Vui lòng thử lại.';
                    header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                    exit;
                }

                // Store a web-relative path (full_public_url helper will map it)
                $data['anh_cccd'] = 'uploads/' . $filename;
            }

            // ma_nhan_su được tự sinh phía trên và đã kiểm tra trùng lặp

            $data['password'] = password_hash($password, PASSWORD_DEFAULT);

            try {
                $ok = User::createWithRole($data);
            } catch (PDOException $e) {
                // For debugging: capture DB error message to session temporarily
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu: ' . $e->getMessage();
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }
            if ($ok) {
                $_SESSION['success'] = 'Tạo nhân sự thành công.';
                header('Location: ' . BASE_URL . '/superadmin/management-owner');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }
        }

        $this->view('superadmin/add-personnel');
    }

    public function updatepersonnel()
    {
        // Expect an id via query string: /superadmin/update-personnel?id=123
        $id = $_GET['id'] ?? null;
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/management-owner');
            exit;
        }

        require_once __DIR__ . '/../Models/User.php';

        // If POST: handle update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            $token = $_POST['_csrf'] ?? null;
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
                exit;
            }

            $data = [];
            $data['ho_ten'] = trim($_POST['ho_ten'] ?? '');
            $data['so_dien_thoai'] = trim($_POST['so_dien_thoai'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['nam_sinh'] = trim($_POST['nam_sinh'] ?? '');

            // Nếu so_cccd rỗng thì gán bằng NULL để tránh lỗi Duplicate entry
            $data['so_cccd'] = trim($_POST['so_cccd'] ?? '');
            if ($data['so_cccd'] === '') $data['so_cccd'] = null;

            $data['phong_ban'] = trim($_POST['phong_ban'] ?? null);
            $data['ma_nhan_su'] = trim($_POST['ma_nhan_su'] ?? null);
            $data['ma_gioi_thieu'] = trim($_POST['ma_gioi_thieu'] ?? null);
            $data['link_fb'] = trim($_POST['link_fb'] ?? null);
            $data['dia_chi'] = trim($_POST['dia_chi'] ?? null);
            $data['quyen'] = trim($_POST['quyen'] ?? 'user');
            // Nhận giá trị trạng thái (0: Chờ duyệt, 1: Hoạt động, 2: Tạm dừng)
            $data['trang_thai'] = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 0;
            $data['vi_tri'] = isset($_POST['vi_tri']) && $_POST['vi_tri'] !== '' ? (int)$_POST['vi_tri'] : null;

            // Handle optional password change
            $newPassword = $_POST['password'] ?? '';

            // Preserve existing anh_cccd unless a new file is uploaded
            $existingUser = User::findById((int)$id);
            $data['anh_cccd'] = $existingUser['anh_cccd'] ?? null;

            // Handle file upload (anh_cccd) when provided
            if (!empty($_FILES['anh_cccd']) && isset($_FILES['anh_cccd']['error']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anh_cccd'];
                // limit size to 3MB
                if ($file['size'] > 3 * 1024 * 1024) {
                    $_SESSION['error'] = 'Kích thước ảnh CCCD không được vượt quá 3MB.';
                    header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
                    exit;
                }

                // Validate MIME type securely
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mime, $allowedMime, true)) {
                    $_SESSION['error'] = 'Định dạng ảnh CCCD không hợp lệ (chỉ JPG/PNG/GIF/WEBP).';
                    header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
                    exit;
                }

                $origName = basename($file['name']);
                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $ext = strtolower($ext ?: '');
                // normalize extension for webp images which may be detected as webp mime
                if ($mime === 'image/webp') $ext = 'webp';

                $uploadsDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                $fileName = 'cccd_' . (int)$id . '_' . time() . '.' . $ext;
                $dest = $uploadsDir . '/' . $fileName;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $_SESSION['error'] = 'Không lưu được ảnh CCCD. Vui lòng thử lại.';
                    header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
                    exit;
                }

                // Store as a relative uploads path so other code (helpers/models) can map URLs consistently
                $data['anh_cccd'] = 'uploads/' . $fileName;
            }

            // Persist changes
            $ok = User::updateProfile((int)$id, $data);

            // If password provided, update it
            if ($ok && !empty($newPassword)) {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                User::updatePasswordById((int)$id, $hashed);
            }

            if ($ok) {
                $_SESSION['success'] = 'Cập nhật thành công.';
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
            }
            header('Location: ' . BASE_URL . '/superadmin/update-personnel?id=' . (int)$id);
            exit;
        }

        $user = User::findById((int)$id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy nhân sự.';
            header('Location: ' . BASE_URL . '/superadmin/management-owner');
            exit;
        }

        $this->view('superadmin/update-personnel', ['user' => $user]);
    }

    public function guest()
    {
        // fetch query params for pagination / search (same logic as owner but for role 'user')
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $status = null;
        if (isset($_GET['status']) && $_GET['status'] !== 'all' && $_GET['status'] !== '') {
            $s = $_GET['status'];
            if ($s === 'hoạt động') $status = 1;
            elseif ($s === 'tạm dừng') $status = 2;
            elseif ($s === 'chờ duyệt') $status = 0;
            else $status = (int)$s; // Fallback nếu truyền số trực tiếp
        }

        $total = User::countByRole('user', $search, $status);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $users = User::getByRole('user', $perPage, $offset, $search, $status);

        $this->view('superadmin/management-guest', [
            'users' => $users,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status
        ]);
    }

    // Handle AJAX delete request
    public function delete()
    {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            return;
        }
        // Support both form POST and JSON body for AJAX
        $id = $_POST['id'] ?? null;
        $token = $_POST['_csrf'] ?? null;

        // If JSON body (AJAX), decode it
        if (!$id) {
            $body = file_get_contents('php://input');
            $json = json_decode($body, true);
            if (is_array($json)) {
                $id = $json['id'] ?? null;
                // allow CSRF token in JSON as well
                if (isset($json['_csrf'])) $token = $json['_csrf'];
            }
        }

        // Basic id validation
        if (empty($id) || !is_numeric($id)) {
            // If AJAX, return json
            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($isJson) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'Invalid id']);
                return;
            }
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }

        // Verify CSRF
        require_once __DIR__ . '/../Helpers/functions.php';
        if (!verify_csrf($token)) {
            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($isJson) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'CSRF token invalid']);
                return;
            }
            $_SESSION['error'] = 'Token không hợp lệ. Vui lòng thử lại.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }

        require_once __DIR__ . '/../Models/User.php';

        // Gọi hàm xóa trong Model (đã bao gồm xóa file và dữ liệu liên quan)
        $ok = User::deleteById((int)$id);

        $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($ok) {
            if ($isJson) {
                echo json_encode(['ok' => true, 'message' => 'Đã xóa thành công']);
                return;
            }
            $_SESSION['success'] = 'Xóa nhân sự thành công.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        } else {
            if ($isJson) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'message' => 'Lỗi server khi xóa']);
                return;
            }
            $_SESSION['error'] = 'Lỗi khi xóa trên server.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-owner'));
            return;
        }
    }
}
