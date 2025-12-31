<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quản lý khách tự khớp</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Quản lý khách tự khớp</div>
            <div class="header-icon-btn"></div>
        </header>

        <div id="filter-container" style="padding: 15px;">
            <label class="search-label-small">Loại tin BĐS</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" id="match-type">
                <option value="">Tất cả</option>
                <option value="ban" <?= (isset($filters['type']) && $filters['type'] === 'ban') ? 'selected' : '' ?>>Mua bán</option>
                <option value="cho_thue" <?= (isset($filters['type']) && $filters['type'] === 'cho_thue') ? 'selected' : '' ?>>Cho thuê</option>
            </select>

            <label class="search-label-small">Khu vực</label>
            <input type="text" class="input-blue-border" style="margin-bottom: 10px;" id="match-location" placeholder="Nhập khu vực...">

            <label class="search-label-small">Khoảng giá</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" id="match-price">
                <option value="">Tất cả</option>
                <option value="lt_5" <?= (isset($filters['price']) && $filters['price'] === 'lt_5') ? 'selected' : '' ?>>Dưới 5 tỷ</option>
                <option value="5_10" <?= (isset($filters['price']) && $filters['price'] === '5_10') ? 'selected' : '' ?>>5 - 10 tỷ</option>
                <option value="10_20" <?= (isset($filters['price']) && $filters['price'] === '10_20') ? 'selected' : '' ?>>10 - 20 tỷ</option>
                <option value="gt_20" <?= (isset($filters['price']) && $filters['price'] === 'gt_20') ? 'selected' : '' ?>>Trên 20 tỷ</option>
            </select>

            <label class="search-label-small">Pháp lý</label>
            <select class="select-blue-border" style="margin-bottom: 10px;" id="match-legal">
                <option value="">Tất cả</option>
                <option value="so_do" <?= (isset($filters['legal']) && $filters['legal'] === 'so_do') ? 'selected' : '' ?>>Sổ đỏ</option>
                <option value="khong_so" <?= (isset($filters['legal']) && $filters['legal'] === 'khong_so') ? 'selected' : '' ?>>Không sổ</option>
            </select>

            <label class="search-label-small">Diện tích</label>
            <div class="area-input-group">
                <input type="number" id="match-area" value="<?= isset($filters['area']) ? htmlspecialchars($filters['area']) : '' ?>">
                <div class="area-unit">m2</div>
            </div>
            <button class="btn-submit-blue" id="btn-show-results" style="margin-top: 20px; width: 100%; margin-left: 0; margin-right: 0;">Tìm kiếm</button>
        </div>

        <div id="result-header-container" style="padding: 0 15px; display: none;">
            <div class="result-header">Các bài viết kết quả hiển thị</div>
        </div>

        <?php if (empty($properties)) : ?>
            <div id="post-list-container" style="padding-bottom: 80px; display: none;">

                <article class="post-card">
                    <div class="user-row">
                        <div class="user-left">
                            <img src="icon/menuanhdaidien.png" class="user-avatar">
                            <div class="user-info">
                                <div class="user-name" onclick="window.location.href='detailprofile.html'">TP Nguyễn Kim Ngàn - NPHN - 888</div>
                                <div class="rating-stars">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <div class="contact-icons">
                                        <i class="fa-brands fa-facebook-messenger c-icon icon-mess"></i>
                                        <i class="fa-solid fa-z c-icon icon-zalo"></i>
                                        <i class="fa-solid fa-phone c-icon icon-phone"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn-status-outline">Bán mạnh</button>
                    </div>

                    <div class="price-tag-row">
                        <div class="price-text">8.4 tỷ - 646tr/m²</div>
                        <div class="tags-group">
                            <span class="tag-gray">Mặt phố</span>
                            <span class="tag-gray">Kinh doanh</span>
                        </div>
                    </div>

                    <div class="post-content">
                        <div class="auto-truncate-text">
                            <strong>NHÀ PHỐ VIỆT NAM - CHỐT NHÀ TOÀN QUỐC</strong> Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.

                        </div>
                        <div style="margin-top: 5px;"></div>
                        <span class="hashtag">#tpnguyenkimngan</span> <span class="hashtag">#nphn</span>
                    </div>

                    <div class="meta-row">
                        <span class="red-badge">Số đỏ/sổ hồng</span>
                        <span class="code-text">Mã số: <span class="code-number">#101944</span></span>
                    </div>

                    <img src="../img/phongkhach.png" class="post-image-large">
                </article>

                <article class="post-card">
                    <div class="user-row">
                        <div class="user-left">
                            <img src="icon/menuanhdaidien.png" class="user-avatar">
                            <div class="user-info">
                                <div class="user-name">TP Nguyễn Kim Ngàn - NPHN - 888</div>
                                <div class="rating-stars">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <div class="contact-icons">
                                        <i class="fa-brands fa-facebook-messenger c-icon icon-mess"></i>
                                        <i class="fa-solid fa-z c-icon icon-zalo"></i>
                                        <i class="fa-solid fa-phone c-icon icon-phone"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn-status-outline">Bán mạnh</button>
                    </div>

                    <div class="price-tag-row">
                        <div class="price-text">8.4 tỷ - 646tr/m²</div>
                        <div class="tags-group">
                            <span class="tag-gray">Mặt phố</span>
                            <span class="tag-gray">Kinh doanh</span>
                        </div>
                    </div>

                    <div class="post-content">
                        <div class="auto-truncate-text">
                            <strong>NHÀ PHỐ VIỆT NAM</strong> Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                            Kính thưa Chủ tịch, Tổng Giám đốc cùng các ban lãnh đạo. Tôi Trưởng phòng Nguyễn Kim Ngân - NPDN xin vui mừng thông báo CV Nguyễn Khanh - Nhà Phố 888 chốt hạ thành công căn nhà siêu phẩm.
                        </div>
                        <div style="margin-top: 5px;"></div>
                    </div>
                    <div class="meta-row">
                        <span class="red-badge">Số đỏ/sổ hồng</span>
                        <span class="code-text">Mã số: <span class="code-number">#101944</span></span>
                    </div>
                    <img src="../img/phongkhach.png" class="post-image-large">
                </article>

            </div>

        <?php else: ?>
            <div id="post-list-container" style="padding-bottom: 80px;">
                <?php foreach ($properties as $p) :
                    $title = htmlspecialchars($p['tieu_de'] ?? '');
                    $code = htmlspecialchars($p['ma_hien_thi'] ?? ($p['id'] ?? ''));
                    $statusKey = $p['trang_thai'] ?? '';
                    $statusMap = [
                        'ban_manh' => 'Bán mạnh',
                        'tam_dung_ban' => 'Tạm dừng bán',
                        'dung_ban' => 'Dừng bán',
                        'da_ban' => 'Đã bán',
                        'tang_chao' => 'Tăng chào',
                        'ha_chao' => 'Hạ chào'
                    ];
                    $statusLabel = htmlspecialchars($statusMap[$statusKey] ?? $statusKey);

                    // address
                    $address = trim($p['dia_chi_chi_tiet'] ?? '');
                    if ($address === '') {
                        $parts = array_filter([$p['tinh_thanh'] ?? '', $p['quan_huyen'] ?? '', $p['xa_phuong'] ?? '']);
                        $address = htmlspecialchars(implode(', ', $parts));
                    } else {
                        $address = htmlspecialchars($address);
                    }

                    // thumbnail
                    $thumbHtml = '';
                    if (!empty($p['thumb'])) {
                        $img = $p['thumb'];
                        if (stripos($img, 'http://') === 0 || stripos($img, 'https://') === 0) $src = $img;
                        else $src = rtrim(BASE_URL, '/') . '/' . ltrim($img, '/');
                        $thumbHtml = '<img src="' . htmlspecialchars($src) . '" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">';
                    } else {
                        $thumbHtml = '<div style="width:100%;height:100%;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888;">thumb</div>';
                    }

                    // price + per-area formatting (reuse logic from resource-detail)
                    $gia = isset($p['gia_chao']) && $p['gia_chao'] !== null && $p['gia_chao'] !== '' ? (float)$p['gia_chao'] : null;
                    $area = isset($p['dien_tich']) && $p['dien_tich'] !== null && $p['dien_tich'] !== '' ? (float)$p['dien_tich'] : null;
                    $areaUnit = strtolower(trim($p['don_vi_dien_tich'] ?? 'm2'));
                    if ($gia === null) {
                        $mainPrice = 'Liên hệ';
                    } else {
                        if ($gia >= 1000000000) {
                            $val = round($gia / 1000000000, 1);
                            $val = rtrim(rtrim(number_format($val, 1, '.', ''), '0'), '.');
                            $mainPrice = $val . ' tỷ';
                        } elseif ($gia >= 1000000) {
                            $val = round($gia / 1000000);
                            $mainPrice = $val . ' triệu';
                        } else {
                            $mainPrice = number_format($gia, 0, ',', '.') . ' VND';
                        }
                    }
                    $perUnitText = '';
                    if ($gia !== null && $area && $area > 0) {
                        $ppu_million = ($gia / $area) / 1000000;
                        $unitLabel = ($areaUnit === 'ha') ? 'tr/ha' : 'tr/m²';
                        $ppu_round = round($ppu_million);
                        $perUnitText = $ppu_round . $unitLabel;
                    }

                    // tags: attempt to derive simple tags, show only if present
                    $tags = [];
                    if (!empty($p['tags'])) {
                        $raw = $p['tags'];
                        $decoded = @json_decode($raw, true);
                        if (is_array($decoded)) $tags = $decoded;
                        else $tags = array_map('trim', explode(',', $raw));
                    } else {
                        // simple keyword checks in title
                        $td = strtolower($p['tieu_de'] ?? '');
                        if (strpos($td, 'mặt phố') !== false) $tags[] = 'Mặt phố';
                        if (strpos($td, 'kinh doanh') !== false) $tags[] = 'Kinh doanh';
                        if (strpos($td, 'cửa hàng') !== false) $tags[] = 'Cửa hàng';
                        if (strpos($td, 'lô') !== false) $tags[] = 'Lô';
                    }
                ?>
                    <article class="post-card" onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource-detail?id=<?= (int)$p['id'] ?>'">
                        <div class="user-row">
                            <div class="user-left">
                                <img src="<?= rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png' ?>" class="user-avatar">
                                <div class="user-info">
                                    <?php
                                    // Ưu tiên hiển thị họ tên, nếu không có thì dùng phòng ban
                                    $userName = !empty($p['ho_ten']) ? $p['ho_ten'] : ($p['phong_ban'] ?? 'Người đăng');
                                    if (empty($p['ho_ten']) && !empty($p['user_id'])) $userName .= ' - ' . $p['user_id'];
                                    ?>
                                    <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                                    <div class="rating-stars">
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <div class="contact-icons">
                                            <i class="fa-brands fa-facebook-messenger c-icon icon-mess"></i>
                                            <i class="fa-solid fa-z c-icon icon-zalo"></i>
                                            <i class="fa-solid fa-phone c-icon icon-phone"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-status-outline"><?= $statusLabel ?></button>
                        </div>

                        <div class="price-tag-row">
                            <div class="price-text"><?= htmlspecialchars($mainPrice) ?><?= $perUnitText ? ' - ' . htmlspecialchars($perUnitText) : '' ?></div>
                            <div class="tags-group">
                                <?php foreach ($tags as $t): ?>
                                    <span class="tag-gray"><?= htmlspecialchars($t) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="post-content">
                            <div class="auto-truncate-text">
                                <strong><?= $title ?></strong>
                                <?= nl2br(htmlspecialchars($p['mo_ta'] ?? '')) ?>
                            </div>
                            <div style="margin-top: 5px;"></div>
                            <?php if (!empty($p['ma_gioi_thieu'] ?? null)): ?>
                                <span class="hashtag"><?= htmlspecialchars($p['ma_gioi_thieu']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="meta-row">
                            <?php if (!empty($p['ma_so_so'])): ?>
                                <span class="red-badge">Số đỏ/sổ hồng</span>
                            <?php endif; ?>
                            <span class="code-text">Mã số: <span class="code-number">#<?= $code ?></span></span>
                        </div>

                        <?php
                        // image: use thumb if available, else placeholder
                        $imgSrc = '';
                        if (!empty($p['thumb'])) {
                            $imgRaw = $p['thumb'];
                            if (stripos($imgRaw, 'http://') === 0 || stripos($imgRaw, 'https://') === 0) $imgSrc = $imgRaw;
                            else $imgSrc = rtrim(BASE_URL, '/') . '/' . ltrim($imgRaw, '/');
                        } else {
                            $imgSrc = rtrim(BASE_URL, '/') . '/img/phongkhach.png';
                        }
                        ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="post-image-large">
                    </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- Modal Tìm kiếm cho trang Auto Match -->
        <div id="search-modal-match" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm</h3>
                <div class="filter-group">
                    <input type="text" id="search-input-match" class="filter-input" placeholder="Nhập từ khóa (Mã tin, địa chỉ, nội dung)...">
                </div>
                <div class="modal-actions">
                    <button id="close-search-match" class="btn-cancel">Hủy</button>
                    <button id="apply-search-match" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnShow = document.getElementById('btn-show-results');

            if (btnShow) {
                btnShow.addEventListener('click', function() {
                    const type = document.getElementById('match-type') ? document.getElementById('match-type').value : '';
                    const location = document.getElementById('match-location') ? document.getElementById('match-location').value.trim() : '';
                    const price = document.getElementById('match-price') ? document.getElementById('match-price').value : '';
                    const legal = document.getElementById('match-legal') ? document.getElementById('match-legal').value : '';
                    const area = document.getElementById('match-area') ? document.getElementById('match-area').value : '';

                    const params = new URLSearchParams(window.location.search);
                    if (type) params.set('type', type);
                    else params.delete('type');
                    if (location) params.set('location', location);
                    else params.delete('location');
                    if (price) params.set('price', price);
                    else params.delete('price');
                    if (legal) params.set('legal', legal);
                    else params.delete('legal');
                    if (area) params.set('area', area);
                    else params.delete('area');
                    params.delete('page');

                    // ensure search flag is set so server knows to run query
                    params.set('searched', '1');

                    const qs = params.toString();
                    window.location.search = qs ? ('?' + qs) : '';
                });
            }
        });
    </script>
</body>

</html>