<?php

class NotificationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function notification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $offset = ($page - 1) * $perPage;

        $posts = DealPost::getList($perPage, $offset, $search);

        $this->view('superadmin/notification', [
            'posts' => $posts,
            'page' => $page,
            'search' => $search
        ]);
    }

    public function creNotification()
    {
        // Handle POST (create new deal post)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/DealPost.php';
            require_once __DIR__ . '/../Helpers/functions.php';
            $user = \Auth::user();
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
                $allowed = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'video/mp4',
                    'video/webm',
                    'video/quicktime'
                ];
                $maxSize = 20 * 1024 * 1024; // 20MB
                for ($i = 0; $i < count($_FILES['images']['tmp_name']); $i++) {
                    $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                    if ($err === UPLOAD_ERR_NO_FILE) continue;
                    if ($err !== UPLOAD_ERR_OK) {
                        $errors[] = 'Lỗi upload file ' . ($_FILES['images']['name'][$i] ?? '') . '.';
                        continue;
                    }
                    $tmp = $_FILES['images']['tmp_name'][$i];
                    $size = filesize($tmp);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowed, true)) {
                        $errors[] = 'Định dạng file không hợp lệ: ' . ($_FILES['images']['name'][$i] ?? '');
                        continue;
                    }
                    if ($size > $maxSize) {
                        $errors[] = 'Kích thước file quá lớn (max 5MB): ' . ($_FILES['images']['name'][$i] ?? '');
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
                // Determine which user should be recorded as the closer/author.
                $targetUserId = $userId; // default: the creator
                if (!empty($ma_nhan_vien)) {
                    require_once __DIR__ . '/../Models/User.php';
                    $found = User::findByMaNhanSu($ma_nhan_vien);
                    if ($found && !empty($found['id'])) {
                        $targetUserId = (int)$found['id'];
                    } else {
                        $errors[] = 'Mã nhân viên không tồn tại trong hệ thống.';
                    }
                }

                if (empty($errors)) {
                    $data = [
                        'user_id' => $targetUserId,
                        'bat_dong_san_id' => null,
                        'tieu_de' => $tieu_de,
                        'noi_dung' => $noi_dung,
                        'trang_thai' => 1
                    ];

                    $postId = DealPost::create($data);
                    if ($postId && !empty($saved)) {
                        DealPost::addImages($postId, $saved);
                    }

                    header('Location: ' . BASE_URL . '/superadmin/notification');
                    exit;
                }
            }

            // If validation failed, re-render form with errors and old input
            $this->view('superadmin/cre-notification', [
                'errors' => $errors,
                'old' => [
                    'tieu_de' => $tieu_de,
                    'noi_dung' => $noi_dung,
                    'ma_nhan_vien' => $ma_nhan_vien
                ]
            ]);
            return;
        }

        $this->view('superadmin/cre-notification');
    }

    public function editNotification()
    {
        require_once __DIR__ . '/../Models/DealPost.php';
        require_once __DIR__ . '/../Helpers/functions.php';

        $id = $_GET['id'] ?? 0;
        if (empty($id) || !is_numeric($id)) {
            $_SESSION['error'] = 'ID bài viết không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/notification');
            exit;
        }
        $id = (int)$id;

        // Handle POST for update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token bảo mật không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/edit-notification?id=' . $id);
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
            header('Location: ' . BASE_URL . '/superadmin/edit-notification?id=' . $id);
            exit;
        }

        // Handle GET to show form
        $post = DealPost::getById($id);
        if (!$post) {
            $_SESSION['error'] = 'Không tìm thấy bài viết.';
            header('Location: ' . BASE_URL . '/superadmin/notification');
            exit;
        }

        $this->view('superadmin/edit-notification', ['post' => $post]);
    }

    public function deleteNotification()
    {
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
}
