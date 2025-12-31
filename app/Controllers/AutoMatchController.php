<?php

class AutoMatchController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function index()
    {
        require_once __DIR__ . '/../Models/Property.php';

        $type = isset($_GET['type']) ? trim($_GET['type']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $price = isset($_GET['price']) ? trim($_GET['price']) : '';
        $legal = isset($_GET['legal']) ? trim($_GET['legal']) : '';
        $area = isset($_GET['area']) ? (float)$_GET['area'] : 0;

        $properties = [];

        // Only run the query after user has performed a search (clicked 'Tìm kiếm')
        $shouldSearch = isset($_GET['searched']) && $_GET['searched'] == '1';

        if ($shouldSearch) {
            $db = Database::connect();
            // include poster name from users table so view can use $p['ho_ten']
            $sql = "SELECT properties.*, u.ho_ten FROM properties LEFT JOIN users u ON properties.user_id = u.id WHERE 1=1";
            $params = [];

            if ($type !== '') {
                // expecting 'ban' or 'cho_thue'
                $sql .= " AND loai_bds = ?";
                $params[] = $type;
            }

            if ($location !== '') {
                $sql .= " AND (tinh_thanh LIKE ? OR quan_huyen LIKE ? OR xa_phuong LIKE ? OR dia_chi_chi_tiet LIKE ?)";
                $like = '%' . $location . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            if ($price !== '') {
                // interpret price ranges in VND
                if ($price === 'lt_5') {
                    $sql .= " AND gia_chao < ?";
                    $params[] = 5000000000;
                } elseif ($price === '5_10') {
                    $sql .= " AND gia_chao BETWEEN ? AND ?";
                    $params[] = 5000000000;
                    $params[] = 10000000000;
                } elseif ($price === '10_20') {
                    $sql .= " AND gia_chao BETWEEN ? AND ?";
                    $params[] = 10000000000;
                    $params[] = 20000000000;
                } elseif ($price === 'gt_20') {
                    $sql .= " AND gia_chao > ?";
                    $params[] = 20000000000;
                }
            }

            if ($legal !== '') {
                // legal options: 'so_do' or 'khong_so'
                if ($legal === 'so_do') {
                    $sql .= " AND phap_ly = ?";
                    $params[] = 'co_so';
                } elseif ($legal === 'khong_so') {
                    $sql .= " AND phap_ly = ?";
                    $params[] = 'khong_so';
                }
            }

            if ($area > 0) {
                $sql .= " AND dien_tich >= ?";
                $params[] = $area;
            }

            $sql .= " ORDER BY id DESC LIMIT 100";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Lấy ảnh đại diện cho mỗi tin đăng
        foreach ($properties as &$p) {
            $p['thumb'] = Property::getFirstImagePath((int)$p['id']);
        }

        $this->view('superadmin/auto-match', [
            'properties' => $properties,
            'filters' => ['type' => $type, 'location' => $location, 'price' => $price, 'legal' => $legal, 'area' => $area]
        ]);
    }

    // Alias to support POST route if needed
    public function autoMatch()
    {
        // Accept form POST then redirect to GET with params
        $qs = [];
        if (!empty($_POST['type'])) $qs['type'] = $_POST['type'];
        if (!empty($_POST['location'])) $qs['location'] = $_POST['location'];
        if (!empty($_POST['price'])) $qs['price'] = $_POST['price'];
        if (!empty($_POST['legal'])) $qs['legal'] = $_POST['legal'];
        if (!empty($_POST['area'])) $qs['area'] = $_POST['area'];
        $qs = http_build_query($qs);
        header('Location: ' . BASE_URL . '/superadmin/auto-match' . ($qs ? ('?' . $qs) : ''));
        exit;
    }
}
