<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chính sách Cookie</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>

</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/profile" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chính sách Cookie</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="policy-container">

            <p class="policy-text">
                Chính sách này giải thích cách chúng tôi sử dụng Cookie và các công nghệ tương tự để thu thập và lưu trữ thông tin khi người dùng truy cập ứng dụng/website.
            </p>

            <div class="policy-section-title">1. Cookie là gì?</div>
            <p class="policy-text">
                Cookie là tệp dữ liệu nhỏ được lưu trữ trên thiết bị của người dùng nhằm nhận diện trình duyệt, ghi nhớ thông tin truy cập và cải thiện trải nghiệm sử dụng.
            </p>

            <div class="policy-section-title">2. Chúng tôi sử dụng Cookie để làm gì</div>
            <div class="policy-text">Cookie được sử dụng cho các mục đích:</div>
            <ul class="policy-list">
                <li>Ghi nhớ lựa chọn và cài đặt của người dùng.</li>
                <li>Phân tích hành vi truy cập (thời gian, tần suất, thiết bị truy cập).</li>
                <li>Cá nhân hóa nội dung và đề xuất phù hợp.</li>
                <li>Tối ưu hiệu suất, bảo mật và ngăn chặn hành vi gian lận.</li>
            </ul>
            <p class="policy-text">
                Nếu tích hợp bên thứ ba, họ có thể dùng cookie theo chính sách của riêng họ (ví dụ: Google Analytics, Facebook Pixel).
            </p>

            <div class="policy-section-title">3. Các loại Cookie chúng tôi sử dụng</div>
            <ul class="policy-list">
                <li><strong>Cookie kỹ thuật:</strong> cần thiết để ứng dụng/website hoạt động.</li>
                <li><strong>Cookie phân tích:</strong> theo dõi lưu lượng truy cập, đo hiệu quả tính năng.</li>
                <li><strong>Cookie chức năng:</strong> ghi nhớ thông tin đăng nhập, ngôn ngữ, tuỳ chọn hiển thị.</li>
                <li><strong>Cookie quảng cáo:</strong> phục vụ cá nhân hóa quảng cáo (chỉ nếu có triển khai).</li>
            </ul>

            <div class="policy-section-title">4. Quản lý hoặc tắt Cookie</div>
            <div class="policy-text">Người dùng có quyền:</div>
            <ul class="policy-list">
                <li>Chấp nhận hoặc từ chối cookie không thiết yếu.</li>
                <li>Xóa cookie khỏi trình duyệt bất kỳ lúc nào.</li>
                <li>Thiết lập khóa cookie trong phần cài đặt trình duyệt.</li>
            </ul>
            <p class="policy-text" style="font-style: italic;">
                Lưu ý: Một số tính năng có thể hoạt động không đầy đủ khi cookie bị tắt.
            </p>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
</body>

</html>