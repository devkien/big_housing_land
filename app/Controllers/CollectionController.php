<?php

class CollectionController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function collection()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $user = \Auth::user();
        $userId = $user['id'] ?? null;
        $collections = Collection::allWithCount($search, $userId);

        $this->view('superadmin/collection', [
            'collections' => $collections,
            'search' => $search
        ]);
    }

    public function collectionDetail()
    {
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../Models/Property.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID bộ sưu tập không hợp lệ.';
            header('Location: ' . BASE_URL . '/superadmin/collection');
            exit;
        }

        $collection = Collection::getById($id);
        if (!$collection) {
            $_SESSION['error'] = 'Không tìm thấy bộ sưu tập.';
            header('Location: ' . BASE_URL . '/superadmin/collection');
            exit;
        }

        // Read optional filters from query string
        $filters = [];
        if (isset($_GET['q']) && trim($_GET['q']) !== '') $filters['q'] = trim($_GET['q']);
        if (isset($_GET['status']) && trim($_GET['status']) !== '' && trim($_GET['status']) !== 'all') $filters['status'] = trim($_GET['status']);
        if (isset($_GET['address']) && trim($_GET['address']) !== '') $filters['address'] = trim($_GET['address']);

        // Pass null to getItems so collection detail shows resources regardless of resource_type
        $items = Collection::getItems($id, null, $filters);

        $this->view('superadmin/collection-detail', [
            'collection' => $collection,
            'items' => $items,
            'filters' => $filters
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
                    // Save relative path from project root
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
                header('Location: ' . BASE_URL . '/superadmin/collection');
                exit;
            } else {
                $_SESSION['error'] = 'Không thể tạo bộ sưu tập';
            }
        }

        $this->view('superadmin/cre-collection');
    }

    // AJAX: rename collection
    public function renameCollection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json', true, 405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['ten_bo_suu_tap']) ? trim($_POST['ten_bo_suu_tap']) : '';

        if ($id <= 0 || $name === '') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Invalid params']);
            return;
        }

        $ok = Collection::updateName($id, $name);
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
    }

    // AJAX: delete collection
    public function deleteCollection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json', true, 405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Invalid id']);
            return;
        }

        $ok = Collection::deleteById($id);
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
    }

    // AJAX: remove single item from collection
    public function removeItem()
    {
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

        // Allow super_admin to remove items from any collection
        $force = false;
        if (isset($user['quyen']) && $user['quyen'] === 'super_admin') $force = true;
        $ok = Collection::removeItem($collectionId, $resourceId, $userId, $resourceType, $force);
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
    }
}
