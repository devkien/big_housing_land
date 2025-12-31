<?php
// 1. Nhúng file PHPMailer từ thư mục dự án (đường dẫn tương đối)
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../Models/User.php';


class AuthController extends Controller
{
    public function login()
    {
        // Nếu đã đăng nhập, điều hướng về trang chủ tương ứng
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $role = $user['quyen'] ?? 'user';

            if ($role === 'super_admin') {
                header('Location: ' . BASE_URL . '/superadmin/home');
                exit;
            }

            if ($role === 'admin') {
                header('Location: ' . BASE_URL . '/admin/home');
                exit;
            }

            // Mặc định user thường
            header('Location: ' . BASE_URL . '/home');
            exit;
        }

        // Đọc cookie để điền lại form
        $identity = $_COOKIE['bh_identity'] ?? '';
        // Nếu có dữ liệu nhập cũ (do lỗi đăng nhập), ưu tiên hiển thị
        if (isset($_SESSION['old_identity'])) {
            $identity = $_SESSION['old_identity'];
            unset($_SESSION['old_identity']);
        }
        $password = $_COOKIE['bh_password'] ?? '';
        $remember = isset($_COOKIE['bh_remember']);

        $this->view('auth/login', [
            'identity' => $identity,
            'password' => $password,
            'remember' => $remember
        ]);
    }

    public function handleLogin()
    {
        $identity = $_POST['identity'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Basic validation
        if (empty($identity) || empty($password)) {
            $_SESSION['old_identity'] = $identity;
            $_SESSION['error'] = 'Vui lòng nhập số điện thoại và mật khẩu';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = User::findForLogin($identity);

        if (!$user) {
            $_SESSION['old_identity'] = $identity;
            $_SESSION['error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['old_identity'] = $identity;
            $_SESSION['error'] = 'Mật khẩu không đúng';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $status = (int)($user['trang_thai'] ?? 0);
        if ($status !== 1) {
            $_SESSION['old_identity'] = $identity;
            if ($status === 0) {
                $_SESSION['error'] = 'Tài khoản đang chờ duyệt vui lòng chờ xét duyệt hoặc liên hệ quản trị viên để biết thêm chi tiết';
            } else {
                $_SESSION['error'] = 'Tài khoản đã bị khóa';
            }
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $_SESSION['user'] = $user;

        // Xử lý lưu cookie nếu người dùng chọn "Lưu mật khẩu"
        if ($remember) {
            $cookie_time = time() + (86400 * 30); // 30 ngày
            setcookie('bh_identity', $identity, $cookie_time, BASE_PATH . '/');
            setcookie('bh_password', $password, $cookie_time, BASE_PATH . '/');
            setcookie('bh_remember', '1', $cookie_time, BASE_PATH . '/');
        } else {
            // Xóa cookie nếu không chọn
            setcookie('bh_identity', '', time() - 3600, BASE_PATH . '/');
            setcookie('bh_password', '', time() - 3600, BASE_PATH . '/');
            setcookie('bh_remember', '', time() - 3600, BASE_PATH . '/');
        }

        // KIỂM TRA ROLE VÀ REDIRECT - Sử dụng trực tiếp cột 'quyen' từ CSDL để tăng cường bảo mật
        $role = $user['quyen'] ?? 'user';

        if ($role === 'super_admin') {
            header('Location: ' . BASE_URL . '/superadmin/home');
            exit;
        }

        if ($role === 'admin') {
            header('Location: ' . BASE_URL . '/admin/home');
            exit;
        }

        // Mặc định user thường
        header('Location: ' . BASE_URL . '/home');
        exit;
    }


    public function register()
    {
        $this->view('auth/register');
    }

    public function handleRegister()
    {
        $data = [
            'so_dien_thoai'   => trim($_POST['so_dien_thoai'] ?? ''),
            'so_cccd'         => trim($_POST['so_cccd'] ?? ''),
            'password'        => $_POST['password'] ?? '',
            'ho_ten'          => trim($_POST['ho_ten'] ?? ''),
            'nam_sinh'        => $_POST['nam_sinh'] ?? '',
            'dia_chi'         => trim($_POST['dia_chi'] ?? ''),
            'gioi_tinh'       => $_POST['gioi_tinh'] ?? '',
            'email'           => trim($_POST['email'] ?? ''),
            'link_fb'         => trim($_POST['link_fb'] ?? ''),
            'ma_gioi_thieu'   => trim($_POST['ma_gioi_thieu'] ?? ''),
            'loai_tai_khoan'  => $_POST['loai_tai_khoan'] ?? '',
            'phong_ban'       => $_POST['phong_ban'] ?? '',
            'vi_tri'          => $_POST['vi_tri'] ?? '',
        ];
        // Helper lưu lại input cũ (trừ password)
        $saveOldInput = function () use ($data) {
            $old = $data;
            unset($old['password']);
            $_SESSION['old'] = $old;
        };

        // ===== VALIDATE BẮT BUỘC =====
        if (
            empty($data['so_dien_thoai']) ||
            empty($data['so_cccd']) ||
            empty($data['password']) ||
            empty($data['ho_ten']) ||
            empty($data['nam_sinh']) ||
            empty($data['dia_chi']) ||
            empty($data['gioi_tinh']) ||
            empty($data['email']) ||
            empty($data['link_fb']) ||
            empty($data['ma_gioi_thieu']) ||
            empty($data['loai_tai_khoan']) ||
            empty($data['phong_ban']) ||
            empty($data['vi_tri'])
        ) {
            $saveOldInput();
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK ẢNH CCCD BẮT BUỘC =====
        if (empty($_FILES['anh_cccd']) || $_FILES['anh_cccd']['error'] !== UPLOAD_ERR_OK) {
            $saveOldInput();
            $_SESSION['error'] = 'Vui lòng tải lên ảnh CCCD';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG SĐT =====
        if (User::findByPhone($data['so_dien_thoai'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Số điện thoại đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG EMAIL (NẾU CÓ) =====
        if (!empty($data['email']) && User::findByEmail($data['email'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Email đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK TRÙNG CCCD (NẾU CÓ) =====
        if (!empty($data['so_cccd']) && User::findByCCCD($data['so_cccd'])) {
            $saveOldInput();
            $_SESSION['error'] = 'Số CCCD đã tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // ===== CHECK MÃ GIỚI THIỆU TỒN TẠI & PHÂN QUYỀN =====
        $referrer = User::findByMaNhanSu($data['ma_gioi_thieu']);
        if (!$referrer) {
            $saveOldInput();
            $_SESSION['error'] = 'Mã giới thiệu không tồn tại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        // Validate hierarchy (Phân cấp giới thiệu)
        $regType = $data['loai_tai_khoan']; // 'nhan_vien' or 'quan_ly'
        // Đảm bảo loại tài khoản hợp lệ để kiểm tra (mặc định là nhan_vien nếu sai)
        if (!in_array($regType, ['nhan_vien', 'quan_ly', 'admin'])) $regType = 'nhan_vien';

        $refRole = $referrer['quyen'] ?? '';

        if ($regType === 'nhan_vien') {
            // Đầu khách -> Cần Đầu chủ (admin)
            if ($refRole !== 'admin') {
                $saveOldInput();
                $_SESSION['error'] = 'Đăng ký Đầu khách chỉ dùng được mã giới thiệu của Đầu chủ.';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
        } elseif ($regType === 'quan_ly') {
            // Đầu chủ -> Cần Cấp quản lý (super_admin)
            if ($refRole !== 'super_admin') {
                $saveOldInput();
                $_SESSION['error'] = 'Đăng ký Đầu chủ chỉ dùng được mã giới thiệu của Cấp quản lý.';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
        }

        // ===== HASH PASSWORD =====
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ===== HANDLE UPLOADED CCCD IMAGE =====
        if (!empty($_FILES['anh_cccd']) && $_FILES['anh_cccd']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['anh_cccd'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if ($file['size'] > 3 * 1024 * 1024) {
                $saveOldInput();
                $_SESSION['error'] = 'Kích thước ảnh không được vượt quá 3MB';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed, true)) {
                $saveOldInput();
                $_SESSION['error'] = 'Định dạng ảnh không hợp lệ (chỉ JPG/PNG/WEBP)';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            $uploadsDir = realpath(__DIR__ . '/../../public') . '/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadsDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $saveOldInput();
                $_SESSION['error'] = 'Không lưu được ảnh. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Save filename relative to public
            $data['anh_cccd'] = 'uploads/' . $filename;
        }

        // ===== VALIDATE 'loai_tai_khoan' =====
        $allowedRoles = ['nhan_vien', 'quan_ly', 'admin'];
        if (!in_array($data['loai_tai_khoan'], $allowedRoles, true)) {
            $data['loai_tai_khoan'] = 'nhan_vien';
        }

        // Map loai_tai_khoan to quyen
        if ($data['loai_tai_khoan'] === 'quan_ly') {
            $data['quyen'] = 'admin';
        } else {
            // 'nhan_vien' hoặc các trường hợp khác sẽ có quyền 'user'
            $data['quyen'] = 'user';
        }
        $data['trang_thai'] = 0;

        // ===== TỰ ĐỘNG TẠO MÃ NHÂN SỰ (DC.../DK...) =====
        $prefix = '';
        if ($data['loai_tai_khoan'] === 'quan_ly') { // Đầu chủ
            $prefix = 'DC';
        } elseif ($data['loai_tai_khoan'] === 'nhan_vien') { // Đầu khách
            $prefix = 'DK';
        }

        if ($prefix) {
            $db = \Database::connect();
            // Lấy mã nhân sự cuối cùng với prefix tương ứng
            $sql = "SELECT ma_nhan_su FROM users WHERE ma_nhan_su LIKE :prefix ORDER BY id DESC LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':prefix' => $prefix . '%']);
            $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextNum = 1;
            if ($lastUser && !empty($lastUser['ma_nhan_su'])) {
                // Lấy phần số sau prefix (DC hoặc DK)
                $numPart = substr($lastUser['ma_nhan_su'], 2);
                if (is_numeric($numPart)) {
                    $nextNum = (int)$numPart + 1;
                }
            }
            // Tạo mã mới: ví dụ DC01, DK10
            $data['ma_nhan_su'] = $prefix . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
        } else {
            // Fallback nếu loai_tai_khoan không hợp lệ, hoặc không cần mã
            $data['ma_nhan_su'] = null;
        }

        // ===== MAP 'vi_tri' TO INTEGER =====
        // Ánh xạ giá trị chuỗi từ form sang số nguyên để lưu vào DB
        // 0: Kho nhà đất, 1: Kho nhà cho thuê, 2: Cả hai
        $vi_tri_string = $data['vi_tri'];
        switch ($vi_tri_string) {
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
                $data['vi_tri'] = null; // Giá trị mặc định nếu không khớp
        }

        $result = User::createWithRole($data);

        if (!$result) {
            $saveOldInput();
            $_SESSION['error'] = 'Đăng ký thất bại, vui lòng thử lại';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }

        unset($_SESSION['old']); // Xóa dữ liệu cũ nếu thành công
        $_SESSION['success'] = 'Đăng ký thành công vui lòng liên hệ admin 123456789 để được hỗ trợ';
        // Set a short-lived cookie so client-side can clear localStorage after successful registration
        setcookie('bh_clear_form', '1', time() + 60, '/');
        header('Location: ' . BASE_URL . '/login');
    }
    // Form quên mật khẩu
    public function forgotPassword()
    {
        $this->view('auth/forgot');
    }
    // app/Controllers/AuthController.php

    // ... (Giữ nguyên các đoạn require và use PHPMailer ở trên đầu file) ...

    public function handleForgotPassword()
    {
        // 1. Chỉ nhận method POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        // 2. Kiểm tra email có tồn tại không
        require_once __DIR__ . '/../Models/User.php';
        $user = \User::findByEmail($email);

        if (!$user) {
            $_SESSION['error'] = 'Email này chưa được đăng ký trong hệ thống!';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        // 3. Tạo mật khẩu mới (8 chữ số ngẫu nhiên)
        $newPasswordRaw = (string) mt_rand(10000000, 99999999); // VD: 12345678

        // 4. Mã hóa mật khẩu để lưu vào DB
        $hashedPassword = password_hash($newPasswordRaw, PASSWORD_BCRYPT);

        // 5. Cập nhật vào Database ngay lập tức
        $updated = \User::updatePassword($email, $hashedPassword);

        if (!$updated) {
            $_SESSION['error'] = 'Lỗi hệ thống: Không thể cập nhật mật khẩu. Vui lòng thử lại.';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        // 6. Gửi Email chứa mật khẩu GỐC (8 số) cho user
        $subject = '[Big Housing Land] Cấp lại mật khẩu mới';
        $body = "
            <h3>Xin chào " . htmlspecialchars($user['ho_ten']) . ",</h3>
            <p>Yêu cầu cấp lại mật khẩu của bạn đã được xử lý.</p>
            <p>Mật khẩu mới của bạn là: <b style='font-size: 20px; color: red;'>$newPasswordRaw</b></p>
            <p>Vui lòng đăng nhập và đổi lại mật khẩu ngay để bảo mật tài khoản.</p>
            <hr>
            <a href='" . BASE_URL . "/login'>Bấm vào đây để đăng nhập</a>
        ";

        $sent = $this->sendMailSMTP($email, $user['ho_ten'], $subject, $body);

        if ($sent) {
            $_SESSION['success'] = 'Thành công! Mật khẩu mới (8 số) đã được gửi vào Email của bạn.';
            header('Location: ' . BASE_URL . '/login'); // Chuyển hướng về trang đăng nhập luôn
        } else {
            $_SESSION['error'] = 'Lỗi gửi mail. Vui lòng kiểm tra lại cấu hình SMTP.';
            header('Location: ' . BASE_URL . '/forgot-password');
        }
    }
    // 2. Xử lý gửi mail
    public function sendResetLink()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        // --- A. Kiểm tra email có tồn tại trong DB không ---
        require_once __DIR__ . '/../Models/User.php';
        $user = \User::findByEmail($email);

        if (!$user) {
            $_SESSION['error'] = 'Email này chưa được đăng ký trong hệ thống!';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }

        // --- B. Tạo Token và Lưu vào DB ---
        // Token là chuỗi ngẫu nhiên để xác thực
        $token = bin2hex(random_bytes(32));

        // Gọi hàm createResetToken đã có trong User Model của bạn
        // Lưu ý: Nên xóa token cũ của email này trước (User::deleteToken) để tránh rác DB (tuỳ chọn)
        \User::createResetToken($email, $token);

        // --- C. Gửi Email qua Gmail SMTP ---
        $resetLink = BASE_URL . "/reset-password?token=" . $token; // Link dẫn đến trang đặt lại pass

        $subject = '[Big Housing Land] Yêu cầu đặt lại mật khẩu';
        $body = "
            <h3>Xin chào " . htmlspecialchars($user['ho_ten']) . ",</h3>
            <p>Bạn (hoặc ai đó) vừa yêu cầu lấy lại mật khẩu cho tài khoản: <b>$email</b></p>
            <p>Vui lòng nhấn vào nút dưới đây để đổi mật khẩu mới:</p>
            <p>
                <a href='$resetLink' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;font-weight:bold;'>ĐẶT LẠI MẬT KHẨU</a>
            </p>
            <p>Hoặc copy link này vào trình duyệt: <br> $resetLink</p>
            <p><i>Link này chỉ có hiệu lực trong thời gian ngắn. Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</i></p>
        ";

        $sent = $this->sendMailSMTP($email, $user['ho_ten'], $subject, $body);

        if ($sent) {
            $_SESSION['success'] = 'Đã gửi link khôi phục! Vui lòng kiểm tra Email (cả hộp thư Rác/Spam).';
            header('Location: ' . BASE_URL . '/forgot-password');
        } else {
            $_SESSION['error'] = 'Lỗi gửi mail: Không thể kết nối đến máy chủ mail.';
            header('Location: ' . BASE_URL . '/forgot-password');
        }
    }

    // --- Hàm gửi mail dùng PHPMailer ---
    private function sendMailSMTP($toEmail, $toName, $subject, $body)
    {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình Server Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ducok0122@gmail.com'; // <--- ĐIỀN GMAIL CỦA BẠN (người gửi)
            $mail->Password   = 'jnzd xqeg dwmd qnqa';       // <--- ĐIỀN APP PASSWORD (16 ký tự)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Người nhận & Người gửi
            $mail->setFrom('ducok0122@gmail.com', 'Big Housing Land Admin');
            $mail->addAddress($toEmail, $toName);

            // Nội dung
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body); // Nội dung rút gọn cho trình duyệt không hỗ trợ HTML

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Ghi log lỗi để debug nếu cần: error_log($mail->ErrorInfo);
            return false;
        }
    }

    // Form nhập mật khẩu mới
    public function reset()
    {
        if (empty($_GET['token'])) {
            die('Token không hợp lệ');
        }

        $this->view('auth/reset');
    }

    // Xử lý reset
    public function handleReset()
    {
        $token    = $_POST['token'];
        $password = $_POST['password'];
        $confirm  = $_POST['confirm'];

        if ($password !== $confirm) {
            die('Mật khẩu không khớp');
        }

        $email = User::getEmailByToken($token);
        if (!$email) {
            die('Token không hợp lệ hoặc đã hết hạn');
        }

        User::updatePassword(
            $email,
            password_hash($password, PASSWORD_BCRYPT)
        );

        User::deleteToken($token);

        $_SESSION['success'] = 'Đổi mật khẩu thành công';
        header('Location: ' . BASE_URL . '/login');
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
    }
}
