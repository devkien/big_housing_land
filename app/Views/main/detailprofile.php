<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thông tin chi tiết</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <?php
        $displayName = $user['ho_ten'] ?? '---';
        ?>
        <header class="profile-detail-header">
            <a href="<?= BASE_URL ?>/profile" style="color: black; font-size: 18px;"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="header-title"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></div>
            <div style="font-size: 20px;"></div>
        </header>
        <div class="cover-wrapper">
            <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=800&auto=format&fit=crop" class="cover-image">
            <?php
            // Normalize avatar path: support full URL, root-relative path, or stored relative path
            $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
            $avatar = $user['avatar'] ?? null;
            if (!empty($avatar)) {
                $trim = ltrim($avatar);
                if (stripos($trim, 'http') === 0) {
                    $avatarSrc = $trim;
                } elseif (strpos($trim, '/') === 0) {
                    $avatarSrc = rtrim(BASE_URL, '/') . $trim;
                } else {
                    $avatarSrc = rtrim(BASE_URL, '/') . '/' . ltrim($trim, '/');
                }
            }
            ?>

            <img src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar-circle" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">

            <?php
            // Hiện nút chỉnh sửa chỉ khi đang xem chính tài khoản của mình
            $currentId = $_SESSION['user']['id'] ?? null;
            $viewedId = $user['id'] ?? null;
            if (!empty($currentId) && !empty($viewedId) && (int)$currentId === (int)$viewedId):
            ?>
                <a href="<?= BASE_URL ?>/editprofile" class="edit-profile-btn">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa hồ sơ
                </a>
            <?php endif; ?>
            <?php
            // Determine CCCD visibility: only super_admin or the owner can see full CCCD
            $viewer = $_SESSION['user'] ?? null;
            $viewerRole = strtolower($viewer['quyen'] ?? '');
            $viewerId = $viewer['id'] ?? null;
            $canViewCCCD = false;
            if (!empty($viewerId) && !empty($viewedId) && (int)$viewerId === (int)$viewedId) {
                $canViewCCCD = true;
            } elseif ($viewerRole === 'super_admin') {
                $canViewCCCD = true;
            }
            ?>
            <?php // rating block moved into right column to avoid absolute-positioning anchor issues 
            ?>
        </div>

        <div class="profile-text-info">
            <div class="user-fullname"><?php echo htmlspecialchars($user['ho_ten'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></div>
            <?php
            // Map role from DB column 'quyen' to display label
            $roleRaw = strtolower(trim($user['quyen'] ?? ''));
            $roleMap = [
                'super_admin' => 'Cấp quản lý',
                'admin' => 'Cấp đầu chủ',
                'user' => 'Cấp đầu khách',
            ];
            $displayRole = $roleMap[$roleRaw] ?? 'Cấp đầu khách';
            ?>
            <div class="user-job-title"><?php echo htmlspecialchars($displayRole, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="user-office">Trụ sở <?php echo htmlspecialchars($user['phong_ban'] ?? $user['dia_chi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>

            <?php if (!empty($user['link_fb'])): ?>
                <a href="<?php echo htmlspecialchars($user['link_fb'], ENT_QUOTES, 'UTF-8'); ?>" class="fb-link" target="_blank" rel="noopener">
                    <i class="fa-brands fa-facebook"></i> <?php echo htmlspecialchars($user['ho_ten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-list-left">
                <div class="info-row"><i class="fa-solid fa-share-nodes"></i> <?php echo htmlspecialchars($user['phong_ban'] ?? $user['dia_chi'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-id-card"></i>
                    <?php if ($canViewCCCD): ?>
                        <?php echo htmlspecialchars($user['so_cccd'] ?? $user['ma_nhan_su'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['ma_nhan_su'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </div>
                <div class="info-row"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-regular fa-clock"></i> Tham gia: <?php echo !empty($user['created_at']) ? htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') : ''; ?></div>
                <div class="info-row"><i class="fa-regular fa-calendar"></i> Năm sinh: <?php echo htmlspecialchars($user['nam_sinh'] ?? ''); ?></div>
                <div class="info-row"><i class="fa-solid fa-location-dot"></i> Địa chỉ: <?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></div>
            </div>

            <div class="info-list-right">
                <?php
                $rating_count = isset($user['rating_count']) ? intval($user['rating_count']) : 0;
                $rating = ($rating_count === 0) ? 5.0 : (isset($user['rating']) && $user['rating'] !== null ? floatval($user['rating']) : 5.0);
                $full = floor($rating);
                $half = (($rating - $full) >= 0.5) ? 1 : 0;
                $empty = 5 - $full - $half;

                $currentId = $_SESSION['user']['id'] ?? null;
                $viewedId  = $user['id'] ?? null;
                $has_rated = isset($has_rated) ? (bool)$has_rated : false;
                ?>

                <!-- ===== RATING BOX (STANDARD) ===== -->
                <div
                    class="cover-rating-container rating--info"
                    data-user-id="<?= htmlspecialchars($viewedId) ?>"
                    data-current-user-id="<?= htmlspecialchars($currentId) ?>"
                    data-has-rated="<?= $has_rated ? '1' : '0' ?>">
                    <div class="rating-label">Đánh giá</div>

                    <div class="rating-number-row">
                        <span class="rating-number"><?= htmlspecialchars(number_format($rating, 1)) ?></span>
                        <i class="fa-solid fa-star rating-number-star" aria-hidden="true"></i>
                    </div>

                    <?php if (!$has_rated): ?>
                        <div class="rating-stars-large" aria-hidden="true">
                            <?php for ($i = 0; $i < $full; $i++): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>

                            <?php if ($half): ?>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            <?php endif; ?>

                            <?php for ($i = 0; $i < $empty; $i++): ?>
                                <i class="fa-regular fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                    <div class="rating-count-caption-small">
                        <?= htmlspecialchars(number_format($rating_count)) ?> Reviews
                    </div>
                </div>
                <!-- ===== END RATING BOX ===== -->

                <!-- CCCD -->
                <?php if ($canViewCCCD): ?>
                    <?php
                    // Normalize CCCD image URL: prefer full URL if stored, otherwise prefix BASE_URL/public/ or BASE_URL/
                    $rawCccd = $user['anh_cccd'] ?? $user['cccd_image'] ?? null;
                    $cccdSrc = 'https://cdn.tgdd.vn/Files/2022/06/10/1438726/hinh-anh-cccd-gan-chip-tgdd-1-3_1280x720-800-resize.jpg';
                    if (!empty($rawCccd)) {
                        if (stripos($rawCccd, 'http') === 0) {
                            $cccdSrc = $rawCccd;
                        } else {
                            // remove leading slashes
                            $path = ltrim($rawCccd, '/');
                            // if path already contains public/uploads or uploads
                            if (stripos($path, 'public/uploads') === 0 || stripos($path, 'uploads/') === 0) {
                                $cccdSrc = rtrim(BASE_URL, '/') . '/' . $path;
                            } else {
                                // assume stored relative to public/uploads
                                $cccdSrc = rtrim(BASE_URL, '/') . '/public/uploads/' . $path;
                            }
                        }
                    }
                    ?>
                    <img
                        src="<?= htmlspecialchars($cccdSrc, ENT_QUOTES, 'UTF-8') ?>"
                        class="id-card-img"
                        alt="CCCD"
                        style="background:#eee;">

                    <div class="id-number-caption">
                        <i class="fa-regular fa-id-card"></i>
                        <?= htmlspecialchars($user['so_cccd'] ?? 'Đang cập nhật', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="feed-list" id="feed-list-<?= htmlspecialchars($user['id'] ?? '0') ?>" data-initial-count="3" style="padding-top: 10px; padding-bottom: 80px;">

            <?php
            // Load recent posts by this user
            if (!empty($user['id'])) {
                require_once __DIR__ . '/../../Models/DealPost.php';
                $posts = DealPost::getByUser((int)$user['id'], 6);
            } else {
                $posts = [];
            }
            $initialShow = 3;
            ?>

            <?php if (empty($posts)): ?>
                <div style="padding:18px; color:#666; text-align:center;">Người dùng chưa có bài đăng nào.</div>
            <?php else: ?>
                <?php foreach ($posts as $idx => $p): ?>
                    <article class="post-card" style="<?php echo ($idx >= $initialShow) ? 'display:none; ' : ''; ?>margin-bottom: 10px;" data-post-index="<?= $idx ?>">
                        <div class="user-row">
                            <div class="user-left">
                                <img src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" class="user-avatar" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($user['ho_ten'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="rating-stars" style="color: #FFC107;">
                                        <?php echo !empty($p['created_at']) ? htmlspecialchars($p['created_at']) : ''; ?>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <?php
                                $phone = $user['so_dien_thoai'] ?? '';
                                $fb_link = $user['link_fb'] ?? '';
                                $zalo_link = $phone ? 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $phone) : '';
                                ?>
                                <?php if (!empty($fb_link)): ?>
                                    <a href="<?= htmlspecialchars($fb_link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Messenger" style="display:inline-flex; width:36px; height:36px; border-radius:50%; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; text-decoration:none; color:#0084ff;">
                                        <i class="fa-brands fa-facebook-messenger" style="font-size:16px;"></i>
                                    </a>
                                <?php else: ?>
                                    <span title="Messenger" style="display:inline-flex; width:36px; height:36px; border-radius:50%; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; color:#b0b0b0;">
                                        <i class="fa-brands fa-facebook-messenger" style="font-size:16px;"></i>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($zalo_link)): ?>
                                    <a href="<?= htmlspecialchars($zalo_link, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Zalo" style="display:inline-flex; width:36px; height:36px; border-radius:50%; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; text-decoration:none; color:#0068ff;">
                                        <i class="fa-solid fa-z" style="font-size:16px; font-weight:900;"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($phone)): ?>
                                    <a href="tel:<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>" title="Gọi ngay" style="display:inline-flex; width:36px; height:36px; border-radius:50%; align-items:center; justify-content:center; background:#fff; border:1px solid #eee; text-decoration:none; color:#00c853;">
                                        <i class="fa-solid fa-phone" style="font-size:16px;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; padding: 0 15px 10px 15px; align-items:flex-start;">
                            <?php if (!empty($p['images'][0])): ?>
                                <?php
                                $imgPath = $p['images'][0];
                                if (stripos($imgPath, 'http') === 0) {
                                    $thumb = $imgPath;
                                } else {
                                    $thumb = rtrim(BASE_URL, '/') . '/public/' . ltrim($imgPath, '/');
                                }
                                ?>
                                <img src="<?= htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') ?>" alt="thumb" style="width:84px; height:64px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                            <?php endif; ?>

                            <div style="flex:1; font-size:13px; color:#000;">
                                <div style="font-weight:700; margin-bottom:6px;">
                                    <?= htmlspecialchars($p['tieu_de'] ?? strip_tags(mb_substr($p['noi_dung'] ?? '', 0, 60)), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <?php
                                $fullText = strip_tags($p['noi_dung'] ?? '');
                                $excerpt = mb_substr($fullText, 0, 150);
                                $isLongText = mb_strlen($fullText) > 150;
                                ?>
                                <div class="post-text" style="font-size:13px; color:#444; line-height:1.4;">
                                    <div class="post-excerpt"><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if ($isLongText): ?>
                                        <div class="post-full" style="display:none;"><?= htmlspecialchars($fullText, ENT_QUOTES, 'UTF-8') ?></div>
                                        <div style="margin-top:6px;"><a href="#" class="read-toggle" data-expanded="0" style="color:#1e88e5; text-decoration:none;">Xem thêm</a></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (count($posts) > $initialShow): ?>
                    <div style="text-align:center; padding:10px 0;">
                        <button type="button" class="toggle-posts-btn" data-expanded="0">Xem thêm (<?php echo max(0, count($posts) - $initialShow); ?>)</button>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

        <!-- Rating modal (moved here to avoid affecting layout) -->
        <div id="rating-modal" class="modal" style="display:none; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:14px; border-radius:10px; width:280px; box-shadow:0 8px 22px rgba(0,0,0,0.12);">
                <div style="font-weight:700; margin-bottom:8px;">Đánh giá người dùng</div>
                <div id="rating-stars" style="display:flex; gap:6px; justify-content:center; font-size:28px; color:#FFC107; margin-bottom:8px;">
                    <i class="fa-regular fa-star" data-value="1"></i>
                    <i class="fa-regular fa-star" data-value="2"></i>
                    <i class="fa-regular fa-star" data-value="3"></i>
                    <i class="fa-regular fa-star" data-value="4"></i>
                    <i class="fa-regular fa-star" data-value="5"></i>
                </div>
                <div style="text-align:center; margin-bottom:8px;"><button id="rating-submit" class="btn-login" style="padding:8px 16px; color: white">Gửi đánh giá</button></div>
                <div style="text-align:center;"><a href="#" id="rating-cancel" style="color:#666; text-decoration:none;">Hủy</a></div>
            </div>
        </div>

        <script>
            window.BASE_PATH = '<?= BASE_PATH ?>';
        </script>
        <script src="<?= BASE_URL ?>/Public/Js/script.js"></script>
        <script>
            (function() {
                document.addEventListener('click', function(e) {
                    var readBtn = e.target.closest && e.target.closest('.read-toggle');
                    if (readBtn) {
                        e.preventDefault();
                        var container = readBtn.closest('.post-text');
                        if (!container) return;
                        var excerpt = container.querySelector('.post-excerpt');
                        var full = container.querySelector('.post-full');
                        if (!excerpt || !full) return;
                        if (readBtn.dataset.expanded === '1') {
                            full.style.display = 'none';
                            excerpt.style.display = 'block';
                            readBtn.textContent = 'Xem thêm';
                            readBtn.dataset.expanded = '0';
                        } else {
                            full.style.display = 'block';
                            excerpt.style.display = 'none';
                            readBtn.textContent = 'Thu gọn';
                            readBtn.dataset.expanded = '1';
                        }
                        return;
                    }

                    var btn = e.target.closest && e.target.closest('.toggle-posts-btn');
                    if (!btn) return;
                    var feed = btn.closest('.feed-list');
                    if (!feed) return;
                    var initial = parseInt(feed.dataset.initialCount || 3, 10) || 3;
                    var posts = Array.prototype.slice.call(feed.querySelectorAll('.post-card'));
                    if (btn.dataset.expanded === '1') {
                        posts.forEach(function(p, i) {
                            if (i >= initial) p.style.display = 'none';
                        });
                        btn.textContent = 'Xem thêm (' + Math.max(0, posts.length - initial) + ')';
                        btn.dataset.expanded = '0';
                    } else {
                        posts.forEach(function(p, i) {
                            if (i >= initial) p.style.display = 'block';
                        });
                        btn.textContent = 'Thu gọn';
                        btn.dataset.expanded = '1';
                    }
                });
            })();
        </script>
    </div>
</body>

</html>