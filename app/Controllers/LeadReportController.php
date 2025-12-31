<?php

class LeadReportController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function list()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;

        $offset = ($page - 1) * $perPage;
        $reports = LeadReport::getList($perPage, $offset, $search);
        $total = LeadReport::countAll($search);
        $totalPages = (int)ceil($total / $perPage);

        $this->view('superadmin/report-list', [
            'reports' => $reports,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function detail()
    {
        require_once __DIR__ . '/../Models/LeadReport.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/superadmin/report-list');
            exit;
        }

        $report = LeadReport::getById($id);
        if (!$report) {
            header('Location: ' . BASE_URL . '/superadmin/report-list');
            exit;
        }

        $this->view('superadmin/report-customer', [
            'report' => $report
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

        // Import Model
        require_once __DIR__ . '/../Models/LeadReport.php';

        // Gọi hàm xóa trong Model
        if (LeadReport::delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: Không thể xóa báo cáo']);
        }
        exit;
    }
}
