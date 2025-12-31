<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chính sách bảo mật</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/profile" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chính sách bảo mật</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="policy-container">

            <p class="policy-text">
                Chúng tôi cam kết bảo vệ dữ liệu cá nhân và quyền riêng tư của người dùng trong quá trình sử dụng ứng dụng. Chính sách này giải thích cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin của người dùng.
            </p>

            <div class="policy-section-title">1. Thu thập thông tin</div>
            <div class="policy-text" style="font-weight: 600;">Ứng dụng có thể thu thập các loại thông tin sau:</div>
            <ul class="policy-list">
                <li>Thông tin cá nhân cung cấp trực tiếp (ví dụ: họ tên, email, số điện thoại).</li>
                <li>Dữ liệu kỹ thuật (địa chỉ IP, loại thiết bị, hệ điều hành, thời gian truy cập).</li>
                <li>Dữ liệu phát sinh từ quá trình sử dụng tính năng của ứng dụng.</li>
            </ul>

            <div class="policy-section-title">2. Mục đích sử dụng thông tin</div>
            <div class="policy-text" style="font-weight: 600;">Chúng tôi sử dụng dữ liệu để:</div>
            <ul class="policy-list">
                <li>Cung cấp, duy trì, tối ưu hiệu suất sản phẩm và trải nghiệm.</li>
                <li>Gửi thông báo dịch vụ, hỗ trợ khách hàng.</li>
                <li>Phân tích hành vi để cải thiện chức năng & bảo mật.</li>
                <li>Tuân thủ yêu cầu pháp lý khi cần thiết.</li>
            </ul>

            <div class="policy-section-title">3. Không chia sẻ trái phép</div>
            <p class="policy-text">
                Chúng tôi không bán, cho thuê hay chia sẻ dữ liệu cá nhân cho bên thứ ba trừ khi:
            </p>
            <ul class="policy-list">
                <li>Có sự đồng ý của người dùng.</li>
                <li>Để thực hiện dịch vụ qua bên thứ ba (ví dụ: thanh toán, phân tích), và các bên này cam kết bảo mật.</li>
                <li>Theo quy định pháp luật.</li>
            </ul>

            <div class="policy-section-title">4. Lưu trữ & bảo mật</div>
            <ul class="policy-list">
                <li>Dữ liệu được lưu trữ an toàn bằng biện pháp mã hóa và kiểm soát truy cập.</li>
                <li>Người dùng có quyền yêu cầu truy cập, chỉnh sửa hoặc xóa dữ liệu cá nhân.</li>
            </ul>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>