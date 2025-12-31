<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Trang chủ - Big Housing Land</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <style>
        /* Make news cards horizontally scrollable on tablet (iPad) and up.
           Keep vertical stacking on small phones. */
        .news-scroll {
            display: flex;
            gap: 12px;
            padding: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        }

        .news-card {
            flex: 0 0 320px;
            min-width: 260px;
            max-width: 380px;
            scroll-snap-align: start;
        }

        .news-scroll::-webkit-scrollbar {
            height: 8px
        }

        .news-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 6px
        }

        /* Keep horizontal scrolling on all screen sizes (including phones) */
    </style>
</head>

<body>
    <div class="app-container" style="background-color: #E8F4FF;">

        <header class="home-header">
            <form class="search-bar" action="<?= BASE_URL ?>/management-resource" method="GET">
                <input type="text" name="q" placeholder="Tìm kiếm mã tài nguyên...">
                <button type="submit" style="border: none; background: transparent; padding: 0; cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </header>

        <section class="menu-section" id="menu-container">
            <?php
            $__menu_path = __DIR__ . '/layouts/menu.php';
            if (!file_exists($__menu_path)) {
                $__alt = __DIR__ . '/../layouts/menu.php';
                if (file_exists($__alt)) {
                    $__menu_path = $__alt;
                } else {
                    error_log("Menu include not found: {$__menu_path}");
                }
            }
            if (file_exists($__menu_path)) require_once $__menu_path;
            ?>
        </section>

        <section class="banner-section">
            <img src="<?= BASE_URL ?>/images/home.png" alt="Banner Thanh Tri" class="banner-images">
        </section>

        <section class="news-section">
            <div class="btn-news-header">BẢNG TIN</div>

            <div class="news-scroll">
                <?php
                $posts = $pinnedPosts ?? [];
                if (!empty($posts) && is_array($posts)):
                    foreach ($posts as $post):
                        $title = htmlspecialchars($post['tieu_de'] ?? '');
                        $created = !empty($post['created_at']) ? date('m/d/Y', strtotime($post['created_at'])) : '';
                        $snippet = mb_strimwidth(strip_tags($post['noi_dung'] ?? ''), 0, 120, '...');
                        $firstImg = '';
                        if (!empty($post['images']) && is_array($post['images']) && !empty($post['images'][0]['image_path'])) {
                            $firstImg = htmlspecialchars($post['images'][0]['image_path']);
                        }
                ?>
                        <article class="news-card" onclick="window.location.href='<?= BASE_URL ?>/internal-info-detail?id=<?= (int)$post['id'] ?>'" style="cursor:pointer;">
                            <div class="news-header">
                                <img src="<?= htmlspecialchars($post['author_avatar_src'] ?? (rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png'), ENT_QUOTES, 'UTF-8') ?>" class="avatar" />
                                <div class="news-info">
                                    <h4><?= htmlspecialchars($post['author_name'] ?? 'Big Housing Land') ?></h4>
                                    <span><?= $created ?></span>
                                </div>
                            </div>
                            <div class="news-content">
                                <p>
                                    <strong><?= $title ?></strong> <?= $snippet ?><span class="dots">...</span><span class="more-text" style="display: none;"> <?= strip_tags($post['noi_dung'] ?? '') ?></span>
                                    <a href="javascript:void(0)" class="see-more" onclick="event.stopPropagation(); toggleNews(this)">Xem thêm</a>
                                </p>
                                <?php if ($firstImg !== ''): ?>
                                    <img src="<?= BASE_URL ?>/<?= $firstImg ?>" alt="" class="doc-preview-images">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>/images/vanban.png" alt="Văn bản thông báo" class="doc-preview-images">
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php
                    endforeach;
                else:
                    ?>
                    <div style="text-align: center; padding: 20px; color: #666;">Chưa có tin nào được ghim.</div>
                <?php endif; ?>
            </div>

        </section>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

    <script>
        // Highlight trang chủ trong bottom nav (nếu cần)
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('nav-home');
            if (el) el.classList.add('active');
        });

        function toggleNews(btn) {
            var p = btn.parentElement;
            var dots = p.querySelector(".dots");
            var moreText = p.querySelector(".more-text");

            if (dots.style.display === "none") {
                dots.style.display = "inline";
                btn.innerHTML = "Xem thêm";
                moreText.style.display = "none";
            } else {
                dots.style.display = "none";
                btn.innerHTML = "Thu gọn";
                moreText.style.display = "inline";
            }
        }
    </script>
</body>

</html>