<?php

class ResourceController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Only super_admin
        $this->requireRole([ROLE_SUPER_ADMIN]);
    }

    public function resourcePost()
    {
        // If POST: handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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

            // ===== VALIDATE MÃ SỐ SỔ =====
            if ($phap_ly === 'co_so' && empty(trim($_POST['ma_so_so'] ?? ''))) {
                $_SESSION['error'] = 'Vui lòng nhập Mã số sổ khi chọn pháp lý là "Có sổ".';
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
                exit;
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
                    header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                    header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
                header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
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
            header('Location: ' . BASE_URL . '/superadmin/management-resource-post');
            exit;
        }

        $this->view('superadmin/resource-post');
    }

    public function resource()
    {
        // list kho_nha_dat
        require_once __DIR__ . '/../Models/Property.php';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $search = isset($_GET['q']) ? trim($_GET['q']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $address = isset($_GET['address']) ? trim($_GET['address']) : null;

        // prefer address as explicit search term
        $searchTerm = $address ?: $search;

        // If search term looks like a resource code, try exact match on ma_hien_thi first
        $properties = [];
        if ($searchTerm) {
            $code = trim($searchTerm);
            $found = Property::findByMaHienThi($code);
            if ($found) {
                $total = 1;
                $pages = 1;
                $offset = 0;
                $properties = [$found];
            } else {
                $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
                $pages = (int)ceil($total / $perPage);
                $offset = ($page - 1) * $perPage;
                $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);
            }
        } else {
            $total = Property::countByLoaiKho('kho_nha_dat', $searchTerm, $status);
            $pages = (int)ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $properties = Property::getByLoaiKho('kho_nha_dat', $perPage, $offset, $searchTerm, $status);
        }

        // load collections for "save to collection" modal (only collections owned by current superadmin)
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $userId = $user['id'] ?? null;
        $collections = Collection::allWithCount(null, $userId);
        // build map of property_id => count of collections that include it
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        // resource type for this controller action is 'kho_nha_dat'
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_nha_dat', $userId);

        $this->view('superadmin/resource', [
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

        // load collections and build collection map (only those owned by current superadmin)
        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $userId = $user['id'] ?? null;
        $collections = Collection::allWithCount(null, $userId);
        $propertyIds = array_map(function ($r) {
            return (int)($r['id'] ?? 0);
        }, $properties);
        // resource type for this action is 'kho_cho_thue'
        $collectionMap = Collection::getCountsForProperties(array_filter($propertyIds), 'kho_cho_thue', $userId);

        $this->view('superadmin/resource-rent', [
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
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        $media = Property::getMedia($id);

        $this->view('superadmin/resource-detail', [
            'property' => $property,
            'media' => $media
        ]);
    }

    // AJAX: save property into selected collections
    public function saveToCollections()
    {
        // Accept JSON body OR standard form POST (fallback)
        require_once __DIR__ . '/../Helpers/functions.php';
        $body = file_get_contents('php://input');
        $logPath = __DIR__ . '/../../storage/logs/save_collections.log';
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Raw body: " . $body . "\n", FILE_APPEND);

        $data = json_decode($body, true);
        // If not JSON, try form-encoded POST
        if (!is_array($data) || empty($data)) {
            if (!empty($_POST)) {
                $data = $_POST;
                @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Using \\$_POST payload: " . json_encode($data) . "\n", FILE_APPEND);
            }
        }

        header('Content-Type: application/json');

        if (!$data || !isset($data['property_id']) || !isset($data['collections'])) {
            @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Missing params: " . json_encode($data) . "\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
            return;
        }

        $csrfOk = verify_csrf($data['_csrf'] ?? ($_POST['_csrf'] ?? null));
        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - CSRF ok: " . ($csrfOk ? '1' : '0') . "\n", FILE_APPEND);
        if (!$csrfOk) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $propertyId = (int)$data['property_id'];
        $collections = is_array($data['collections']) ? $data['collections'] : [];
        $resourceType = isset($data['resource_type']) ? trim($data['resource_type']) : 'bat_dong_san';

        if ($propertyId <= 0) {
            @file_put_contents($logPath, date('Y-m-d H:i:s') . " - Invalid property_id: {$propertyId}\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid property_id']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';

        // Use syncItems to ensure resource_id/resource_type semantics are used
        // and that after this call the resource belongs exactly to provided collections.
        $result = Collection::syncItems($collections, $propertyId, $resourceType);
        // syncItems returns number of inserted rows or false on error
        if ($result === false) {
            @file_put_contents($logPath, date('Y-m-d H:i:s') . " - syncItems failed for prop {$propertyId} resource_type={$resourceType} collections=" . json_encode($collections) . "\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Failed to save collections']);
            return;
        }

        @file_put_contents($logPath, date('Y-m-d H:i:s') . " - syncPropertyCollections result: {$result}\n", FILE_APPEND);
        echo json_encode(['ok' => true, 'added' => $result]);
    }

    // AJAX handler to update property status
    public function updateStatus()
    {
        // Expect JSON body: { id: int, status: 'ban_manh'|'tam_dung_ban'|..., _csrf: token }
        require_once __DIR__ . '/../Helpers/functions.php';
        // Read JSON payload
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!$data) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid payload']);
            return;
        }

        if (!verify_csrf($data['_csrf'] ?? null)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $statusInput = trim($data['status'] ?? '');
        if (!$id || $statusInput === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
            return;
        }

        // Allow either display labels or internal enum values
        $map = [
            'Bán mạnh' => 'ban_manh',
            'Tạm dừng bán' => 'tam_dung_ban',
            'Dừng bán' => 'dung_ban',
            'Đã bán' => 'da_ban',
            'Tăng chào' => 'tang_chao',
            'Hạ chào' => 'ha_chao',
            'ban_manh' => 'ban_manh',
            'tam_dung_ban' => 'tam_dung_ban',
            'dung_ban' => 'dung_ban',
            'da_ban' => 'da_ban',
            'tang_chao' => 'tang_chao',
            'ha_chao' => 'ha_chao'
        ];

        $trang_thai = $map[$statusInput] ?? null;
        if (!$trang_thai) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Invalid status value']);
            return;
        }

        require_once __DIR__ . '/../Models/Property.php';
        $ok = Property::updateStatus($id, $trang_thai);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(['ok' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Không thể cập nhật cơ sở dữ liệu']);
        }
    }

    public function getCollectionsForProperty()
    {
        require_once __DIR__ . '/../Helpers/functions.php';
        header('Content-Type: application/json');

        $id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing property_id']);
            return;
        }

        require_once __DIR__ . '/../Models/Collection.php';
        require_once __DIR__ . '/../../core/Auth.php';

        $user = \Auth::user();
        $userId = (int)($user['id'] ?? 0);

        // Restrict to collections owned by the current superadmin (isolation per account)
        $rawIds = Collection::getCollectionIdsForProperty($id, $userId);

        // 🔥 ÉP KIỂU + LẤY GIÁ TRỊ THUẦN
        $ids = array_map(function ($row) {
            if (is_array($row)) {
                return (int)($row['collection_id'] ?? 0);
            }
            return (int)$row;
        }, $rawIds);

        // bỏ các giá trị rỗng
        $ids = array_values(array_filter($ids));

        echo json_encode([
            'ok' => true,
            'collections' => $ids
        ]);
    }

    public function editResource()
    {
        require_once __DIR__ . '/../Models/Property.php';
        $id = $_GET['id'] ?? ($_POST['id'] ?? null);

        if (!$id) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        $property = Property::findById((int)$id);
        if (!$property) {
            $_SESSION['error'] = 'Không tìm thấy tài nguyên';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Helpers/functions.php';
            if (!verify_csrf($_POST['_csrf'] ?? null)) {
                $_SESSION['error'] = 'Token không hợp lệ.';
                header('Location: ' . BASE_URL . '/superadmin/management-resource-edit?id=' . $id);
                exit;
            }

            $data = [
                'tieu_de' => trim($_POST['tieu_de'] ?? ''),
                'loai_bds' => $_POST['loai_bds'] ?? 'ban',
                'phap_ly' => $_POST['phap_ly'] ?? 'co_so',
                'ma_so_so' => ($_POST['phap_ly'] === 'co_so') ? (trim($_POST['ma_so_so'] ?? '') ?: null) : null,
                'dien_tich' => (float)($_POST['dien_tich'] ?? 0),
                'don_vi_dien_tich' => $_POST['don_vi_dien_tich'] ?? 'm2',
                'chieu_dai' => (float)($_POST['chieu_dai'] ?? 0),
                'chieu_rong' => (float)($_POST['chieu_rong'] ?? 0),
                'so_tang' => (int)($_POST['so_tang'] ?? 0),
                'gia_chao' => (float)($_POST['gia_chao'] ?? 0),
                'don_vi_gia' => $_POST['don_vi_gia'] ?? 'nguyen_can',
                'trich_thuong_gia_tri' => trim($_POST['trich_thuong_gia_tri'] ?? ''),
                'trich_thuong_don_vi' => $_POST['trich_thuong_don_vi'] ?? '%',
                'tinh_thanh' => trim($_POST['tinh_thanh'] ?? ''),
                'quan_huyen' => trim($_POST['quan_huyen'] ?? ''),
                'xa_phuong' => trim($_POST['xa_phuong'] ?? ''),
                'dia_chi_chi_tiet' => trim($_POST['dia_chi_chi_tiet'] ?? ''),
                'mo_ta' => trim($_POST['mo_ta'] ?? ''),
                'phong_ban' => trim($_POST['phong_ban'] ?? ''),
                'trang_thai' => $_POST['trang_thai'] ?? 'ban_manh',
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0
            ];

            Property::update((int)$id, $data);
            $_SESSION['success'] = 'Cập nhật thành công';
            header('Location: ' . BASE_URL . '/superadmin/management-resource');
            exit;
        }

        $this->view('superadmin/resource-edit', ['property' => $property]);
    }

    public function deleteResource()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Property.php';
            $id = $_POST['id'] ?? null;
            if ($id && Property::delete((int)$id)) {
                $_SESSION['success'] = 'Đã xóa tài nguyên.';
            } else {
                $_SESSION['error'] = 'Xóa thất bại.';
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/superadmin/management-resource'));
            exit;
        }
    }
}
