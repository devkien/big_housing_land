<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kho nhà cho thuê</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script>
        window.BASE_PATH = '<?= BASE_PATH ?>';
    </script>
    <script>
        window.CURRENT_RESOURCE_TYPE = 'kho_cho_thue';
    </script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="resource-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="resource-title">Kho tài nguyên</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="tabs-container">
            <button class="tab-btn inactive" onclick="window.location.href='<?= BASE_URL ?>/superadmin/management-resource'">Kho nhà đất</button>
            <button class="tab-btn active">Kho nhà cho thuê</button>
        </div>

        <div class="toolbar-section">
            <button class="tool-btn" id="btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
            <div style="flex:1;"></div>
        </div>

        <div class="table-wrapper" style="margin-bottom: 0;">
            <table class="resource-table" style="min-width:1500px;">
                <thead>
                    <tr>
                        <th style="padding-left:15px; width: 60px;">LƯU</th>
                        <th style="width: 60px;">GHI CHÚ</th>
                        <th style="width: 100px;">HÀNH ĐỘNG</th>
                        <th style="width: 120px;">MÃ HIỂN THỊ</th>
                        <th style="width: 100px;">THỜI GIAN</th>
                        <th style="width: 100px;">PHÒNG BAN</th>
                        <th style="width: 240px;">TIÊU ĐỀ</th>
                        <th style="width: 100px;">LOẠI BĐS</th>
                        <th style="width: 100px;">LOẠI KHO</th>
                        <th style="width: 80px;">CÓ SỔ</th>
                        <th style="width: 120px;">MÃ SỔ</th>
                        <th style="width:100px;">DIỆN TÍCH</th>
                        <th style="width:80px">ĐV</th>
                        <th style="width:90px">CHIỀU DÀI</th>
                        <th style="width:90px">CHIỀU RỘNG</th>
                        <th style="width:80px">SỐ TẦNG</th>
                        <th style="width:140px; text-align:right; padding-right:15px;">GIÁ CHÀO</th>
                        <th style="width:120px;">HIỆN TRẠNG</th>
                        <th style="text-align:right; padding-right:15px;">ĐỊA CHỈ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $statusMap = [
                        'ban_manh' => 'Bán mạnh',
                        'tam_dung_ban' => 'Tạm dừng',
                        'dung_ban' => 'Dừng bán',
                        'da_ban' => 'Đã bán',
                        'tang_chao' => 'Tăng chào',
                        'ha_chao' => 'Hạ chào'
                    ];

                    if (empty($properties)) :
                    ?>
                        <tr>
                            <td colspan="19" style="text-align:center; padding:20px;">Không tìm thấy tài nguyên nào.</td>
                        </tr>
                        <?php else :
                        foreach ($properties as $p) :
                            $code = htmlspecialchars($p['ma_hien_thi'] ?? '');
                            $created = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '';
                            $status = $statusMap[$p['trang_thai'] ?? ''] ?? ($p['trang_thai'] ?? '');
                            $address = trim($p['dia_chi_chi_tiet'] ?? '');
                            if ($address === '') {
                                $parts = array_filter([$p['tinh_thanh'] ?? '', $p['quan_huyen'] ?? '', $p['xa_phuong'] ?? '']);
                                $address = htmlspecialchars(implode(', ', $parts));
                            } else {
                                $address = htmlspecialchars($address);
                            }
                        ?>
                            <?php
                            $phong_ban = htmlspecialchars($p['phong_ban'] ?? '');
                            $tieu_de = htmlspecialchars($p['tieu_de'] ?? '');
                            $loai_bds_map = ['ban' => 'Bán', 'cho_thue' => 'Cho thuê'];
                            $loai_bds = $loai_bds_map[$p['loai_bds'] ?? ''] ?? ($p['loai_bds'] ?? '');
                            $loai_kho_map = ['kho_nha_dat' => 'Kho nhà đất', 'kho_cho_thue' => 'Kho cho thuê'];
                            $loai_kho = $loai_kho_map[$p['loai_kho'] ?? ''] ?? ($p['loai_kho'] ?? '');
                            $phap_ly_map = ['co_so' => 'Có sổ', 'khong_so' => 'Không sổ'];
                            $phap_ly = $phap_ly_map[$p['phap_ly'] ?? ''] ?? ($p['phap_ly'] ?? '');
                            $ma_so_so = htmlspecialchars($p['ma_so_so'] ?? '');
                            $dien_tich = isset($p['dien_tich']) && $p['dien_tich'] !== null ? (float)$p['dien_tich'] : null;
                            $don_vi = htmlspecialchars($p['don_vi_dien_tich'] ?? '');
                            $chieu_dai = isset($p['chieu_dai']) && $p['chieu_dai'] !== null ? (float)$p['chieu_dai'] : null;
                            $chieu_rong = isset($p['chieu_rong']) && $p['chieu_rong'] !== null ? (float)$p['chieu_rong'] : null;
                            $so_tang = isset($p['so_tang']) && $p['so_tang'] !== null ? (int)$p['so_tang'] : null;
                            $gia_chao = isset($p['gia_chao']) && $p['gia_chao'] !== null ? (float)$p['gia_chao'] : null;
                            $gia_chao_fmt = $gia_chao !== null ? number_format($gia_chao, 0, ',', '.') . ' VND' : '';
                            ?>
                            <tr data-id="<?= htmlspecialchars($p['id']) ?>">
                                <?php $inCount = isset($collectionMap[(int)$p['id']]) ? (int)$collectionMap[(int)$p['id']] : 0; ?>
                                <td style="padding-left:15px;">
                                    <i class="<?= $inCount > 0 ? 'fa-solid' : 'fa-regular' ?> fa-bookmark icon-save" style="<?= $inCount > 0 ? 'color:#ffcc00' : '' ?>" title="<?= $inCount > 0 ? 'Đã lưu (' . $inCount . ')' : 'Chưa lưu' ?>"></i>
                                </td>
                                <td><i class="fa-regular fa-note-sticky icon-note"></i></td>

                                <td style="text-align: center;">
                                    <a href="<?= BASE_URL ?>/superadmin/management-resource-edit?id=<?= $p['id'] ?>" class="btn-action-icon edit" title="Sửa">
                                        <i class="fa-solid fa-pen" style="color: #0044cc; margin-right: 10px;"></i>
                                    </a>
                                    <form action="<?= BASE_URL ?>/superadmin/management-resource-delete" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này không?');">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-action-icon delete" style="border:none; background:none; padding:0; cursor: pointer;" title="Xóa"><i class="fa-solid fa-trash" style="color: #dc3545;"></i></button>
                                    </form>
                                </td>

                                <td style="cursor:pointer; color:#0b66ff;" onclick="window.location.href='<?= BASE_URL ?>/superadmin/management-resource-detail?id=<?= htmlspecialchars($p['id']) ?>'"><?= $code ?></td>
                                <td><?= $created ?></td>
                                <td><?= $phong_ban ?></td>
                                <td><?= $tieu_de ?></td>
                                <td><?= htmlspecialchars($loai_bds) ?></td>
                                <td><?= htmlspecialchars($loai_kho) ?></td>
                                <td><?= htmlspecialchars($phap_ly) ?></td>
                                <td><?= $ma_so_so ?></td>
                                <td><?= $dien_tich !== null ? rtrim(rtrim(number_format($dien_tich, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $don_vi ?></td>
                                <td><?= $chieu_dai !== null ? rtrim(rtrim(number_format($chieu_dai, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $chieu_rong !== null ? rtrim(rtrim(number_format($chieu_rong, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $so_tang !== null ? (int)$so_tang : '' ?></td>
                                <td style="text-align:right; padding-right:15px;"><?= htmlspecialchars($gia_chao_fmt) ?></td>
                                <?php $statusKey = htmlspecialchars($p['trang_thai'] ?? ''); ?>
                                <td><span class="status-badge strong <?= $statusKey ? 'status-badge--' . $statusKey : '' ?>"><?= htmlspecialchars($status) ?></span></td>
                                <td style="text-align:right; padding-right:15px;"><?= $address ?></td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>

                </tbody>
            </table>
        </div>
        <div class="pagination-container">
        </div>
        <div id="filter-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Bộ lọc tìm kiếm</h3>

                <div class="filter-group">
                    <label class="filter-label">Hiện trạng</label>
                    <select id="filter-status" class="filter-select">
                        <option value="all" <?= (empty($status) || $status === 'all') ? 'selected' : '' ?>>Tất cả</option>
                        <option value="ban_manh" <?= (isset($status) && $status === 'ban_manh') ? 'selected' : '' ?>>Bán mạnh</option>
                        <option value="tam_dung_ban" <?= (isset($status) && $status === 'tam_dung_ban') ? 'selected' : '' ?>>Tạm dừng bán</option>
                        <option value="dung_ban" <?= (isset($status) && $status === 'dung_ban') ? 'selected' : '' ?>>Dừng bán</option>
                        <option value="da_ban" <?= (isset($status) && $status === 'da_ban') ? 'selected' : '' ?>>Đã bán</option>
                        <option value="tang_chao" <?= (isset($status) && $status === 'tang_chao') ? 'selected' : '' ?>>Tăng chào</option>
                        <option value="ha_chao" <?= (isset($status) && $status === 'ha_chao') ? 'selected' : '' ?>>Hạ chào</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Mã tin / Địa chỉ</label>
                    <input type="text" id="filter-address" class="filter-input" placeholder="Nhập mã tin (VD: 1277)..." value="<?= htmlspecialchars($address ?? '') ?>">
                </div>
                <div class="modal-actions">
                    <button id="close-filter" class="btn-cancel">Hủy</button>
                    <button id="apply-filter" class="btn-apply">Áp dụng</button>
                </div>
            </div>
        </div>
        <div id="search-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm</h3>

                <div class="filter-group">
                    <input type="text" id="search-input" class="filter-input"
                        placeholder="Nhập từ khóa (Mã tin, địa chỉ, ghi chú)...">
                </div>

                <div class="modal-actions">
                    <button id="close-search" class="btn-cancel">Hủy</button>
                    <button id="apply-search" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>
        <div id="save-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Lưu vào bộ sưu tập</h3>

                <div class="filter-group">
                    <div class="collection-list-select" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px;">
                        <?php if (!empty($collections)) : ?>
                            <?php foreach ($collections as $c) : ?>
                                <label class="collection-option" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                                    <input type="checkbox" name="collection[]" value="<?= (int)$c['id'] ?>" style="margin-right: 10px;">
                                    <span style="font-size: 14px; color: #000;"><?= htmlspecialchars($c['ten_bo_suu_tap'] ?? '') ?><?php if (isset($c['item_count'])) echo ' (' . (int)$c['item_count'] . ')'; ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div style="padding:10px; color:#666">Chưa có bộ sưu tập nào.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-actions">
                    <button id="close-save-collection" class="btn-cancel">Hủy</button>
                    <button id="confirm-save-collection" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>
        <div id="status-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Cập nhật trạng thái</h3>

                <div class="filter-group">
                    <label class="filter-label">Chọn trạng thái mới</label>
                    <select id="edit-status-select" class="filter-select">
                        <option value="Bán mạnh">Bán mạnh</option>
                        <option value="Tạm dừng bán">Tạm dừng bán</option>
                        <option value="Dừng bán">Dừng bán</option>
                        <option value="Đã bán">Đã bán</option>
                        <option value="Tăng chào">Tăng chào</option>
                        <option value="Hạ chào">Hạ chào</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button id="close-status-modal" class="btn-cancel">Hủy</button>
                    <button id="save-status-btn" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

</body>

</html>