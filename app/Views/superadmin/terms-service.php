<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Điều khoản dịch vụ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <script src="<?= BASE_URL ?>/public/js/script.js"></script>
            <div class="detail-title">Điều khoản dịch vụ</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="terms-container">

            <div class="terms-main-title">ĐIỀU KHOẢN DỊCH VỤ</div>

            <p>
                <span class="text-underline-blue">Xin vui lòng đọc kỹ Điều khoản Dịch vụ này trước khi sử dụng ứng dụng của chúng tôi. Khi truy cập hoặc sử dụng ứng dụng, người dùng được xem là đã đọc, hiểu và đồng ý với các điều khoản dưới đây.</span>
            </p>

            <div class="terms-section-title">1. Chấp nhận điều khoản</div>
            <p>
                <span class="text-underline-blue">Bằng việc sử dụng ứng dụng, người dùng đồng ý tuân thủ tất cả điều khoản và quy định được đề ra. Nếu không đồng ý, vui lòng ngừng sử dụng dịch vụ.</span>
            </p>

            <div class="terms-section-title">2. Tài khoản người dùng</div>
            <ul class="terms-list">
                <li>
                    <span class="text-underline-blue">Người dùng chịu trách nhiệm về thông tin đăng ký cung cấp phải chính xác và cập nhật.</span>
                </li>
                <li>
                    <span class="text-underline-blue">Người dùng chịu trách nhiệm bảo mật tài khoản và mật khẩu.</span>
                </li>
                <li>
                    <span class="text-underline-blue">Chúng tôi không chịu trách nhiệm về tổn thất phát sinh do việc chia sẻ tài khoản với bên thứ ba.</span>
                </li>
            </ul>

            <div class="terms-section-title">3. Quyền và trách nhiệm của người dùng</div>
            <div style="font-weight: 700; margin-bottom: 5px; text-decoration: underline;">Người dùng không được:</div>
            <ul class="terms-list">
                <li>
                    <span class="text-underline-blue">Sử dụng dịch vụ vào mục đích vi phạm pháp luật.</span>
                </li>
                <li>
                    <span class="text-underline-blue">Can thiệp, phá hoại chức năng hệ thống, bảo mật.</span>
                </li>
                <li>
                    <span class="text-underline-blue">Sao chép, bán, phân phối, hoặc khai thác thương mại nội dung ứng dụng nếu chưa được phép bằng văn bản.</span>
                </li>
            </ul>

            <div class="terms-section-title">4. Quyền và trách nhiệm của nhà cung cấp dịch vụ</div>
            <div style="font-weight: 700; margin-bottom: 5px; text-decoration: underline;">Chúng tôi có quyền:</div>
            <ul class="terms-list">
                <li>
                    <span class="text-underline-blue">Thay đổi, cập nhật, tạm ngưng hoặc ngừng cung cấp dịch vụ mà không cần thông báo trước.</span>
                </li>
                <li>
                    <span class="text-underline-blue">Khoá hoặc xóa tài khoản có hành vi vi phạm điều khoản.</span>
                </li>
            </ul>
            <p style="margin-top: 5px;">
                <span class="text-underline-blue">Chúng tôi nỗ lực đảm bảo tính ổn định, nhưng không bảo đảm rằng dịch vụ luôn không gián đoạn hoặc không xảy ra lỗi.</span>
            </p>

        </div>

        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</body>

</html>