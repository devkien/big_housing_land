<?php

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Admin area: allow admin and super_admin
        $this->requireRole([ROLE_ADMIN, ROLE_SUPER_ADMIN]);
    }
    public function index()
    {
        // Load pinned internal posts for news feed
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Models/User.php';
        $pinned = InternalPost::getPinned(6);
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
        $this->view('admin/home', ['pinnedPosts' => $pinnedFull]);
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
        $this->view('admin/profile', ['user' => $user]);
    }

    public function detailprofile()
    {
        require_once __DIR__ . '/../../core/Auth.php';
        require_once __DIR__ . '/../Models/User.php';

        $sessionUser = \Auth::user();
        $userId = $_GET['id'] ?? null;
        $user = null;

        if ($userId) {
            // Viewing someone else's profile
            $user = User::findById((int)$userId);
        } else {
            // Viewing own profile
            if (!empty($sessionUser['id'])) {
                $user = User::findById($sessionUser['id']);
            }
            // Fallback to session user if DB lookup fails
            if (!$user) $user = $sessionUser;
        }

        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/home'));
            exit;
        }

        // Determine if session user already rated the viewed profile
        $hasRated = false;
        $sessionId = $sessionUser['id'] ?? 0;
        $viewedId = $user['id'] ?? 0;
        if ($sessionId && $viewedId && $sessionId !== $viewedId) {
            $db = \Database::connect();
            $chk = $db->prepare('SELECT id FROM user_ratings WHERE rater_id = ? AND rated_user_id = ? LIMIT 1');
            $chk->execute([$sessionId, $viewedId]);
            if ($chk->fetch()) $hasRated = true;
        }

        $this->view('admin/detailprofile', [
            'user' => $user,
            'sessionUser' => $sessionUser, // Pass session user for permission checks in view
            'has_rated' => $hasRated
        ]);
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

        // Lấy ID từ URL, nếu không có thì mặc định là user đang đăng nhập
        $id = isset($_GET['id']) ? (int)$_GET['id'] : $sessionUser['id'];

        // === PERMISSION CHECK ===
        // Super Admin can edit anyone.
        // Admin can only edit their own profile.
        if (($sessionUser['quyen'] ?? 'user') === 'admin' && $id !== (int)$sessionUser['id']) {
            $_SESSION['error'] = 'Bạn không có quyền chỉnh sửa hồ sơ của người dùng khác.';
            header('Location: ' . BASE_URL . '/admin/detailprofile?id=' . $id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'nam_sinh' => trim($_POST['nam_sinh'] ?? null),
                'so_cccd' => trim($_POST['so_cccd'] ?? null),
                'dia_chi' => trim($_POST['dia_chi'] ?? null),
            ];

            if (empty($data['so_dien_thoai'])) {
                // $_SESSION['error'] = 'Số điện thoại là bắt buộc';
                header('Location: ' . BASE_URL . '/admin/editprofile?id=' . $id);
                exit;
            }

            $existing = User::findByPhone($data['so_dien_thoai']);
            if ($existing && !empty($existing['id']) && $existing['id'] != $id) {
                // $_SESSION['error'] = 'Số điện thoại đã được sử dụng';
                header('Location: ' . BASE_URL . '/admin/editprofile?id=' . $id);
                exit;
            }

            // Handle avatar upload (admin may update avatar for the target user)
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
                // save avatar path if uploaded
                if ($avatarSavedPath) {
                    User::updateAvatar($id, $avatarSavedPath);
                }
                // Chỉ cập nhật session nếu đang sửa chính mình
                if ($id == $sessionUser['id']) {
                    $updated = User::findById($id);
                    if ($updated) $_SESSION['user'] = $updated;
                }
                header('Location: ' . BASE_URL . '/admin/detailprofile?id=' . $id);
                exit;
            } else {
                // remove uploaded file if DB update failed
                if ($avatarSavedPath) {
                    $f = __DIR__ . '/../../public/uploads/' . $avatarSavedPath;
                    if (file_exists($f)) @unlink($f);
                }
                header('Location: ' . BASE_URL . '/admin/editprofile?id=' . $id);
                exit;
            }
        }

        $user = User::findById($id);
        if (!$user) $user = $sessionUser;

        $this->view('admin/editprofile', ['user' => $user]);
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
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            if ($new !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            $user = User::findById($id);
            if (!$user || !password_verify($current, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            }

            $hash = password_hash($new, PASSWORD_BCRYPT);
            $ok = User::updatePasswordById($id, $hash);

            if ($ok) {
                // Refresh session user
                $updated = User::findById($id);
                if ($updated) $_SESSION['user'] = $updated;
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
                header('Location: ' . BASE_URL . '/admin/changepassword');
                exit;
            } else {
                $_SESSION['error'] = 'Lỗi khi lưu mật khẩu mới';
                header('Location: ' . BASE_URL . '/admin/changepassword');
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

        $this->view('admin/changepassword', [
            'user' => $user,
            'displayRole' => $displayRole,
            'officeBadge' => $officeBadge
        ]);
    }

    // Admin vào kho tài nguyền 
    public function resourcePost()
    {
        // If POST: handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            require_once __DIR__ . '/../Models/Property.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $sessionUser = \Auth::user();
            $userId = $sessionUser['id'] ?? null;

            // ----- Server-side mapping & validation -----
            // Allowed enums / values (map client inputs to canonical DB values)
            $allowed = [
                'loai_bds' => ['ban', 'cho_thue'],
                'phap_ly' => ['co_so', 'khong_so'],
                'don_vi_dien_tich' => ['m2', 'm²', 'ha'],
                'trich_thuong_don_vi' => ['%', 'VND'],
                'don_vi_gia' => ['nguyen_can', 'm2']
            ];

            // normalize helpers
            $normalizeUnit = function ($v) {
                if ($v === null) return null;
                $v = trim((string)$v);
                if ($v === 'm²' || $v === 'm2') return 'm2';
                if ($v === 'ha') return 'ha';
                return $v;
            };

            $loai_bds = trim($_POST['loai_bds'] ?? '');
            if (!in_array($loai_bds, $allowed['loai_bds'], true)) {
                $loai_bds = $allowed['loai_bds'][0];
            }

            $phap_ly = trim($_POST['phap_ly'] ?? '');
            if (!in_array($phap_ly, $allowed['phap_ly'], true)) {
                $phap_ly = $allowed['phap_ly'][0];
            }

            $don_vi = $normalizeUnit($_POST['don_vi_dien_tich'] ?? '');
            if (!in_array($don_vi, ['m2', 'ha'], true)) $don_vi = 'm2';

            $trich_unit = trim($_POST['trich_thuong_don_vi'] ?? '');
            if (!in_array($trich_unit, $allowed['trich_thuong_don_vi'], true)) $trich_unit = '%';

            $don_vi_gia = trim($_POST['don_vi_gia'] ?? '');
            if (!in_array($don_vi_gia, $allowed['don_vi_gia'], true)) {
                $don_vi_gia = 'nguyen_can';
            }

            // floors validation
            $so_tang_raw = $_POST['so_tang'] ?? '';
            $so_tang = null;
            if ($so_tang_raw !== '') {
                $so_tang_val = filter_var($so_tang_raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
                if ($so_tang_val === false) {
                    $_SESSION['error'] = 'Số tầng không hợp lệ';
                    header('Location: ' . BASE_URL . '/admin/management-resource-post');
                    exit;
                }
                $so_tang = $so_tang_val;
            }

            // numeric fields
            $makeFloat = function ($v) {
                if ($v === null || $v === '') return null;
                if (!is_numeric($v)) return null;
                return (float)$v;
            };

            // Determine loai_kho (DB enum) from loai_bds
            $loai_kho = ($loai_bds === 'ban') ? 'kho_nha_dat' : 'kho_cho_thue';

            // Build sanitized data array
            $data = [
                'user_id' => $userId,
                'phong_ban' => trim($_POST['phong_ban'] ?? ''),
                'tieu_de' => trim($_POST['tieu_de'] ?? ''),
                'loai_bds' => $loai_bds,
                'loai_kho' => $loai_kho,
                'phap_ly' => $phap_ly,
                // If phap_ly indicates there is a title ('co_so'), capture the mã số sổ; otherwise store null
                'ma_so_so' => ($phap_ly === 'co_so') ? (trim($_POST['ma_so_so'] ?? '') ?: null) : null,
                'ma_so_thue' => trim($_POST['ma_so_thue'] ?? '') ?: null,
                'dien_tich' => $makeFloat($_POST['dien_tich'] ?? null),
                'don_vi_dien_tich' => $don_vi,
                'chieu_dai' => $makeFloat($_POST['chieu_dai'] ?? null),
                'chieu_rong' => $makeFloat($_POST['chieu_rong'] ?? null),
                'so_tang' => $so_tang,
                'gia_chao' => $makeFloat($_POST['gia_chao'] ?? null),
                'don_vi_gia' => $don_vi_gia,
                'trich_thuong_gia_tri' => trim($_POST['trich_thuong_gia_tri'] ?? ''),
                'trich_thuong_don_vi' => $trich_unit,
                'tinh_thanh' => trim($_POST['tinh_thanh'] ?? ''),
                'quan_huyen' => (trim($_POST['quan_huyen'] ?? '') ?: null),
                'xa_phuong' => (trim($_POST['xa_phuong'] ?? '') ?: null),
                'dia_chi_chi_tiet' => trim($_POST['dia_chi_chi_tiet'] ?? ''),
                'mo_ta' => trim($_POST['mo_ta'] ?? ''),
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
                'trang_thai' => trim($_POST['trang_thai'] ?? '')
            ];

            // Basic required fields
            if (empty($data['tieu_de']) || empty($data['tinh_thanh'])) {
                $_SESSION['error'] = 'Vui lòng điền tiêu đề và tỉnh/thành.';
                // debug log
                $log = __DIR__ . '/../../storage/logs/resource_post_debug.log';
                @file_put_contents($log, json_encode(["ts" => date('c'), "event" => "validation_failed", "reason" => "missing_title_or_province", "user_id" => ($userId ?? null), "post" => $_POST, "files_count" => (empty($_FILES) ? 0 : array_sum(array_map('count', array_filter($_FILES)))),]) . "\n", FILE_APPEND);
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            // ===== map/validate trang_thai (DB enum) =====
            $allowedStatuses = ['ban_manh', 'tam_dung_ban', 'dung_ban', 'da_ban'];
            $trang_thai = trim($_POST['trang_thai'] ?? '');
            if (!in_array($trang_thai, $allowedStatuses, true)) {
                $trang_thai = 'ban_manh';
            }
            // ensure it's set in data
            $data['trang_thai'] = $trang_thai;

            // Ensure DB-required fields have sensible defaults to avoid insert failure
            if (!isset($data['dien_tich']) || $data['dien_tich'] === null || $data['dien_tich'] === '') {
                $data['dien_tich'] = 0.0;
            } else {
                $data['dien_tich'] = (float)$data['dien_tich'];
            }
            if (!isset($data['gia_chao']) || $data['gia_chao'] === null || $data['gia_chao'] === '') {
                $data['gia_chao'] = 0.0;
            } else {
                $data['gia_chao'] = (float)$data['gia_chao'];
            }
            if (empty($data['don_vi_gia'])) {
                $data['don_vi_gia'] = 'nguyen_can';
            }

            // ----- Validate uploaded media -----
            $savedMedia = [];
            $maxFiles = 12;
            $maxSize = 8 * 1024 * 1024; // 8MB each
            $allowedMimes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'video/mp4',
                'video/quicktime'
            ];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) {
                    $_SESSION['error'] = "Chỉ được tải tối đa $maxFiles file.";
                    header('Location: ' . BASE_URL . '/admin/management-resource-post');
                    exit;
                }
                // prepare upload dir early
                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/properties_temp';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                // files checked later and moved after property created
            }

            // Normalize and log incoming files for debugging (helps diagnose why files may be missing)
            $uploadDebugPath = __DIR__ . '/../../storage/logs/upload_debug.log';
            @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - FILES dump: " . var_export($_FILES, true) . "\n", FILE_APPEND);

            $propertyId = Property::create($data);
            if (!$propertyId) {
                $_SESSION['error'] = 'Lưu tin thất bại. Vui lòng thử lại.';
                // log create failure details
                $log = __DIR__ . '/../../storage/logs/resource_post_debug.log';
                @file_put_contents($log, json_encode(["ts" => date('c'), "event" => "create_failed", "user_id" => ($userId ?? null), "post" => $_POST, "data" => $data, "files" => $_FILES]) . "\n", FILE_APPEND);
                header('Location: ' . BASE_URL . '/admin/management-resource-post');
                exit;
            }

            // Handle uploaded media files (more robust: accept single file cases and log skips)
            $savedMedia = [];
            if (!empty($_FILES['media'])) {
                // normalize arrays
                $files = $_FILES['media'];
                $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
                $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
                $names = is_array($files['name']) ? $files['name'] : [$files['name']];

                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/properties/' . $propertyId;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                $count = count($tmpNames);
                for ($i = 0; $i < $count; $i++) {
                    $err = $errors[$i] ?? UPLOAD_ERR_NO_FILE;
                    $tmp = $tmpNames[$i] ?? '';
                    $orig = isset($names[$i]) ? basename($names[$i]) : '';

                    if ($err !== UPLOAD_ERR_OK) {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - Skipping file (#$i) orig={$orig} err={$err}\n", FILE_APPEND);
                        continue;
                    }
                    if (empty($tmp) || !is_uploaded_file($tmp)) {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - Not an uploaded file (#$i) tmp={$tmp}\n", FILE_APPEND);
                        continue;
                    }

                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    // validate size
                    $size = @filesize($tmp);
                    if ($size === false) {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - Could not read size (#$i) tmp={$tmp}\n", FILE_APPEND);
                        continue;
                    }
                    if ($size > $maxSize) {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - File too large (#$i) size={$size}\n", FILE_APPEND);
                        continue;
                    }
                    // validate mime from tmp
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - Rejected mime (#$i) mime={$mime} orig={$orig}\n", FILE_APPEND);
                        continue;
                    }

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/properties/' . $propertyId . '/' . $filename;
                        $type = strpos($mime, 'video/') === 0 ? 'video' : 'image';
                        $savedMedia[] = ['type' => $type, 'path' => $webPath];
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - Saved file (#$i) to {$dest}\n", FILE_APPEND);
                    } else {
                        @file_put_contents($uploadDebugPath, date('Y-m-d H:i:s') . " - move_uploaded_file failed (#$i) src={$tmp} dest={$dest}\n", FILE_APPEND);
                    }
                }
            }

            if (!empty($savedMedia)) {
                Property::addMedia($propertyId, $savedMedia);
            }

            $_SESSION['success'] = 'Đăng tin thành công.';
            header('Location: ' . BASE_URL . '/admin/management-resource-post');
            exit;
        }

        $this->view('admin/resource-post');
    }

    public function resource()
    {
        // list kho_nha_dat
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../Models/Property.php';

        // --- 1. Lấy ID người dùng hiện tại ---
        // Giả sử bạn dùng class Auth như các phần trước
        require_once __DIR__ . '/../../core/Auth.php';
        $currentUser = \Auth::user();
        $currentUserId = $currentUser['id'] ?? 0;
        // -------------------------------------

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);

        // Lấy danh sách collection của user để hiển thị trong modal "Lưu"
        $collections = Collection::getForUser($currentUserId);

        // Tạo một map để kiểm tra tài sản nào đã được lưu
        $collectionMap = [];
        if ($currentUserId > 0 && !empty($properties)) {
            $propertyIds = array_map(function ($p) {
                return (int)$p['id'];
            }, $properties);

            $db = \Database::connect();

            // Lấy tất cả collection IDs mà user này sở hữu
            $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmtOwner->execute([$currentUserId]);
            $ownedCollectionIds = $stmtOwner->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ownedCollectionIds) && !empty($propertyIds)) {
                // Tìm xem các tài sản đang hiển thị có nằm trong collection nào của user không
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $collectionPlaceholders = implode(',', array_fill(0, count($ownedCollectionIds), '?'));
                $sqlMap = "SELECT resource_id, COUNT(id) as count FROM collection_items WHERE resource_id IN ($placeholders) AND collection_id IN ($collectionPlaceholders) GROUP BY resource_id";
                $stmtMap = $db->prepare($sqlMap);
                $stmtMap->execute(array_merge($propertyIds, $ownedCollectionIds));
                $collectionMap = $stmtMap->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }

        $this->view('admin/resource', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap
        ]);
    }

    public function resourceRent()
    {
        // list kho_cho_thue
        require_once __DIR__ . '/../Models/Property.php';
        require_once __DIR__ . '/../Models/Collection.php';

        // --- 1. Lấy ID người dùng (Tương tự hàm trên) ---
        require_once __DIR__ . '/../../core/Auth.php';
        $currentUser = \Auth::user();
        $currentUserId = $currentUser['id'] ?? 0;
        // ----------------------------------------------

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_cho_thue', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_cho_thue', $perPage, $offset, $searchTerm, $status);

        // Lấy danh sách collection của user để hiển thị trong modal "Lưu"
        $collections = Collection::getForUser($currentUserId);

        // Tạo một map để kiểm tra tài sản nào đã được lưu
        $collectionMap = [];
        if ($currentUserId > 0 && !empty($properties)) {
            $propertyIds = array_map(function ($p) {
                return (int)$p['id'];
            }, $properties);

            $db = \Database::connect();

            // Lấy tất cả collection IDs mà user này sở hữu
            $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmtOwner->execute([$currentUserId]);
            $ownedCollectionIds = $stmtOwner->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ownedCollectionIds) && !empty($propertyIds)) {
                // Tìm xem các tài sản đang hiển thị có nằm trong collection nào của user không
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $collectionPlaceholders = implode(',', array_fill(0, count($ownedCollectionIds), '?'));
                $sqlMap = "SELECT resource_id, COUNT(id) as count FROM collection_items WHERE resource_id IN ($placeholders) AND collection_id IN ($collectionPlaceholders) GROUP BY resource_id";
                $stmtMap = $db->prepare($sqlMap);
                $stmtMap->execute(array_merge($propertyIds, $ownedCollectionIds));
                $collectionMap = $stmtMap->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }

        $this->view('admin/resource-rent', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections, // Dùng cho modal
            'collectionMap' => $collectionMap // Dùng để hiển thị icon đã lưu
        ]);
    }

    public function resourceSum()
    {
        // list kho_nha_dat
        require_once __DIR__ . '/../Models/Property.php';

        // --- 1. Lấy ID người dùng đang đăng nhập ---
        require_once __DIR__ . '/../../core/Auth.php';
        $userId = \Auth::user()['id'] ?? 0;
        // ------------------------------------------

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        // prefer address as explicit search term
        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);

        // --- 2. Sửa truy vấn: Chỉ lấy Collection của user_id này ---
        $db = \Database::connect();
        // Thêm điều kiện: user_id = :uid
        $sql = "SELECT * FROM collections WHERE user_id = :uid AND trang_thai = 1 ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // -----------------------------------------------------------

        // --- 3. Tạo map kiểm tra tài sản đã lưu (Logic mới thêm) ---
        $collectionMap = [];
        if ($userId > 0 && !empty($properties)) {
            $propertyIds = array_map(function ($p) {
                return (int)$p['id'];
            }, $properties);

            // Lấy danh sách ID bộ sưu tập của user
            $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmtOwner->execute([$userId]);
            $ownedCollectionIds = $stmtOwner->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ownedCollectionIds) && !empty($propertyIds)) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $collectionPlaceholders = implode(',', array_fill(0, count($ownedCollectionIds), '?'));
                $sqlMap = "SELECT resource_id, COUNT(id) as count FROM collection_items WHERE resource_id IN ($placeholders) AND collection_id IN ($collectionPlaceholders) GROUP BY resource_id";
                $stmtMap = $db->prepare($sqlMap);
                $stmtMap->execute(array_merge($propertyIds, $ownedCollectionIds));
                $collectionMap = $stmtMap->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }

        $this->view('admin/resource_sum', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections,
            'collectionMap' => $collectionMap // Truyền biến này xuống view
        ]);
    }

    public function resourceSum2()
    {
        // list kho_cho_thue
        require_once __DIR__ . '/../Models/Property.php';

        // --- 1. Lấy ID người dùng đang đăng nhập (Giống hàm trên) ---
        require_once __DIR__ . '/../../core/Auth.php';
        $userId = \Auth::user()['id'] ?? 0;
        // ----------------------------------------------------------

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        $searchTerm = $address ?: $search;

        $total = Property::countByLoaiKho('kho_cho_thue', $searchTerm, $status);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $properties = Property::getByLoaiKho('kho_cho_thue', $perPage, $offset, $searchTerm, $status);

        // --- 2. Sửa truy vấn: Copy logic từ resourceSum sang ---
        // Thay vì dùng Model::allWithCount(), ta dùng query trực tiếp để lọc theo user_id
        $db = \Database::connect();
        $sql = "SELECT * FROM collections WHERE user_id = :uid AND trang_thai = 1 ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // -----------------------------------------------------

        // --- 3. Tạo map kiểm tra tài sản đã lưu (Logic mới thêm) ---
        $collectionMap = [];
        if ($userId > 0 && !empty($properties)) {
            $propertyIds = array_map(function ($p) {
                return (int)$p['id'];
            }, $properties);

            // Lấy danh sách ID bộ sưu tập của user
            $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmtOwner->execute([$userId]);
            $ownedCollectionIds = $stmtOwner->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($ownedCollectionIds) && !empty($propertyIds)) {
                $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
                $collectionPlaceholders = implode(',', array_fill(0, count($ownedCollectionIds), '?'));
                $sqlMap = "SELECT resource_id, COUNT(id) as count FROM collection_items WHERE resource_id IN ($placeholders) AND collection_id IN ($collectionPlaceholders) GROUP BY resource_id";
                $stmtMap = $db->prepare($sqlMap);
                $stmtMap->execute(array_merge($propertyIds, $ownedCollectionIds));
                $collectionMap = $stmtMap->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }

        $this->view('admin/resource_sum_2', [
            'properties' => $properties,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'address' => $address,
            'collections' => $collections, // Bây giờ biến này chứa đúng dữ liệu của user
            'collectionMap' => $collectionMap // Truyền biến này xuống view
        ]);
    }
    public function reportList()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $managerCode = $user['ma_nhan_su'] ?? null;

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $total = LeadReport::countAll($search, $managerCode);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $reports = LeadReport::getList($perPage, $offset, $search, $managerCode);

        $this->view('admin/report_list', [
            'reports' => $reports,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'search' => $search
        ]);
    }

    public function reportCustomerDetail()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID báo cáo không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/report_list');
            exit;
        }

        $report = LeadReport::getById($id);

        if (!$report) {
            $_SESSION['error'] = 'Không tìm thấy báo cáo.';
            header('Location: ' . BASE_URL . '/admin/report_list');
            exit;
        }

        $this->view('admin/report_customer', [
            'report' => $report,
        ]);
    }

    public function updateResourceStatus()
    {
        // Chỉ xử lý method POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if ($id && $status) {
                // Sử dụng Database class trực tiếp để tránh lỗi nếu Model chưa có hàm update
                $db = \Database::connect();
                $sql = "UPDATE properties SET trang_thai = :status WHERE id = :id";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([':status' => $status, ':id' => $id]);

                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hoặc trạng thái']);
            }
            exit;
        }
    }

    public function detail()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/admin/management-resource');
            exit;
        }

        $db = \Database::connect();

        // Lấy thông tin bất động sản và người đăng
        $sql = "SELECT p.*, u.ho_ten as user_name, u.so_dien_thoai as user_phone, u.avatar as user_avatar, u.phong_ban 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            header('Location: ' . BASE_URL . '/admin/management-resource');
            exit;
        }

        // Lấy hình ảnh/media
        require_once __DIR__ . '/../Models/Property.php';
        $media = [];
        if (method_exists('Property', 'getMedia')) {
            $media = Property::getMedia($id);
        } else {
            $sqlMedia = "SELECT * FROM property_media WHERE property_id = :id";
            $stmtMedia = $db->prepare($sqlMedia);
            $stmtMedia->execute([':id' => $id]);
            $media = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);
        }
        $property['media'] = $media;

        $this->view('admin/detail', ['property' => $property]);
    }

    public function addToCollection()
    {
        // Xóa bộ đệm đầu ra để đảm bảo JSON sạch (tránh lỗi do khoảng trắng hoặc warning)
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Thêm kiểm tra CSRF token để server chấp nhận yêu cầu
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Token bảo mật không hợp lệ.']);
                exit;
            }

            $propertyId = $_POST['property_id'] ?? null;
            $collectionIds = $_POST['collections'] ?? [];

            // Lấy ID người dùng hiện tại để bảo mật
            require_once __DIR__ . '/../../core/Auth.php';
            $user = \Auth::user();
            $userId = $user['id'] ?? 0;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
                exit;
            }

            if ($propertyId) {
                $db = \Database::connect();
                try {
                    $db->beginTransaction();

                    // 1. Lấy danh sách ID các bộ sưu tập mà user này sở hữu
                    $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
                    $stmtOwner->execute([$userId]);
                    $userOwnedCollections = $stmtOwner->fetchAll(PDO::FETCH_COLUMN);

                    // Chỉ thực hiện nếu user có collection
                    if (!empty($userOwnedCollections)) {
                        // 2. Xóa tài nguyên này khỏi TẤT CẢ bộ sưu tập CỦA USER (Reset trạng thái)
                        $placeholders = implode(',', array_fill(0, count($userOwnedCollections), '?'));

                        // SỬA LỖI: Dùng `resource_id` thay vì `property_id`
                        $sqlDelete = "DELETE FROM collection_items 
                                      WHERE resource_id = ? 
                                      AND collection_id IN ($placeholders)";

                        $paramsDelete = array_merge([$propertyId], $userOwnedCollections);
                        $stmtDelete = $db->prepare($sqlDelete);
                        $stmtDelete->execute($paramsDelete);
                    }

                    // 3. Thêm lại vào các bộ sưu tập được chọn (và user này sở hữu)
                    if (!empty($collectionIds)) {
                        // SỬA LỖI: Dùng `resource_id` và thêm `resource_type`
                        $sqlInsert = "INSERT INTO collection_items (collection_id, resource_id, resource_type) VALUES (?, ?, 'bat_dong_san')";
                        $stmtInsert = $db->prepare($sqlInsert);

                        foreach ($collectionIds as $cId) {
                            $cId = (int)$cId;
                            // Chỉ thêm nếu collection ID được chọn nằm trong danh sách collection của user
                            if (in_array($cId, $userOwnedCollections)) {
                                $stmtInsert->execute([$cId, $propertyId]);
                            }
                        }
                    }

                    $db->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu ID tài nguyên.']);
            }
            exit;
        }
    }

    // --- HÀM 1: Lấy danh sách Collection đã tick (ĐÃ SỬA: Chỉ lấy của User đang đăng nhập) ---
    public function getPropertyCollections()
    {
        // 1. Dọn sạch bộ đệm để tránh lỗi cú pháp JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        // 2. Lấy User ID
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $userId = $user['id'] ?? 0;
        $resourceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($userId && $resourceId) {
            $db = \Database::connect();
            try {
                // SỬA LỖI: Join bảng collections để chỉ lấy ID collection của chính user này
                $sql = "SELECT ci.collection_id 
                        FROM collection_items ci
                        JOIN collections c ON ci.collection_id = c.id
                        WHERE ci.resource_id = ? AND c.user_id = ?";

                $stmt = $db->prepare($sql);
                $stmt->execute([$resourceId, $userId]);

                // Sử dụng \PDO để tránh lỗi namespace nếu chưa use PDO
                $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                echo json_encode(['success' => true, 'collection_ids' => $ids]);
            } catch (\Throwable $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID hoặc chưa đăng nhập']);
        }
        exit;
    }

    // --- HÀM 2: Lưu/Xóa Collection Đồng bộ (ĐÃ SỬA: Xử lý bỏ tick) ---
    public function saveToCollections()
    {
        // 1. Xóa buffer rác
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        // 2. Kiểm tra method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            exit;
        }

        try {
            // 3. Nhận dữ liệu JSON
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, true);

            if (!$input) throw new \Exception('Dữ liệu không hợp lệ');

            // 4. Kiểm tra CSRF
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($input['_csrf'] ?? null)) {
                throw new \Exception('Token bảo mật không hợp lệ');
            }

            // 5. Lấy User và Dữ liệu
            require_once __DIR__ . '/../../core/Auth.php';
            $user = \Auth::user();
            $userId = $user['id'] ?? 0;

            $propertyId = isset($input['property_id']) ? (int)$input['property_id'] : 0;
            // Mảng các collection ID được tick (nếu bỏ tick hết thì mảng rỗng)
            $selectedCollectionIds = isset($input['collections']) && is_array($input['collections']) ? $input['collections'] : [];

            if (!$userId || $propertyId <= 0) {
                throw new \Exception('Thiếu thông tin người dùng hoặc tài nguyên');
            }

            $db = \Database::connect();
            $db->beginTransaction();

            // BƯỚC A: Lấy danh sách TẤT CẢ bộ sưu tập mà User này sở hữu (để bảo mật)
            $stmtOwner = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmtOwner->execute([$userId]);
            $userOwnedCollections = $stmtOwner->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($userOwnedCollections)) {
                // Tạo chuỗi placeholder (?,?,?) cho câu lệnh IN
                $placeholders = implode(',', array_fill(0, count($userOwnedCollections), '?'));

                // BƯỚC B: XÓA tài nguyên này khỏi TẤT CẢ bộ sưu tập của User này (Reset trạng thái)
                // Đây là bước quan trọng để xử lý việc "Bỏ tick"
                $sqlDelete = "DELETE FROM collection_items 
                              WHERE resource_id = ? 
                              AND collection_id IN ($placeholders)";

                // Tham số: [property_id, col_id_1, col_id_2, ...]
                $paramsDelete = array_merge([$propertyId], $userOwnedCollections);
                $stmtDelete = $db->prepare($sqlDelete);
                $stmtDelete->execute($paramsDelete);

                // BƯỚC C: THÊM LẠI vào các bộ sưu tập được chọn
                if (!empty($selectedCollectionIds)) {
                    $sqlInsert = "INSERT INTO collection_items (collection_id, resource_id, resource_type) VALUES (?, ?, 'bat_dong_san')";
                    $stmtInsert = $db->prepare($sqlInsert);

                    foreach ($selectedCollectionIds as $cId) {
                        $cId = (int)$cId;
                        // Chỉ thêm nếu collection ID đó thực sự thuộc về user (Chặn hack ID)
                        if (in_array($cId, $userOwnedCollections)) {
                            $stmtInsert->execute([$cId, $propertyId]);
                        }
                    }
                }
            }

            $db->commit();
            // Trả về cả 2 key 'ok' và 'success' để JS bắt cái nào cũng được
            echo json_encode(['ok' => true, 'success' => true]);
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            // Trả về HTTP 200 nhưng json báo lỗi để JS alert ra
            echo json_encode(['ok' => false, 'success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    public function collection()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $user = \Auth::user();
        $userId = $user['id'] ?? null;
        $userRole = \Auth::role();

        // Super admin thấy tất cả, admin chỉ thấy của mình
        $filterUserId = null;
        if ($userRole !== 'super_admin') {
            $filterUserId = $userId;
        }
        $collections = Collection::allWithCount($search, $filterUserId);

        // Kiểm tra file ảnh thực tế, nếu không tồn tại thì gán null để tránh lỗi 404 ở View
        foreach ($collections as &$col) {
            if (!empty($col['anh_dai_dien'])) {
                $physPath = __DIR__ . '/../../public/' . $col['anh_dai_dien'];
                if (!file_exists($physPath)) $col['anh_dai_dien'] = null;
            }
        }

        $this->view('admin/collection', [
            'collections' => $collections,
            'search' => $search,
            'currentUser' => $user,
            'currentUserRole' => $userRole
        ]);
    }

    public function collectionDetail()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../Models/Property.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID bộ sưu tập không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/collection');
            exit;
        }

        $collection = Collection::getById($id);
        if (!$collection) {
            $_SESSION['error'] = 'Không tìm thấy bộ sưu tập.';
            header('Location: ' . BASE_URL . '/admin/collection');
            exit;
        }

        // Read optional filters from query string
        $filters = [];
        if (isset($_GET['q']) && trim($_GET['q']) !== '') $filters['q'] = trim($_GET['q']);
        if (isset($_GET['status']) && trim($_GET['status']) !== '' && trim($_GET['status']) !== 'all') $filters['status'] = trim($_GET['status']);
        if (isset($_GET['address']) && trim($_GET['address']) !== '') $filters['address'] = trim($_GET['address']);

        $items = Collection::getItems($id, 'bat_dong_san', $filters);

        $user = \Auth::user();
        $userRole = \Auth::role();

        $this->view('admin/collection-detail', [
            'collection' => $collection,
            'items' => $items,
            'filters' => $filters,
            'currentUser' => $user,
            'currentUserRole' => $userRole
        ]);
    }

    public function creCollection()
    {
        // Handle POST (form submit)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Collection.php';
            require_once __DIR__ . '/../Helpers/functions.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';
            $mo_ta = isset($_POST['mo_ta']) ? trim($_POST['mo_ta']) : null;

            $user = \Auth::user();
            $userId = $user['id'] ?? null;

            $uploadPath = __DIR__ . '/../../public/uploads/collections';
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            $savedPath = null;
            if (!empty($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['anh_dai_dien']['tmp_name'];
                $orig = basename($_FILES['anh_dai_dien']['name']);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $filename = uniqid('coll_') . '.' . $ext;
                $dest = $uploadPath . '/' . $filename;
                if (@move_uploaded_file($tmp, $dest)) {
                    $savedPath = 'uploads/collections/' . $filename;
                }
            }

            $data = [
                'user_id' => $userId,
                'ten_bo_suu_tap' => $name,
                'anh_dai_dien' => $savedPath,
                'mo_ta' => $mo_ta,
                'is_default' => 0,
                'trang_thai' => 1,
            ];

            $created = Collection::create($data);
            if ($created) {
                header('Location: ' . BASE_URL . '/admin/collection');
                exit;
            } else {
                $_SESSION['error'] = 'Không thể tạo bộ sưu tập';
            }
        }

        $this->view('admin/cre-collection');
    }

    public function renameCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';

        if ($id <= 0 || $name === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $ok = Collection::updateName($id, $name);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }

    public function deleteCollection()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID không hợp lệ']);
            exit;
        }

        $ok = Collection::deleteById($id);
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }

    public function notification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        $posts = DealPost::getList($perPage, $offset, $search);

        $this->view('admin/notification', [
            'posts' => $posts,
            'page' => $page,
            'search' => $search
        ]);
    }

    public function creNotification()
    {
        require_once __DIR__ . '/../Helpers/functions.php';
        $user = \Auth::user();

        // Handle POST (create new deal post)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/DealPost.php';
            $userId = $user['id'] ?? null;

            $tieu_de = isset($_POST['tieu_de']) ? trim($_POST['tieu_de']) : null;
            $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';
            $ma_nhan_vien = isset($_POST['ma_nhan_vien']) ? trim($_POST['ma_nhan_vien']) : null;

            $errors = [];
            // Validation: title and content required
            if (empty($tieu_de)) {
                $errors[] = 'Tiêu đề không được để trống.';
            }
            if (empty(strip_tags($noi_dung))) {
                $errors[] = 'Nội dung không được để trống.';
            }

            // Validate files if provided
            $saved = [];
            $uploadDir = __DIR__ . '/../../public/uploads/deal_posts';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

            if (!empty($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/quicktime'];
                $maxSize = 20 * 1024 * 1024; // 20MB
                for ($i = 0; $i < count($_FILES['images']['tmp_name']); $i++) {
                    $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err === UPLOAD_ERR_NO_FILE) continue;
                    if ($err !== UPLOAD_ERR_OK) {
                        $errors[] = 'Lỗi upload file ' . ($_FILES['images']['name'][$i] ?? '') . '.';
                        continue;
                    }
                    $tmp = $_FILES['images']['tmp_name'][$i];
                    if (filesize($tmp) > $maxSize) {
                        $errors[] = 'Kích thước file quá lớn (max 20MB): ' . ($_FILES['images']['name'][$i] ?? '');
                        continue;
                    }
                    // More robust validation if needed
                    $orig = basename($_FILES['images']['name'][$i]);
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $filename = uniqid('deal_') . '.' . $ext;
                    $dest = $uploadDir . '/' . $filename;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $saved[] = 'uploads/deal_posts/' . $filename;
                    } else {
                        $errors[] = 'Không thể lưu file: ' . $orig;
                    }
                }
            }

            if (empty($errors)) {
                $postId = DealPost::create(['user_id' => $userId, 'tieu_de' => $tieu_de, 'noi_dung' => $noi_dung]);
                if ($postId && !empty($saved)) {
                    DealPost::addImages($postId, $saved);
                }
                header('Location: ' . BASE_URL . '/admin/notification');
                exit;
            }

            $this->view('admin/cre-notification', ['errors' => $errors, 'old' => $_POST, 'user' => $user]);
            return;
        }

        $this->view('admin/cre-notification', ['user' => $user]);
    }

    public function editNotification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        $id = $_GET['id'] ?? 0;
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['error'] = 'ID bài viết không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/notification');
            exit;
        }
        $id = (int)$id;

        // Handle POST for update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/edit-notification?id=' . $id);
                exit;
            }

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');
            $errors = [];

            if (empty($tieu_de)) $errors[] = 'Tiêu đề không được để trống.';
            if (empty(strip_tags($noi_dung))) $errors[] = 'Nội dung không được để trống.';

            // Handle removed images
            if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
                foreach ($_POST['remove_images'] as $imgId) {
                    if ((int)$imgId > 0) DealPost::deleteImageById((int)$imgId);
                }
            }

            // Handle new uploads
            $saved = [];
            $uploadDir = __DIR__ . '/../../public/uploads/deal_posts';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

            if (!empty($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/quicktime'];
                $maxSize = 20 * 1024 * 1024; // 20MB
                for ($i = 0; $i < count($_FILES['images']['tmp_name']); $i++) {
                    $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err === UPLOAD_ERR_NO_FILE) continue;
                    if ($err !== UPLOAD_ERR_OK) {
                        $errors[] = 'Lỗi upload file ' . ($_FILES['images']['name'][$i] ?? '') . '.';
                        continue;
                    }
                    $tmp = $_FILES['images']['tmp_name'][$i];
                    if (filesize($tmp) > $maxSize) {
                        $errors[] = 'Kích thước file quá lớn (max 20MB): ' . ($_FILES['images']['name'][$i] ?? '');
                        continue;
                    }
                    $orig = basename($_FILES['images']['name'][$i]);
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $filename = uniqid('deal_') . '.' . $ext;
                    $dest = $uploadDir . '/' . $filename;
                    if (@move_uploaded_file($tmp, $dest)) {
                        $saved[] = 'uploads/deal_posts/' . $filename;
                    } else {
                        $errors[] = 'Không thể lưu file: ' . $orig;
                    }
                }
            }

            if (empty($errors)) {
                $db = Database::connect();
                $stmt = $db->prepare("UPDATE deal_posts SET tieu_de = :tieu_de, noi_dung = :noi_dung WHERE id = :id");
                $stmt->execute([
                    ':tieu_de' => $tieu_de,
                    ':noi_dung' => $noi_dung,
                    ':id' => $id
                ]);

                if (!empty($saved)) {
                    DealPost::addImages($id, $saved);
                }
                $_SESSION['success'] = 'Cập nhật bài viết thành công.';
            } else {
                $_SESSION['errors'] = $errors;
            }
            header('Location: ' . BASE_URL . '/admin/edit-notification?id=' . $id);
            exit;
        }

        // Handle GET to show form
        $post = DealPost::getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL . '/admin/notification');
            exit;
        }

        $this->view('admin/edit-notification', ['post' => $post]);
    }

    public function deleteNotification()
    {
        // Ensure this is an AJAX/JSON request
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        require_once __DIR__ . '/../Models/DealPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $token = $input['_csrf'] ?? null;

        if (!verify_csrf($token)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Token bảo mật không hợp lệ.']);
            exit;
        }

        if (empty($id) || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'ID bài viết không hợp lệ.']);
            exit;
        }

        $ok = DealPost::deleteById((int)$id);
        if ($ok) {
            echo json_encode(['ok' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Xóa bài viết thất bại.']);
        }
        exit;
    }

    public function autoMatch()
    {
        // Nếu là POST, chuyển hướng sang GET với các tham số
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qs = [];
            if (!empty($_POST['type'])) $qs['type'] = $_POST['type'];
            if (!empty($_POST['location'])) $qs['location'] = $_POST['location'];
            if (!empty($_POST['price'])) $qs['price'] = $_POST['price'];
            if (!empty($_POST['legal'])) $qs['legal'] = $_POST['legal'];
            if (!empty($_POST['area'])) $qs['area'] = $_POST['area'];
            $qs['searched'] = 1; // Đánh dấu là đã bấm tìm kiếm
            $qs = http_build_query($qs);
            header('Location: ' . BASE_URL . '/admin/auto-match' . ($qs ? ('?' . $qs) : ''));
            exit;
        }

        require_once __DIR__ . '/../Models/Property.php';

        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $price = isset($_GET['price']) ? trim($_GET['price']) : '';
        $legal = isset($_GET['legal']) ? trim($_GET['legal']) : '';
        $area = isset($_GET['area']) ? (float)$_GET['area'] : 0;

        $properties = null; // Khởi tạo là null để không hiển thị gì ban đầu

        // Chỉ thực hiện tìm kiếm nếu có ít nhất một tham số được gửi lên (người dùng đã bấm tìm kiếm)
        if (isset($_GET['searched']) || isset($_GET['type']) || isset($_GET['location']) || isset($_GET['price']) || isset($_GET['legal']) || isset($_GET['area'])) {
            $db = \Database::connect();
            $sql = "SELECT p.*, u.ho_ten 
                    FROM properties p 
                    LEFT JOIN users u ON p.user_id = u.id 
                    WHERE 1=1";
            $params = [];

            if ($type !== '') {
                $sql .= " AND p.loai_bds = ?";
                $params[] = $type;
            }

            if ($location !== '') {
                $sql .= " AND (p.tinh_thanh LIKE ? OR p.quan_huyen LIKE ? OR p.xa_phuong LIKE ? OR p.dia_chi_chi_tiet LIKE ?)";
                $like = '%' . $location . '%';
                array_push($params, $like, $like, $like, $like);
            }

            if ($price !== '') {
                if ($price === 'lt_5') $sql .= " AND p.gia_chao < 5000000000";
                elseif ($price === '5_10') $sql .= " AND p.gia_chao BETWEEN 5000000000 AND 10000000000";
                elseif ($price === '10_20') $sql .= " AND p.gia_chao BETWEEN 10000000000 AND 20000000000";
                elseif ($price === 'gt_20') $sql .= " AND p.gia_chao > 20000000000";
            }

            if ($legal === 'so_do') $sql .= " AND p.phap_ly = 'co_so'";
            if ($legal === 'khong_so') $sql .= " AND p.phap_ly = 'khong_so'";

            if ($area > 0) {
                $sql .= " AND dien_tich >= ?";
                $params[] = $area;
            }

            $sql .= " ORDER BY p.id DESC LIMIT 10";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as &$p) {
                // Lấy ảnh đầu tiên từ bảng property_media để hiển thị thumbnail
                $stmtImg = $db->prepare("SELECT path FROM property_media WHERE property_id = ? LIMIT 1");
                $stmtImg = $db->prepare("SELECT * FROM property_media WHERE property_id = ? LIMIT 1");
                $stmtImg->execute([$p['id']]);
                $img = $stmtImg->fetch(PDO::FETCH_ASSOC);
                $p['thumb'] = $img['path'] ?? null;
                $p['thumb'] = $img['path'] ?? $img['url'] ?? $img['image'] ?? null;
                $p['thumb'] = $img['path'] ?? $img['image_path'] ?? $img['media_path'] ?? $img['url'] ?? $img['image'] ?? null;
            }
        }

        $this->view('admin/auto_match', [
            'properties' => $properties,
            'filters' => ['type' => $type, 'location' => $location, 'price' => $price, 'legal' => $legal, 'area' => $area]
        ]);
    }

    public function policy()
    {
        $this->view('admin/policy');
    }
    // info thông tin nội bộ
    public function info()
    {
        // Load internal posts and pass to view
        require_once __DIR__ . '/../Models/InternalPost.php';
        $posts = InternalPost::getActive(50, 0);
        $this->view('admin/info', ['posts' => $posts]);
    }

    public function addInternalInfo()
    {
        // Handle POST (create new internal info)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            require_once __DIR__ . '/../Models/InternalPost.php';
            require_once __DIR__ . '/../../core/Auth.php';

            $sessionUser = \Auth::user();
            $userId = $sessionUser['id'] ?? null;

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if ($tieu_de === '' || $noi_dung === '') {
                $_SESSION['error'] = 'Vui lòng điền tiêu đề và nội dung.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            $data = [
                'user_id' => $userId,
                'tieu_de' => $tieu_de,
                'noi_dung' => $noi_dung,
                'trang_thai' => 1
            ];

            $postId = InternalPost::create($data);
            if (!$postId) {
                $_SESSION['error'] = 'Lưu thông tin thất bại.';
                header('Location: ' . BASE_URL . '/admin/add-internal-info');
                exit;
            }

            // Handle uploaded media
            $saved = [];
            $maxFiles = 6;
            $maxSize = 8 * 1024 * 1024; // 8MB
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4', 'video/quicktime'];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) $count = $maxFiles;

                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/internal/' . $postId;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                for ($i = 0; $i < $count; $i++) {
                    $err = $_FILES['media']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err !== UPLOAD_ERR_OK) continue;
                    $tmp = $_FILES['media']['tmp_name'][$i];
                    $orig = basename($_FILES['media']['name'][$i] ?? 'file');
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $size = filesize($tmp);
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) continue;

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/internal/' . $postId . '/' . $filename;
                        $saved[] = $webPath;
                    }
                }
            }

            if (!empty($saved)) {
                InternalPost::addImages($postId, $saved);
            }

            $_SESSION['success'] = 'Thêm thông tin nội bộ thành công.';
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $this->view('admin/add-internal-info');
    }
    public function internalInfoList()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        // Handle POST (delete) - supports form POST and JSON (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $token = $_POST['_csrf'] ?? null;

            // JSON body support
            if (!$id) {
                $body = file_get_contents('php://input');
                $json = json_decode($body, true);
                if (is_array($json)) {
                    $id = $json['id'] ?? null;
                    if (isset($json['_csrf'])) $token = $json['_csrf'];
                }
            }
            if (!verify_csrf($token)) {
                $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
                if ($isJson) {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'message' => 'CSRF token invalid']);
                    return;
                }
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-list');
                return;
            }

            if (empty($id) || !is_numeric($id)) {
                $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
                if ($isJson) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Invalid id']);
                    return;
                }
                $_SESSION['error'] = 'ID không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-list');
                return;
            }

            $ok = InternalPost::deleteById((int)$id);

            $isJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);
            if ($ok) {
                if ($isJson) {
                    echo json_encode(['ok' => true]);
                    return;
                }
                $_SESSION['success'] = 'Đã xóa thông tin nội bộ.';
            } else {
                if ($isJson) {
                    http_response_code(500);
                    echo json_encode(['ok' => false, 'message' => 'Xóa thất bại']);
                    return;
                }
                $_SESSION['error'] = 'Xóa thất bại.';
            }

            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            return;
        }

        // GET: list posts with pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        // Use countActive with search support
        $total = InternalPost::countActive($search);
        $pages = (int)ceil($total / $perPage);

        $posts = InternalPost::getActive($perPage, $offset, $search);

        $this->view('admin/internal-info-list', [
            'posts' => $posts,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }

    public function deleteInternalInfo()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        // Kiểm tra method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $token = $_POST['_csrf'] ?? null;

        // Hỗ trợ nhận JSON (nếu frontend gửi JSON)
        if (!$id) {
            $input = file_get_contents('php://input');
            $json = json_decode($input, true);
            if (is_array($json)) {
                $id = $json['id'] ?? null;
                $token = $json['_csrf'] ?? ($json['token'] ?? null);
            }
        }

        // Kiểm tra request JSON hay Form thường
        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

        // Validate Token
        if (!verify_csrf($token)) {
            if ($isJson) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Invalid Token']);
                exit;
            }
            $_SESSION['error'] = 'Token không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            if ($isJson) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
                exit;
            }
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // --- BẮT ĐẦU XỬ LÝ XÓA (KÈM TRY-CATCH) ---
        try {
            // Gọi hàm xóa trong Model
            $ok = InternalPost::deleteById((int)$id);
        } catch (\Throwable $e) {
            // Bắt lỗi SQL (ví dụ: lỗi khóa ngoại chưa xóa ảnh)
            $ok = false;
            // Ghi log lỗi nếu cần: error_log($e->getMessage());

            // Nếu là JSON, trả về lỗi chi tiết để hiển thị lên màn hình
            if ($isJson) {
                http_response_code(500); // Báo lỗi server
                echo json_encode([
                    'ok' => false,
                    'message' => 'Lỗi Server: ' . $e->getMessage() // Quan trọng: Xem lỗi gì ở đây
                ]);
                exit;
            }
        }
        // --- KẾT THÚC XỬ LÝ ---

        // Trả về kết quả
        if ($isJson) {
            echo json_encode(['ok' => $ok]);
            exit;
        }

        if ($ok) $_SESSION['success'] = 'Đã xóa thông tin.';
        else $_SESSION['error'] = 'Xóa thất bại (Có thể do dữ liệu liên quan).';

        header('Location: ' . BASE_URL . '/admin/internal-info-list');
        exit;
    } // <--- Đảm bảo có dấu ngoặc này!
    public function InternalInfoDetail()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $post = InternalPost::getById($id);
        if (!$post) {
            header('Location: ' . BASE_URL . '/admin/info');
            exit;
        }

        $this->view('admin/internal-info-detail', ['post' => $post]);
    }
    public function InternalInfoEdit()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        // If POST, process update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? null;
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
                exit;
            }

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if ($tieu_de === '' || $noi_dung === '') {
                $_SESSION['error'] = 'Vui lòng nhập tiêu đề và nội dung.';
                header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
                exit;
            }

            $data = [
                'tieu_de' => $tieu_de,
                'noi_dung' => $noi_dung,
                'trang_thai' => 1
            ];

            $ok = InternalPost::update($id, $data);

            // Handle removed images (checkboxes with name remove_images[] containing image ids)
            if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
                foreach ($_POST['remove_images'] as $imgId) {
                    $imgId = (int)$imgId;
                    if ($imgId > 0) InternalPost::deleteImageById($imgId);
                }
            }

            // Handle newly uploaded media
            $saved = [];
            $maxFiles = 6;
            $maxSize = 8 * 1024 * 1024; // 8MB
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'video/mp4', 'video/quicktime'];

            if (!empty($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
                $count = count($_FILES['media']['tmp_name']);
                if ($count > $maxFiles) $count = $maxFiles;

                $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads/internal/' . $id;
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

                for ($i = 0; $i < $count; $i++) {
                    $err = $_FILES['media']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err !== UPLOAD_ERR_OK) continue;
                    $tmp = $_FILES['media']['tmp_name'][$i];
                    $orig = basename($_FILES['media']['name'][$i] ?? 'file');
                    $ext = pathinfo($orig, PATHINFO_EXTENSION);
                    $size = filesize($tmp);
                    if ($size > $maxSize) continue;
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) continue;

                    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadsDir . '/' . $filename;
                    if (move_uploaded_file($tmp, $dest)) {
                        $webPath = 'uploads/internal/' . $id . '/' . $filename;
                        $saved[] = $webPath;
                    }
                }
            }

            if (!empty($saved)) InternalPost::addImages($id, $saved);

            if ($ok) {
                $_SESSION['success'] = 'Cập nhật thông tin nội bộ thành công.';
            } else {
                $_SESSION['error'] = 'Cập nhật thất bại.';
            }

            header('Location: ' . BASE_URL . '/admin/internal-info-edit?id=' . $id);
            exit;
        }

        // GET: load and show
        $post = InternalPost::getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL . '/admin/internal-info-list');
            exit;
        }

        $this->view('admin/internal-info-edit', ['post' => $post]);
    }
    public function termsService()
    {
        $this->view('admin/terms-service');
    }
    public function privacyPolicy()
    {
        $this->view('admin/privacy-policy');
    }

    public function cookiePolicy()
    {
        $this->view('admin/cookie-policy');
    }
    public function paymentPolicy()
    {
        $this->view('admin/payment-policy');
    }
    public function removeItem()
    {
        // 1. Kiểm tra phương thức POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json', true, 405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = $user['id'] ?? 0;

        $collectionId = isset($_POST['collection_id']) ? (int)$_POST['collection_id'] : 0;
        $resourceId = isset($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;
        $resourceType = $_POST['resource_type'] ?? 'bat_dong_san';

        if ($collectionId <= 0 || $resourceId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Invalid params']);
            return;
        }

        // --- PHẦN ĐÃ SỬA ---
        // Cho phép 'admin' (hoặc 'super_admin') có quyền xóa item khỏi bất kỳ bộ sưu tập nào (force = true)
        $force = false;
        if (isset($user['quyen']) && ($user['quyen'] === 'admin')) {
            $force = true;
        }
        // -------------------

        $ok = Collection::removeItem($collectionId, $resourceId, $userId, $resourceType, $force);

        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
        exit; // Thêm exit để đảm bảo không có ký tự thừa nào được in ra sau JSON
    }
    public function resourceDetail()
    {
        require_once __DIR__ . '/../Models/Property.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            $_SESSION['error'] = 'ID tài nguyên không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        // Lấy thông tin bất động sản
        $property = Property::findById($id);

        // --- ĐOẠN CODE MỚI THÊM VÀO ĐỂ LẤY TÊN USER (HOTFIX) ---
        if ($property && !empty($property['user_id'])) {
            // Thử kết nối DB thủ công để lấy tên user (vì Model Property chưa JOIN)
            try {
                // Giả định class Controller có biến $this->db kết nối PDO
                // Nếu framework của bạn dùng static DB, hãy chỉnh lại dòng này
                if (isset($this->db)) {
                    $sqlUser = "SELECT ho_ten, avatar, so_dien_thoai, link_fb FROM user WHERE id = :uid";
                    $stmtUser = $this->db->prepare($sqlUser);
                    $stmtUser->execute([':uid' => $property['user_id']]);
                    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

                    if ($userData) {
                        $property['ho_ten'] = $userData['ho_ten'];
                        $property['avatar'] = $userData['avatar'];
                        // Map số điện thoại user sang biến view cần dùng nếu tin đăng ko có sđt
                        if (empty($property['so_dien_thoai'])) {
                            $property['so_dien_thoai'] = $userData['so_dien_thoai'];
                        }
                        $property['user_phone'] = $userData['so_dien_thoai'];
                        $property['link_fb'] = $userData['link_fb'];
                    }
                }
            } catch (Exception $e) {
                // Bỏ qua lỗi nếu không kết nối được
            }
        }
        // --- HẾT ĐOẠN HOTFIX ---

        if (!$property) {
            $_SESSION['error'] = 'Không tìm thấy tài nguyên.';
            header('Location: ' . BASE_URL . '/admin/management-resource');
            exit;
        }

        $media = Property::getMedia($id);

        $this->view('admin/resource-detail', [
            'property' => $property,
            'media' => $media
        ]);
    }

    public function addPersonnel()
    {
        // This method handles the form from `superadmin/add-personnel`
        // It applies the referral code logic as requested.
        $this->requireRole([ROLE_SUPER_ADMIN]);
        require_once __DIR__ . '/../Models/User.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // --- VALIDATION LOGIC FROM USER REQUEST ---
            $quyen = $_POST['quyen'] ?? ''; // 'user' (Đầu khách) or 'admin' (Đầu chủ)
            $nguoi_gioi_thieu_code = trim($_POST['nguoi_gioi_thieu'] ?? '');

            if (empty($nguoi_gioi_thieu_code)) {
                $_SESSION['error'] = 'Vui lòng nhập mã người giới thiệu.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // Assume User model has a method to find by 'ma_nhan_su'
            $referrer = User::findByMaNhanSu($nguoi_gioi_thieu_code);

            if (!$referrer) {
                $_SESSION['error'] = 'Mã người giới thiệu không tồn tại.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            $referrerRole = $referrer['quyen']; // Assuming 'quyen' is the role column

            if ($quyen === 'user') { // Creating "Đầu khách"
                // Referrer must be "Đầu chủ" (role 'admin')
                if ($referrerRole !== 'admin') {
                    $_SESSION['error'] = 'Tài khoản "Đầu khách" phải được giới thiệu bởi một "Đầu chủ".';
                    $_SESSION['old'] = $_POST;
                    header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                    exit;
                }
            } elseif ($quyen === 'admin') { // Creating "Đầu chủ"
                // Referrer must be "Cấp quản lý" (role 'super_admin')
                if ($referrerRole !== 'super_admin') {
                    $_SESSION['error'] = 'Tài khoản "Đầu chủ" phải được giới thiệu bởi một "Quản trị viên cấp cao".';
                    $_SESSION['old'] = $_POST;
                    header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                    exit;
                }
            }
            // --- END OF VALIDATION LOGIC ---

            $data = [
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'so_dien_thoai' => trim($_POST['so_dien_thoai'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'nam_sinh' => trim($_POST['nam_sinh'] ?? null),
                'phong_ban' => trim($_POST['phong_ban'] ?? null),
                'vi_tri' => trim($_POST['vi_tri'] ?? null),
                'quyen' => $quyen,
                'dia_chi' => trim($_POST['dia_chi'] ?? null),
                'nguoi_gioi_thieu' => $nguoi_gioi_thieu_code,
                'so_cccd' => trim($_POST['so_cccd'] ?? null),
                'trang_thai' => 1, // Default to active
            ];

            // Basic validation
            if (empty($data['ho_ten']) || empty($data['so_dien_thoai']) || empty($data['password']) || empty($data['quyen'])) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ các trường bắt buộc.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // Check for existing user
            if (User::findByPhone($data['so_dien_thoai'])) {
                $_SESSION['error'] = 'Số điện thoại đã được sử dụng.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

            // Create user (returns new user ID)
            $userId = User::create($data);

            if (!$userId) {
                $_SESSION['error'] = 'Không thể tạo người dùng. Đã có lỗi xảy ra.';
                $_SESSION['old'] = $_POST;
                header('Location: ' . BASE_URL . '/superadmin/add-personnel');
                exit;
            }

            // Handle CCCD image upload
            if (!empty($_FILES['anh_cccd']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/cccd/' . $userId . '/';
                $savedPath = upload_file($_FILES['anh_cccd'], $uploadDir);
                if ($savedPath) {
                    User::updateCccdImage($userId, $savedPath);
                }
            }

            $_SESSION['success'] = 'Thêm nhân sự thành công.';
            header('Location: ' . BASE_URL . '/superadmin/management-owner'); // Redirect to user list
            exit;
        }

        // For GET request
        $this->view('superadmin/add-personnel');
    }
}
