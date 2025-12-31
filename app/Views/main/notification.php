<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thông báo vụ chốt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <style>
        /* Carousel styles (same as admin/superadmin) */
        .post-carousel {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
            margin-top: 10px;
            height: 220px;
        }

        .post-carousel .carousel-track {
            display: flex;
            transition: transform 0.45s ease;
            will-change: transform;
            height: 100%;
        }

        .post-carousel .carousel-slide {
            min-width: 100%;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .post-carousel .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .post-carousel .carousel-dots {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 8px;
            display: flex;
            gap: 6px;
        }

        .post-carousel .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .post-carousel .carousel-dot.active {
            background: #0044cc;
        }

        @media (min-width: 768px) {
            .post-carousel {
                height: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Thông báo vụ chốt</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="feed-list" style="padding-bottom: 80px; padding-top: 15px;">
            <?php if (!empty($posts) && is_array($posts)): ?>
                <?php foreach ($posts as $p): ?>
                    <article class="post-card" style="cursor:pointer;">
                        <div class="user-row">
                            <div class="user-left">
                                <?php $profileLink = BASE_URL . '/detailprofile' . (!empty($p['user_id']) ? ('?user_id=' . (int)$p['user_id']) : ''); ?>
                                <a href="<?= htmlspecialchars($profileLink) ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                                    <?php
                                    // determine author avatar (support multiple possible keys)
                                    $authorAvatar = $p['avatar'] ?? $p['author_avatar'] ?? $p['user_avatar'] ?? null;
                                    // if post doesn't include avatar, try to load from users table
                                    if (empty($authorAvatar) && !empty($p['user_id'])) {
                                        if (!class_exists('User')) {
                                            require_once __DIR__ . '/../../Models/User.php';
                                        }
                                        $u = User::findById((int)$p['user_id']);
                                        if (!empty($u['avatar'])) $authorAvatar = $u['avatar'];
                                    }

                                    $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
                                    if (!empty($authorAvatar)) {
                                        $t = ltrim($authorAvatar);
                                        if (stripos($t, 'http') === 0) {
                                            $avatarSrc = $t;
                                        } elseif (strpos($t, '/') === 0) {
                                            $avatarSrc = rtrim(BASE_URL, '/') . $t;
                                        } else {
                                            $avatarSrc = rtrim(BASE_URL, '/') . '/' . ltrim($t, '/');
                                        }
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($avatarSrc) ?>" class="user-avatar" alt="avatar" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
                                    <div class="notify-user-info">
                                        <div class="user-name"><?= htmlspecialchars($p['author_name'] ?? 'Người dùng') ?></div>
                                        <div class="notify-time"><?= isset($p['created_at']) ? date('d/m/Y H:i', strtotime($p['created_at'])) : '' ?></div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="notify-content">
                            <?php
                            $raw = $p['noi_dung'] ?? '';
                            $plain = trim(strip_tags($raw));
                            $title = !empty($p['tieu_de']) ? mb_substr(trim($p['tieu_de']), 0, 120) : null;
                            $short = mb_substr($plain, 0, 220);
                            $tags = [];
                            if (preg_match_all('/#([\p{L}\p{N}_\-]+)/u', $raw, $mt)) {
                                $tags = array_unique($mt[0]);
                            }
                            ?>

                            <?php if (!empty($title)): ?>
                                <div class="notify-title" style="font-weight:600; margin-bottom:6px;"><?= htmlspecialchars($title) ?></div>
                            <?php endif; ?>
                            <div class="auto-truncate-text" data-limit="150">
                                <?= $raw ?>
                            </div>

                            <?php if (!empty($tags)): ?>
                                <div style="margin-top:8px;">
                                    <?php foreach ($tags as $t): ?>
                                        <span class="hashtag"><?= htmlspecialchars($t) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($p['images']) && is_array($p['images'])): ?>
                            <div class="post-carousel" data-post-id="<?= htmlspecialchars($p['id'] ?? '') ?>">
                                <div class="carousel-track">
                                    <?php foreach ($p['images'] as $imgPath):
                                        $src = (stripos($imgPath, 'http') === 0) ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'));
                                    ?>
                                        <div class="carousel-slide"><img src="<?= htmlspecialchars($src) ?>" class="post-image-large"></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="carousel-dots"></div>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">Chưa có thông báo nào.</div>
            <?php endif; ?>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        // Highlight icon thông báo ở bottom nav
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('nav-notify');
            if (el) {
                el.classList.add('active');
                // Nếu muốn đổi icon khi active (ví dụ icon màu xanh)
                // var img = el.querySelector('img');
                // if(img) img.src = '<?= BASE_URL ?>/icon/menuthongbao_active.png'; 
            }
        });
    </script>
    <script>
        (function initPostCarousels() {
            const carousels = document.querySelectorAll('.post-carousel');
            carousels.forEach(carousel => {
                const track = carousel.querySelector('.carousel-track');
                const slides = Array.from(carousel.querySelectorAll('.carousel-slide'));
                if (!track || slides.length === 0) return;

                let index = 0;
                const dotsContainer = carousel.querySelector('.carousel-dots');

                // create dots
                slides.forEach((s, i) => {
                    const d = document.createElement('div');
                    d.className = 'carousel-dot' + (i === 0 ? ' active' : '');
                    d.addEventListener('click', function() {
                        goTo(i);
                        resetTimer();
                    });
                    dotsContainer.appendChild(d);
                });

                function goTo(i) {
                    index = ((i % slides.length) + slides.length) % slides.length;
                    track.style.transform = 'translateX(-' + (index * 100) + '%)';
                    Array.from(dotsContainer.children).forEach((dot, j) => dot.classList.toggle('active', j === index));
                }

                // touch swipe
                let startX = 0,
                    deltaX = 0;
                track.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                });
                track.addEventListener('touchmove', function(e) {
                    deltaX = e.touches[0].clientX - startX;
                });
                track.addEventListener('touchend', function() {
                    if (Math.abs(deltaX) > 40) {
                        if (deltaX < 0) goTo(index + 1);
                        else goTo(index - 1);
                    }
                    deltaX = 0;
                    resetTimer();
                });

                // auto rotate
                let timer = setInterval(function() {
                    goTo(index + 1);
                }, 3000);

                function resetTimer() {
                    clearInterval(timer);
                    timer = setInterval(function() {
                        goTo(index + 1);
                    }, 3000);
                }
            });
        })();
    </script>
</body>

</html>