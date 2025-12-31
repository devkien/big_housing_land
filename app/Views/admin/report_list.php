<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Báo cáo dẫn khách</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <script>
        // Mock CKEditor để tránh lỗi trong script.js vì trang này không cần bộ soạn thảo
        window.ClassicEditor = {
            create: function() {
                // Trả về Promise không bao giờ resolve để script.js không làm gì tiếp theo
                return new Promise(() => {});
            }
        };
    </script>
    <script src="<?= BASE_URL ?>/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header" style="justify-content: flex-start;">
            <a href="<?= BASE_URL ?>/admin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title" style="margin-left: 15px;">Báo cáo dẫn khách</div>
        </header>

        <form class="search-box-blue-border" style="width: 65%;" method="GET" action="<?= BASE_URL ?>/admin/report_list">
            <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tên đầu chủ, khách, SĐT...">
            <button type="submit" style="border:none; background:transparent;"><i class="fa-solid fa-magnifying-glass" style="cursor: pointer;"></i></button>
        </form>

        <div class="table-wrapper" style="padding-bottom: 80px;">
            <table class="report-list-table">
                <thead>
                    <tr>
                        <th style="padding-left: 10px; padding-right: 5px; text-align: left;">Thời gian gửi</th>
                        <th style="text-align: left; padding-left: 5px; padding-right: 5px;">Người gửi</th>
                        <th class="text-center" style="padding-left: 5px; padding-right: 5px;">SĐT</th>
                        <th class="text-right" style="padding-right: 10px; padding-left: 5px;">Người xem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">Không có báo cáo nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                            <tr onclick="window.location.href='<?= BASE_URL ?>/admin/report_customer?id=<?= $r['id'] ?>'" style="cursor: pointer;">
                                <td style="padding-left: 10px; padding-right: 5px;">
                                    <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                                </td>
                                <td style="padding-left: 5px; padding-right: 5px;">
                                    <?= htmlspecialchars($r['sender_name'] ?? '---') ?>
                                </td>
                                <td class="text-center" style="padding-left: 5px; padding-right: 5px;">
                                    <?php
                                    $sdt = $r['customer_phone'] ?? '';
                                    echo htmlspecialchars(strlen($sdt) > 4 ? str_repeat('*', strlen($sdt) - 4) . substr($sdt, -4) : ($sdt ?: ''));
                                    ?>
                                </td>
                                <td class="text-right" style="padding-right: 10px; padding-left: 5px;">
                                    <?= htmlspecialchars($r['customer_name'] ?? '---') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <div class="pagination-container">
                                <?php if ($page > 1): ?>
                                    <a href="<?= BASE_URL ?>/admin/report_list?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
                                <?php endif; ?>

                                <a href="#" class="page-link active"><?= $page ?> / <?= $pages > 0 ? $pages : 1 ?></a>

                                <?php if ($page < $pages): ?>
                                    <a href="<?= BASE_URL ?>/admin/report_list?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>