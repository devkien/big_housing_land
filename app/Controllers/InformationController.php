<?php

class InformationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
    }

    public function info()
    {
        // Load internal posts and pass to view
        require_once __DIR__ . '/../Models/InternalPost.php';
        $posts = InternalPost::getActive(50, 0);
        $this->view('superadmin/info', ['posts' => $posts]);
    }

    public function addInternalInfo()
    {
        // Handle POST (create new internal info)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/add-internal-info');
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
                header('Location: ' . BASE_URL . '/superadmin/add-internal-info');
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
                header('Location: ' . BASE_URL . '/superadmin/add-internal-info');
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
            header('Location: ' . BASE_URL . '/superadmin/info');
            exit;
        }

        $this->view('superadmin/add-internal-info');
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
                header('Location: ' . BASE_URL . '/superadmin/internal-info-list');
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
                header('Location: ' . BASE_URL . '/superadmin/internal-info-list');
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

            header('Location: ' . BASE_URL . '/superadmin/internal-info-list');
            return;
        }

        // GET: list posts with pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = InternalPost::countActive();
        $pages = (int)ceil($total / $perPage);

        $posts = InternalPost::getActive($perPage, $offset);

        $this->view('superadmin/internal-info-list', [
            'posts' => $posts,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'perPage' => $perPage
        ]);
    }

    // AJAX endpoint to pin/unpin an internal post
    public function pinInternalInfo()
    {
        require_once __DIR__ . '/../Helpers/functions.php';
        require_once __DIR__ . '/../Models/InternalPost.php';

        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        header('Content-Type: application/json');

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid payload']);
            return;
        }

        if (!verify_csrf($data['_csrf'] ?? null)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $pinned = isset($data['pinned']) ? (int)$data['pinned'] : null;

        if ($id <= 0 || ($pinned !== 0 && $pinned !== 1)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid parameters']);
            return;
        }

        $ok = InternalPost::setPinned($id, $pinned === 1);
        if ($ok) {
            echo json_encode(['ok' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'DB update failed']);
        }
    }
    public function InternalInfoDetail()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/superadmin/info');
            exit;
        }

        $post = InternalPost::getById($id);
        if (!$post) {
            header('Location: ' . BASE_URL . '/superadmin/info');
            exit;
        }

        $this->view('superadmin/internal-info-detail', ['post' => $post]);
    }
    public function InternalInfoEdit()
    {
        require_once __DIR__ . '/../Models/InternalPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/internal-info-list');
            exit;
        }

        // If POST, process update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? null;
            if (!verify_csrf($token)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/internal-info-edit?id=' . $id);
                exit;
            }

            $tieu_de = trim($_POST['tieu_de'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if ($tieu_de === '' || $noi_dung === '') {
                $_SESSION['error'] = 'Vui lòng nhập tiêu đề và nội dung.';
                header('Location: ' . BASE_URL . '/superadmin/internal-info-edit?id=' . $id);
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

            header('Location: ' . BASE_URL . '/superadmin/internal-info-edit?id=' . $id);
            exit;
        }

        // GET: load and show
        $post = InternalPost::getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL . '/superadmin/internal-info-list');
            exit;
        }

        $this->view('superadmin/internal-info-edit', ['post' => $post]);
    }

    public function createNotification()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/cre-notification');
                exit;
            }

            $ma_nhan_su = trim($_POST['ma_nhan_su'] ?? '');
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if (empty($ma_nhan_su) || empty($noi_dung)) {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
                header('Location: ' . BASE_URL . '/superadmin/cre-notification');
                exit;
            }

            require_once __DIR__ . '/../Models/User.php';
            $user = User::findByMaNhanSu($ma_nhan_su);

            if (!$user) {
                $_SESSION['error'] = 'Mã nhân viên không tồn tại trong hệ thống.';
                header('Location: ' . BASE_URL . '/superadmin/cre-notification');
                exit;
            }

            // Logic lưu thông báo vào database sẽ được thêm ở đây
            
            $_SESSION['success'] = 'Đăng thông báo vụ chốt thành công.';
            header('Location: ' . BASE_URL . '/superadmin/cre-notification');
            exit;
        }

        $this->view('superadmin/cre-notification');
    }
}
