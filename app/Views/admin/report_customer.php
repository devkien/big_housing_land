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
            <a href="<?= BASE_URL ?>/admin/report_list" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chi tiết - Báo cáo dẫn khách</div>
            <div style="width: 20px;"></div>
        </header>

        <div style="padding: 0 15px 80px 15px;">
            <div class="view-section-title">Thông tin người dẫn khách:</div>

            <div class="view-info-row">
                <span class="view-info-label">Họ tên:</span> <?= htmlspecialchars($report['sender_name'] ?? '---') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">SĐT:</span> <?= htmlspecialchars($report['sender_phone'] ?? '---') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Thời gian gửi tin:</span> <?= isset($report['created_at']) ? date('d/m/Y H:i', strtotime($report['created_at'])) : '---' ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Ghi chú:</span> <?= htmlspecialchars($report['note'] ?? 'Không có') ?>
            </div>

            <div class="view-section-title" style="margin-top: 30px;">Thông tin khách:</div>

            <div class="view-info-row">
                <span class="view-info-label">Họ tên:</span> <?= htmlspecialchars($report['ho_ten'] ?? '---') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">SĐT:</span>
                <?php
                $sdt = $report['so_dien_thoai'] ?? '';
                echo htmlspecialchars(strlen($sdt) > 4 ? str_repeat('*', strlen($sdt) - 4) . substr($sdt, -4) : ($sdt ?: '---'));
                ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">Năm sinh:</span> <?= htmlspecialchars($report['nam_sinh'] ?? '---') ?>
            </div>
            <div class="view-info-row">
                <span class="view-info-label">CCCD:</span>
                <?php
                $cccd = $report['cccd'] ?? '';
                echo htmlspecialchars(strlen($cccd) > 4 ? str_repeat('*', strlen($cccd) - 4) . substr($cccd, -4) : ($cccd ?: '---'));
                ?>
            </div>

            <div class="view-info-row" style="margin-top: 10px;">
                <span class="view-info-label">Ảnh:</span>
            </div>

            <div class="view-image-grid">
                <?php if (!empty($report['customer_images'])): ?>
                    <?php foreach ($report['customer_images'] as $imagePath): ?>
                        <div class="view-image-item" style="background-image: url('<?= BASE_URL ?>/<?= htmlspecialchars($imagePath) ?>');"></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888;">Không có ảnh.</p>
                <?php endif; ?>
            </div>
        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
</body>

</html>