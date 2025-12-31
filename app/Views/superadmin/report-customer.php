<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết báo cáo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/report-list" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chi tiết - Báo cáo dẫn khách</div>
            <div style="width: 20px;"></div>
        </header>

        <div style="padding: 0 15px 80px 15px;">
            <?php $r = isset($report) ? $report : null; ?>
            <div class="view-section-title">Thông tin người dẫn khách:</div>

            <div class="view-info-row">
                <span class="view-info-label">Họ tên:</span> <?= htmlspecialchars($r['sender_name'] ?? ($r['ho_ten'] ?? '')) ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">SĐT:</span> <?= htmlspecialchars($r['so_dien_thoai'] ?? '') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Thời gian gửi tin:</span> <?= isset($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '' ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Ghi chú:</span>
                <div><?= nl2br(htmlspecialchars($r['note'] ?? '')) ?></div>
            </div>

            <div class="view-section-title" style="margin-top: 30px;">Thông tin khách:</div>

            <div class="view-info-row">
                <span class="view-info-label">Họ tên:</span> <?= htmlspecialchars($r['ho_ten'] ?? '') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">SĐT:</span> <?= htmlspecialchars($r['so_dien_thoai'] ?? '') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">CCCD:</span> <?= htmlspecialchars($r['cccd'] ?? $r['cmnd'] ?? '') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Ghi chú:</span>
                <div><?= nl2br(htmlspecialchars($r['customer_note'] ?? $r['ghi_chu'] ?? '')) ?></div>
            </div>

            <div class="view-info-row" style="margin-top: 10px;">
                <span class="view-info-label">Ảnh:</span>
            </div>

            <div class="view-image-grid">
                <?php
                $images = [];
                if (!empty($r['customer_images']) && is_array($r['customer_images'])) {
                    $images = $r['customer_images'];
                } else {
                    // fallback: try common single-image fields
                    $possible = ['anh_dai_dien', 'avatar', 'image', 'anh'];
                    foreach ($possible as $k) {
                        if (!empty($r[$k])) {
                            $images[] = $r[$k];
                        }
                    }
                }

                if (!empty($images)) {
                    foreach ($images as $img) {
                        $img = trim($img);
                        if ($img === '') continue;
                        $src = (stripos($img, 'http') === 0) ? $img : (BASE_URL . '/' . ltrim($img, '/'));
                        echo '<div class="view-image-item"><img src="' . htmlspecialchars($src) . '" style="width:100%;height:100%;object-fit:cover;"/></div>';
                    }
                } else {
                    echo '<div class="no-images">Không có ảnh</div>';
                }
                ?>
            </div>
        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>