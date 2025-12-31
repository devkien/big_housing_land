<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết tin đăng</title>
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

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/management-resource" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chi tiết</div>
            <div class="header-icon-btn"></div>
        </header>
        <div class="feed-list" style="padding-bottom: 80px;">

            <?php if (!empty($property)):
                // Xử lý giá
                $price = 'Thỏa thuận';
                if (!empty($property['gia_chao'])) {
                    $val = (float)$property['gia_chao'];
                    if ($val >= 1000000000) {
                        $price = round($val / 1000000000, 2) . ' tỷ';
                    } elseif ($val >= 1000000) {
                        $price = round($val / 1000000, 0) . ' triệu';
                    } else {
                        $price = number_format($val, 0, ',', '.') . ' VND';
                    }
                }

                // Xử lý diện tích
                $area = '';
                if (!empty($property['dien_tich'])) {
                    $area = $property['dien_tich'] . ' ' . ($property['don_vi_dien_tich'] ?? 'm²');
                }

                // Giá trên m2
                $pricePerM2 = '';
                if (!empty($property['gia_chao']) && !empty($property['dien_tich']) && $property['dien_tich'] > 0) {
                    $ppm2 = $property['gia_chao'] / $property['dien_tich'];
                    if ($ppm2 >= 1000000) {
                        $pricePerM2 = round($ppm2 / 1000000, 1) . ' tr/m²';
                    } else {
                        $pricePerM2 = number_format($ppm2, 0, ',', '.') . '/m²';
                    }
                }

                // Trạng thái
                $statusMap = [
                    'ban_manh' => 'Bán mạnh',
                    'tam_dung_ban' => 'Tạm dừng',
                    'dung_ban' => 'Dừng bán',
                    'da_ban' => 'Đã bán',
                    'tang_chao' => 'Tăng chào',
                    'ha_chao' => 'Hạ chào'
                ];
                $statusLabel = $statusMap[$property['trang_thai'] ?? ''] ?? 'Đang bán';

                // User info
                $userName = $property['user_name'] ?? '---';
                $userPhone = $property['user_phone'] ?? '';
                $userAvatar = !empty($property['user_avatar']) ? BASE_URL . '/' . $property['user_avatar'] : BASE_URL . '/icon/menuanhdaidien.png';

                // Tags
                $tags = [];
                if (!empty($property['loai_bds'])) $tags[] = $property['loai_bds'] == 'nha_pho' ? 'Nhà phố' : 'Đất nền';
                if (!empty($property['phap_ly'])) $tags[] = $property['phap_ly'] == 'co_so' ? 'Có sổ' : 'Không sổ';

                // Address parts for tags or display
                $addrParts = array_filter([$property['xa_phuong'] ?? '', $property['quan_huyen'] ?? '', $property['tinh_thanh'] ?? '']);
                $shortAddr = implode(', ', $addrParts);
            ?>
                <article class="post-card">
                    <div class="user-row">
                        <div class="user-left">
                            <img src="<?= htmlspecialchars($userAvatar) ?>" class="user-avatar">
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($userName) ?> - <?= htmlspecialchars($property['phong_ban'] ?? '') ?></div>
                                <div class="rating-stars">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <div class="contact-icons">
                                        <a href="tel:<?= htmlspecialchars($userPhone) ?>" style="color: inherit; margin-right: 10px;"><i class="fa-solid fa-phone c-icon icon-phone"></i></a>
                                        <i class="fa-brands fa-facebook-messenger c-icon icon-mess"></i>
                                        <i class="fa-solid fa-z c-icon icon-zalo"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn-status-outline"><?= htmlspecialchars($statusLabel) ?></button>
                    </div>

                    <div class="price-tag-row">
                        <div class="price-text"><?= $price ?> <?= $pricePerM2 ? '- ' . $pricePerM2 : '' ?></div>
                        <div class="tags-group">
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag-gray"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                            <?php if ($shortAddr): ?>
                                <span class="tag-gray"><?= htmlspecialchars($shortAddr) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="post-content">
                        <div class="auto-truncate-text">
                            <?= nl2br(htmlspecialchars($property['mo_ta'] ?? '')) ?>
                        </div>
                        <div style="margin-top: 5px;"></div>
                    </div>

                    <div class="meta-row">
                        <span class="red-badge"><?= (!empty($property['phap_ly']) && $property['phap_ly'] == 'co_so') ? 'Sổ đỏ/Sổ hồng' : 'Pháp lý khác' ?></span>
                        <span class="code-text">Mã số: <span class="code-number">#<?= htmlspecialchars($property['ma_hien_thi'] ?? $property['id']) ?></span></span>
                    </div>

                    <?php if (!empty($property['media'])): ?>
                        <div class="post-images-list" style="margin-top: 15px;">
                            <?php foreach ($property['media'] as $m):
                                $mediaPath = $m['media_path'] ?? $m['path'] ?? '';
                                if (strpos($mediaPath, 'http') !== 0) $mediaPath = BASE_URL . '/' . $mediaPath;
                            ?>
                                <img src="<?= htmlspecialchars($mediaPath) ?>" class="post-image-large" style="margin-bottom: 10px; border-radius: 8px;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php else: ?>
                <div style="padding: 20px; text-align: center;">Không tìm thấy thông tin tài nguyên.</div>
            <?php endif; ?>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>