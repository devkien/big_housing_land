<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết thông tin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: #f2f4f8;">
        <header class="header-blue">
            <a href="<?= BASE_URL ?>/superadmin/info"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="title-center">Chi tiết thông tin</div>
        </header>

        <div style="padding-bottom: 80px;">

            <div class="article-container">
                <?php
                $post = $post ?? null;
                if ($post):
                    $title = $post['tieu_de'] ?? 'No title';
                    $content = $post['noi_dung'] ?? '';
                    $created = !empty($post['created_at']) ? date('d/m/Y', strtotime($post['created_at'])) : '';
                    $images = $post['images'] ?? [];
                ?>
                    <div class="article-title"><?= htmlspecialchars($title) ?></div>

                    <div class="article-meta" style="color:#666; font-size:13px; margin-bottom:8px;"><?= htmlspecialchars($created) ?></div>

                    <div class="article-content">
                        <?= $content ?>
                    </div>

                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $img):
                            $path = $img['image_path'] ?? $img['media_path'] ?? '';
                            if (preg_match('/^https?:\/\//i', $path) || strpos($path, '/') === 0) {
                                $imgUrl = $path;
                            } else {
                                $imgUrl = rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
                            }
                        ?>
                            <img src="<?= htmlspecialchars($imgUrl) ?>" class="article-image" alt="">
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="padding:30px; text-align:center; color:#666;">Bài viết không tồn tại.</div>
                <?php endif; ?>
            </div>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>