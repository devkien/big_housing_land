<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chính sách thanh toán</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/profile" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chính sách thanh toán</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="policy-container">

            <p class="policy-text">
                Chính sách này áp dụng cho các giao dịch thanh toán khi người dùng sử dụng sản phẩm hoặc dịch vụ thông qua ứng dụng/website của chúng tôi.
            </p>

            <div class="policy-section-title">1. Hình thức thanh toán</div>
            <div class="policy-text" style="font-weight: 600;">Chúng tôi hỗ trợ một hoặc nhiều phương thức sau (tùy theo triển khai):</div>
            <ul class="policy-list">
                <li>Thanh toán online qua ví điện tử (Momo, ZaloPay, VNPay,...).</li>
                <li>Thanh toán qua thẻ ATM nội địa hoặc thẻ quốc tế (Visa/MasterCard).</li>
                <li>Chuyển khoản ngân hàng.</li>
                <li>Thanh toán tiền mặt (COD) khi nhận hàng hoặc sử dụng dịch vụ (nếu áp dụng).</li>
                <li>Thanh toán định kỳ với gói thuê bao (subscription) qua nền tảng Google Play / App Store.</li>
            </ul>

            <div class="policy-section-title">2. Xác nhận giao dịch</div>
            <ul class="policy-list">
                <li>Sau khi thanh toán thành công, người dùng sẽ nhận được thông báo hoặc email xác nhận.</li>
                <li>Đối với thanh toán thất bại, hệ thống sẽ không trừ tiền hoặc tự động hoàn trả theo quy định của ngân hàng/ví điện tử.</li>
            </ul>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>