<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chi tiết bộ sưu tập</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="resource-header">
            <a href="<?= BASE_URL ?>/admin/collection" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="resource-title"><?php echo htmlspecialchars($collection['ten_bo_suu_tap'] ?? 'Chi tiết bộ sưu tập'); ?> <?php echo ' (' . (int)(count($items ?? [])) . ' tin)'; ?></div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="toolbar-section">
            <button class="tool-btn" id="btn-filter"><i class="fa-solid fa-filter"></i> Lọc</button>
            <button class="tool-btn" id="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            <div style="flex:1;"></div>
        </div>
        <div class="table-wrapper" style="margin-bottom: 0;">
            <table class="resource-table" style="min-width:1400px;">
                <thead>
                    <tr>
                        <th style="padding-left:15px; width: 60px;">LƯU</th>
                        <!-- <th style="width: 60px;">GHI CHÚ</th> -->
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

                    if (empty($items)) :
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px;">Không có tài nguyên nào trong bộ sưu tập này.</td>
                        </tr>
                        <?php else :
                        foreach ($items as $p) :
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
                            // friendly labels / formatting (same as resource.php)
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
                            $statusKey = htmlspecialchars($p['trang_thai'] ?? '');
                            $ci_id = isset($p['ci_id']) ? (int)$p['ci_id'] : 0;
                            ?>
                            <tr data-id="<?= htmlspecialchars($p['id']) ?>">
                                <td style="padding-left:15px;">
                                    <?php $rtype = htmlspecialchars($p['resource_type'] ?? 'bat_dong_san'); ?>
                                    <i class="fa-solid fa-bookmark icon-save saved" data-ci="<?= $ci_id ?>" data-resource-id="<?= (int)$p['id'] ?>" data-resource-type="<?= $rtype ?>" title="Bỏ khỏi bộ sưu tập" style="color:#ffcc00; cursor:pointer;"></i>
                                </td>
                                <!-- <td><i class="fa-regular fa-note-sticky icon-note"></i></td> -->
                                <td style="cursor:pointer; color:#0b66ff;" onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource-detail?id=<?= htmlspecialchars($p['id']) ?>'"><?= $code ?></td>
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
            <!-- Phân trang sẽ được tạo bởi JavaScript -->
        </div>

        <!-- Modal Lọc -->
        <div id="filter-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Bộ lọc tìm kiếm</h3>

                <div class="filter-group">
                    <label class="filter-label">Hiện trạng</label>
                    <select id="filter-status" class="filter-select">
                        <option value="all">Tất cả</option>
                        <option value="ban_manh">Bán mạnh</option>
                        <option value="tam_dung_ban">Tạm dừng bán</option>
                        <option value="dung_ban">Dừng bán</option>
                        <option value="da_ban">Đã bán</option>
                        <option value="tang_chao">Tăng chào</option>
                        <option value="ha_chao">Hạ chào</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Mã tin / Địa chỉ</label>
                    <input type="text" id="filter-address" class="filter-input" placeholder="Nhập mã tin (VD: 1277)...">
                </div>
                <div class="modal-actions">
                    <button id="close-filter" class="btn-cancel">Hủy</button>
                    <button id="apply-filter" class="btn-apply">Áp dụng</button>
                </div>
            </div>
        </div>
        <!-- Modal Tìm kiếm -->
        <div id="search-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm</h3>
                <div class="filter-group">
                    <input type="text" id="search-input" class="filter-input" placeholder="Nhập từ khóa (Mã tin, địa chỉ, ghi chú)...">
                </div>
                <div class="modal-actions">
                    <button id="close-search" class="btn-cancel">Hủy</button>
                    <button id="apply-search" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>
        <!-- Modal Lưu vào bộ sưu tập -->
        <div id="save-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Lưu vào bộ sưu tập</h3>

                <div class="filter-group">
                    <div class="collection-list-select" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px;">
                        <label class="collection-option" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                            <input type="checkbox" name="collection" value="1" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Khách hàng tiềm năng</span>
                        </label>
                        <label class="collection-option" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                            <input type="checkbox" name="collection" value="2" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Nhà đất Hà Đông</span>
                        </label>
                        <label class="collection-option" style="display: flex; align-items: center; padding: 10px; cursor: pointer;">
                            <input type="checkbox" name="collection" value="3" style="margin-right: 10px;">
                            <span style="font-size: 14px; color: #000;">Dự án mới</span>
                        </label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button id="close-save-collection" class="btn-cancel">Hủy</button>
                    <button id="confirm-save-collection" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>
        <!-- Modal Cập nhật trạng thái (Khi ấn icon ghi chú) -->
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

        <?php require_once __DIR__ . '/../partials/modals.php'; ?>

        <script>
            (function() {
                var currentRemove = null;
                // Open delete modal when clicking saved icon
                document.body.addEventListener('click', function(e) {
                    var btn = e.target.closest && e.target.closest('.icon-save.saved');
                    if (!btn) return;
                    e.stopPropagation();
                    var ci = btn.getAttribute('data-ci');
                    var rid = btn.getAttribute('data-resource-id');
                    var rtype = btn.getAttribute('data-resource-type') || 'bat_dong_san';
                    currentRemove = {
                        ci: ci,
                        resource_id: rid,
                        resource_type: rtype
                    };

                    // set modal message
                    var msg = document.getElementById('delete-modal-message');
                    if (msg) msg.innerText = 'Bạn có chắc chắn muốn xóa mục này khỏi bộ sưu tập?';

                    // show modal
                    var delModal = document.getElementById('delete-modal');
                    if (delModal) delModal.style.display = 'flex';
                });

                // Filter / Search modal open handlers
                document.getElementById('btn-filter')?.addEventListener('click', function() {
                    // prefill from query params if available
                    var params = new URLSearchParams(window.location.search);
                    var status = params.get('status') || 'all';
                    var address = params.get('address') || '';
                    document.getElementById('filter-status').value = status;
                    document.getElementById('filter-address').value = address;
                    document.getElementById('filter-modal').style.display = 'flex';
                });
                document.getElementById('btn-search')?.addEventListener('click', function() {
                    var params = new URLSearchParams(window.location.search);
                    var q = params.get('q') || '';
                    document.getElementById('search-input').value = q;
                    document.getElementById('search-modal').style.display = 'flex';
                });

                document.getElementById('close-filter')?.addEventListener('click', function() {
                    document.getElementById('filter-modal').style.display = 'none';
                });
                document.getElementById('close-search')?.addEventListener('click', function() {
                    document.getElementById('search-modal').style.display = 'none';
                });

                // Apply filter: build GET url and reload
                document.getElementById('apply-filter')?.addEventListener('click', function() {
                    var status = document.getElementById('filter-status').value || 'all';
                    var address = document.getElementById('filter-address').value || '';
                    var params = new URLSearchParams(window.location.search);
                    params.set('id', <?= (int)($collection['id'] ?? 0) ?>);
                    if (status && status !== 'all') params.set('status', status);
                    else params.delete('status');
                    if (address) params.set('address', address);
                    else params.delete('address');
                    params.delete('page');
                    window.location.href = window.location.pathname + '?' + params.toString();
                });

                // Apply search
                document.getElementById('apply-search')?.addEventListener('click', function() {
                    var q = document.getElementById('search-input').value || '';
                    var params = new URLSearchParams(window.location.search);
                    params.set('id', <?= (int)($collection['id'] ?? 0) ?>);
                    if (q) params.set('q', q);
                    else params.delete('q');
                    params.delete('page');
                    window.location.href = window.location.pathname + '?' + params.toString();
                });

                // Cancel
                document.getElementById('cancel-delete-btn')?.addEventListener('click', function() {
                    document.getElementById('delete-modal').style.display = 'none';
                    currentRemove = null;
                });

                // Confirm delete
                document.getElementById('confirm-delete-btn')?.addEventListener('click', function() {
                    if (!currentRemove) return;
                    var btn = this;
                    btn.disabled = true;
                    var fd = new FormData();
                    fd.append('collection_id', <?= (int)($collection['id'] ?? 0) ?>);
                    fd.append('resource_id', currentRemove.resource_id);
                    fd.append('resource_type', currentRemove.resource_type);

                    fetch('<?= BASE_URL ?>/admin/collection-remove-item', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    }).then(r => r.json()).then(function(json) {
                        btn.disabled = false;
                        document.getElementById('delete-modal').style.display = 'none';
                        if (json && json.ok) {
                            // remove table row containing the icon
                            var icon = document.querySelector('.icon-save.saved[data-resource-id="' + currentRemove.resource_id + '"]');
                            if (icon) {
                                var row = icon.closest('tr');
                                if (row) row.remove();
                            }
                            // show success modal
                            var sTitle = document.getElementById('success-modal-title');
                            var sMsg = document.getElementById('success-modal-message');
                            if (sTitle) sTitle.innerText = 'Đã xóa';
                            if (sMsg) sMsg.innerText = 'Mục đã được xóa khỏi bộ sưu tập.';
                            document.getElementById('success-modal').style.display = 'flex';
                        } else {
                            alert('Không thể xóa, vui lòng thử lại.');
                        }
                        currentRemove = null;
                    }).catch(function() {
                        btn.disabled = false;
                        document.getElementById('delete-modal').style.display = 'none';
                        alert('Lỗi mạng');
                        currentRemove = null;
                    });
                });

                // Close success
                document.getElementById('success-ok-btn')?.addEventListener('click', function() {
                    document.getElementById('success-modal').style.display = 'none';
                });
            })();
        </script>
    </div>
    </div>
</body>

</html>