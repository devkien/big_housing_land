<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết tin đăng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Chi tiết</div>
            <div class="header-icon-btn"></div>
        </header>
        <div class="feed-list" style="padding-bottom: 80px;">

            <?php if (!empty($property)):
                // --- DATA PREPARATION ---
                $price = 'Thỏa thuận';
                if (!empty($property['gia_chao'])) {
                    $val = (float)$property['gia_chao'];
                    if ($val >= 1000000000) $price = round($val / 1000000000, 2) . ' tỷ';
                    elseif ($val >= 1000000) $price = round($val / 1000000, 0) . ' triệu';
                    else $price = number_format($val, 0, ',', '.') . ' VND';
                }
                $pricePerM2 = '';
                if (!empty($property['gia_chao']) && !empty($property['dien_tich']) && $property['dien_tich'] > 0) {
                    $ppm2 = $property['gia_chao'] / $property['dien_tich'];
                    if ($ppm2 >= 1000000) $pricePerM2 = round($ppm2 / 1000000, 1) . ' tr/m²';
                    else $pricePerM2 = number_format($ppm2, 0, ',', '.') . '/m²';
                }
                $statusMap = ['ban_manh' => 'Bán mạnh', 'tam_dung_ban' => 'Tạm dừng', 'dung_ban' => 'Dừng bán', 'da_ban' => 'Đã bán', 'tang_chao' => 'Tăng chào', 'ha_chao' => 'Hạ chào'];
                $statusLabel = $statusMap[$property['trang_thai'] ?? ''] ?? 'Đang bán';
                $userName = $property['ho_ten'] ?? '---';
                $userPhoneRaw = $property['user_phone'] ?? $property['so_dien_thoai'] ?? '';
                $phoneDigits = $userPhoneRaw ? preg_replace('/[^0-9+]/', '', $userPhoneRaw) : '';
                $phoneHref = $phoneDigits ? 'tel:' . $phoneDigits : '#';
                $zaloHref = $phoneDigits ? 'https://zalo.me/' . ltrim($phoneDigits, '+') : '#';
                $linkFbRaw = $property['link_fb'] ?? '';
                $linkFb = '#';
                if (!empty($linkFbRaw)) {
                    $linkFb = $linkFbRaw;
                    if (!preg_match('#^https?://#i', $linkFb)) $linkFb = 'https://facebook.com/' . ltrim($linkFb, '/');
                }
                $userAvatar = !empty($property['avatar']) ? BASE_URL . '/' . ltrim($property['avatar'], '/') : BASE_URL . '/icon/menuanhdaidien.png';
            ?>
                <article class="post-card">
                    <div class="user-row">
                        <div class="user-left">
                            <img src="<?= htmlspecialchars($userAvatar) ?>" class="user-avatar">
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($userName) ?> <span style="font-weight:normal; font-size: 13px; color: #666;">- <?= htmlspecialchars($property['phong_ban'] ?? '') ?></span></div>
                                <div class="rating-stars">
                                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    <?php if (!empty($property['created_at'])): ?>
                                        <span style="color: #666; font-size: 12px; margin-left: 8px;"><?= date('d/m/Y', strtotime($property['created_at'])) ?></span>
                                    <?php endif; ?>
                                    <div class="contact-icons">
                                        <a href="<?= htmlspecialchars($phoneHref) ?>" style="color: inherit; margin-right: 10px;"><i class="fa-solid fa-phone c-icon icon-phone"></i></a>
                                        <a href="<?= htmlspecialchars($linkFb) ?>" target="_blank" rel="noopener noreferrer" class="c-icon" style="margin-right:10px;"><i class="fa-brands fa-facebook-messenger c-icon icon-mess"></i></a>
                                        <a href="<?= htmlspecialchars($zaloHref) ?>" target="_blank" rel="noopener noreferrer" class="c-icon"><i class="fa-solid fa-z c-icon icon-zalo"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn-status-outline"><?= htmlspecialchars($statusLabel) ?></button>
                    </div>

                    <div class="price-tag-row">
                        <div class="price-text"><?= $price ?> <?= $pricePerM2 ? '- ' . $pricePerM2 : '' ?></div>
                    </div>

                    <div class="specs-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; background: #f9f9f9; padding: 10px; border-radius: 8px; font-size: 14px;">
                        <div><i class="fa-solid fa-ruler-combined" style="color: #666; width: 20px;"></i> <strong>Diện tích:</strong> <?= htmlspecialchars($property['dien_tich'] ?? '') ?> <?= htmlspecialchars($property['don_vi_dien_tich'] ?? 'm²') ?></div>
                        <div><i class="fa-solid fa-arrows-left-right" style="color: #666; width: 20px;"></i> <strong>Mặt tiền:</strong> <?= htmlspecialchars($property['chieu_rong'] ?? '') ?> m</div>
                        <div><i class="fa-solid fa-arrows-up-down" style="color: #666; width: 20px;"></i> <strong>Chiều dài:</strong> <?= htmlspecialchars($property['chieu_dai'] ?? '') ?> m</div>
                        <div><i class="fa-solid fa-layer-group" style="color: #666; width: 20px;"></i> <strong>Số tầng:</strong> <?= htmlspecialchars($property['so_tang'] ?? '') ?></div>
                        <?php if (!empty($property['huong'])): ?>
                            <div><i class="fa-regular fa-compass" style="color: #666; width: 20px;"></i> <strong>Hướng:</strong> <?= htmlspecialchars($property['huong']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['duong_vao'])): ?>
                            <div><i class="fa-solid fa-road" style="color: #666; width: 20px;"></i> <strong>Đường vào:</strong> <?= htmlspecialchars($property['duong_vao']) ?> m</div>
                        <?php endif; ?>
                        <?php if (!empty($property['so_phong_ngu'])): ?>
                            <div><i class="fa-solid fa-bed" style="color: #666; width: 20px;"></i> <strong>PN:</strong> <?= htmlspecialchars($property['so_phong_ngu']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['so_nha_ve_sinh'])): ?>
                            <div><i class="fa-solid fa-bath" style="color: #666; width: 20px;"></i> <strong>WC:</strong> <?= htmlspecialchars($property['so_nha_ve_sinh']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['ten_chu_nha'])): ?>
                            <div><i class="fa-solid fa-user-tie" style="color: #666; width: 20px;"></i> <strong>Chủ nhà:</strong> <?= htmlspecialchars($property['ten_chu_nha']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['so_dien_thoai_chu_nha'])): ?>
                            <div><i class="fa-solid fa-phone" style="color: #666; width: 20px;"></i> <strong>SĐT Chủ:</strong> <?= htmlspecialchars($property['so_dien_thoai_chu_nha']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['anh_chu_nha'])): ?>
                            <div style="grid-column: span 2;">
                                <i class="fa-solid fa-image-portrait" style="color: #666; width: 20px;"></i> <strong>Ảnh chủ nhà:</strong><br>
                                <img src="<?= BASE_URL . '/' . ltrim($property['anh_chu_nha'], '/') ?>" style="max-width: 100%; max-height: 300px; margin-top: 5px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                        <div><i class="fa-solid fa-file-contract" style="color: #666; width: 20px;"></i> <strong>Pháp lý:</strong> <?= ($property['phap_ly'] ?? '') === 'co_so' ? 'Sổ đỏ/Sổ hồng' : 'Chưa có sổ' ?></div>
                        <?php if (!empty($property['ma_so_so'])): ?>
                            <div><i class="fa-solid fa-barcode" style="color: #666; width: 20px;"></i> <strong>Mã sổ:</strong> <?= htmlspecialchars($property['ma_so_so']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($property['anh_so'])): ?>
                            <div style="grid-column: span 2;">
                                <i class="fa-solid fa-file-image" style="color: #666; width: 20px;"></i> <strong>Ảnh sổ:</strong><br>
                                <img src="<?= BASE_URL . '/' . ltrim($property['anh_so'], '/') ?>" style="max-width: 100%; max-height: 300px; margin-top: 5px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property['trich_thuong_gia_tri'])): ?>
                            <div><i class="fa-solid fa-gift" style="color: #666; width: 20px;"></i> <strong>Trích thưởng:</strong> <?= htmlspecialchars($property['trich_thuong_gia_tri']) ?> <?= htmlspecialchars($property['trich_thuong_don_vi'] ?? '') ?></div>
                        <?php endif; ?>
                    </div>

                    <?php
                        $addrParts = [];
                        
                        // 1. Detail Address
                        if (!empty($property['dia_chi_chi_tiet'])) {
                            $addrParts[] = $property['dia_chi_chi_tiet'];
                        }
                        
                        // 2. Ward/Commune
                        if (!empty($property['xa_phuong'])) {
                            $addrParts[] = $property['xa_phuong'];
                        }
                        
                        // 3. District
                        if (!empty($property['quan_huyen'])) {
                            $addrParts[] = $property['quan_huyen'];
                        }
                        
                        // 4. Province/City
                        if (!empty($property['tinh_thanh'])) {
                            $addrParts[] = $property['tinh_thanh'];
                        }
                        
                        $fullAddress = implode(', ', $addrParts);
                    ?>
                    
                    <?php if (!empty($fullAddress)): ?>
                        <div style="margin-top: 10px; font-size: 14px; color: #333; padding: 0 5px;">
                            <i class="fa-solid fa-location-dot" style="color: #666; width: 20px;"></i> 
                            <strong>Địa chỉ chi tiết:</strong> <?= htmlspecialchars($fullAddress) ?>
                        </div>
                    <?php endif; ?>
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