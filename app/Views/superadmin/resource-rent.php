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
        window.BASE_URL = '<?= BASE_URL ?>';
        window.BASE_PATH = window.BASE_URL;
    </script>
    <script>
        // Mock CKEditor
        window.ClassicEditor = {
            create: function() {
                return new Promise(() => {});
            }
        };
    </script>
    <script src="<?= BASE_URL ?>/js/script.js"></script>
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
            <button class="tab-btn active" >Kho nhà cho thuê</button>
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
                        <th style="width: 100px; text-align: center;">HÀNH ĐỘNG</th>
                        <th style="width: 120px; text-align: center;">SỬA</th>
                        <th style="width: 100px; text-align: center;">XÉT DUYỆT</th>
                        <th style="width: 100px;">THỜI GIAN</th>
                        <th style="width: 240px;">TIÊU ĐỀ</th>
                        <th style="width:120px;">HIỆN TRẠNG</th>
                        <th style="text-align:right; padding-right:15px;">ĐỊA CHỈ</th>
                        <th style="width: 80px;">CÓ SỔ</th>
                        <th style="width: 120px;">MÃ SỔ</th>
                        <th style="width:100px;">DIỆN TÍCH</th>
                        <th style="width:80px">ĐV</th>
                        <th style="width:90px">CHIỀU DÀI</th>
                        <th style="width:90px">CHIỀU RỘNG</th>
                        <th style="width:80px">SỐ TẦNG</th>
                        <th style="width:140px; text-align:right; padding-right:15px;">GIÁ CHÀO</th>
                        <th style="width: 100px;">LOẠI BĐS</th>
                        <th style="width: 100px;">LOẠI KHO</th>
                        <th style="width: 120px;">MÃ HIỂN THỊ</th>
                        <th style="width: 100px;">PHÒNG BAN</th>
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

                    $approvalMap = [
                        'cho_duyet' => ['label' => 'Chờ duyệt', 'color' => '#f0ad4e'],
                        'da_duyet'  => ['label' => 'Đã duyệt',  'color' => '#28a745'],
                        'tu_choi'   => ['label' => 'Từ chối',   'color' => '#dc3545']
                    ];

                    // Lấy giá trị lọc từ URL
                    $filterApproval = $_GET['approval'] ?? '';

                    if (empty($properties)) :
                    ?>
                        <tr>
                            <td colspan="20" style="text-align:center; padding:20px;">Không tìm thấy tài nguyên nào.</td>
                        </tr>
                        <?php else :
                        foreach ($properties as $p) :
                    
                            
                            $code = htmlspecialchars($p['ma_hien_thi'] ?? '');
                            $created = !empty($p['created_at']) ? date('d/m/Y', strtotime($p['created_at'])) : '';
                            
                            $statusKey = $p['trang_thai'] ?? '';
                            $status = $statusMap[$statusKey] ?? ($statusKey ?: '');
                            
                            $address = trim($p['dia_chi_chi_tiet'] ?? '');
                            if ($address === '') {
                                $parts = array_filter([$p['tinh_thanh'] ?? '', $p['quan_huyen'] ?? '', $p['xa_phuong'] ?? '']);
                                $address = htmlspecialchars(implode(', ', $parts));
                            } else {
                                $address = htmlspecialchars($address);
                            }

                            $currentStatus = htmlspecialchars($p['trang_thai'] ?? 'ban_manh');
                            $currentApproval = htmlspecialchars($p['tinh_trang_duyet'] ?? 'cho_duyet');
                            $appInfo = $approvalMap[$currentApproval] ?? $approvalMap['cho_duyet'];
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

                                <td style="text-align: center; cursor: pointer;" 
                                    onclick="openQuickEditModal(<?= $p['id'] ?>, '<?= $currentStatus ?>', '<?= $currentApproval ?>')"
                                    title="Cập nhật trạng thái">
                                    <i class="fa-regular fa-pen-to-square icon-note" style="color: #e65100; font-size: 16px;"></i>
                                </td>

                                <td>
                                    <div style="display: flex; justify-content: center; align-items: center; gap: 25px;">
                                        <a href="<?= BASE_URL ?>/superadmin/management-resource-edit?id=<?= $p['id'] ?>" title="Sửa nội dung" style="color: #0044cc;">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/superadmin/management-resource-delete" method="POST" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài nguyên này không?');">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" style="border:none; background:none; color: #d32f2f; cursor: pointer; padding:0;" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                <td style="text-align: center;">
                                    <span style="background-color: <?= $appInfo['color'] ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; white-space: nowrap;">
                                        <?= $appInfo['label'] ?>
                                    </span>
                                </td>

                                <td><?= $created ?></td>

                                <td style="cursor:pointer; color:#0b66ff; font-weight:bold;" onclick="window.location.href='<?= BASE_URL ?>/superadmin/management-resource-detail?id=<?= htmlspecialchars($p['id']) ?>'">
                                    <?= $tieu_de ?>
                                </td>

                                <td><span class="status-badge strong <?= $statusKey ? 'status-badge--' . $statusKey : '' ?>"><?= htmlspecialchars($status) ?></span></td>
                                <td style="text-align:right; padding-right:15px; font-weight: 500; color: #333;"><?= $address ?></td>

                                <td><?= htmlspecialchars($phap_ly) ?></td>
                                <td><?= $ma_so_so ?></td>
                                <td><?= $dien_tich !== null ? rtrim(rtrim(number_format($dien_tich, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $don_vi ?></td>
                                <td><?= $chieu_dai !== null ? rtrim(rtrim(number_format($chieu_dai, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $chieu_rong !== null ? rtrim(rtrim(number_format($chieu_rong, 2, ',', '.'), '0'), ',') : '' ?></td>
                                <td><?= $so_tang !== null ? (int)$so_tang : '' ?></td>
                                <td style="text-align:right; padding-right:15px; color: #d32f2f; font-weight: bold;"><?= htmlspecialchars($gia_chao_fmt) ?></td>

                                <td><?= htmlspecialchars($loai_bds) ?></td>
                                <td><?= htmlspecialchars($loai_kho) ?></td>

                                <td style="color: #666;"><?= $code ?></td>
                                <td style="color: #666;"><?= $phong_ban ?></td>
                            </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            <?php
            $queryParams = [];
            if (!empty($status)) $queryParams['status'] = $status;
            if (!empty($address)) $queryParams['address'] = $address;
            
            // --- THÊM DÒNG NÀY ĐỂ GIỮ LẠI THAM SỐ APPROVAL TRÊN URL KHI CHUYỂN TRANG ---
            if (!empty($filterApproval)) $queryParams['approval'] = $filterApproval;
            
            $queryString = http_build_query($queryParams);
            ?>

            <?php if ($page > 1): ?>
                <a href="<?= BASE_URL ?>/superadmin/management-resource-rent?page=<?= $page - 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>
            <?php endif; ?>

            <a href="#" class="page-link active"><?= $page ?> / <?= $pages > 0 ? $pages : 1 ?></a>

            <?php if ($page < $pages): ?>
                <a href="<?= BASE_URL ?>/superadmin/management-resource-rent?page=<?= $page + 1 ?>&<?= $queryString ?>" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>
            <?php endif; ?>
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
                    <label class="filter-label">Trạng thái xét duyệt</label>
                    <select id="filter-approval" class="filter-select">
                        <option value="all" <?= (empty($filterApproval) || $filterApproval === 'all') ? 'selected' : '' ?>>Tất cả</option>
                        <option value="da_duyet" <?= ($filterApproval === 'da_duyet') ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="cho_duyet" <?= ($filterApproval === 'cho_duyet') ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="tu_choi" <?= ($filterApproval === 'tu_choi') ? 'selected' : '' ?>>Từ chối</option>
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

        <div id="save-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Lưu vào bộ sưu tập</h3>
                <div class="filter-group">
                    <div class="collection-list-select" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px;">
                        <?php if (!empty($collections)): ?>
                            <?php foreach ($collections as $c): ?>
                                <label class="collection-option" style="display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                                    <input type="checkbox" name="collection[]" value="<?= $c['id'] ?>" style="margin-right: 10px;">
                                    <span style="font-size: 14px; color: #000;"><?= htmlspecialchars($c['ten_bo_suu_tap']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 10px; text-align: center; color: #666;">Chưa có bộ sưu tập nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-actions">
                    <button id="close-save-collection" class="btn-cancel">Hủy</button>
                    <button id="confirm-save-collection" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>

        <div id="quick-edit-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Cập nhật trạng thái</h3>
                
                <form action="<?= BASE_URL ?>/superadmin/quick-update-status" method="POST">
                    <input type="hidden" name="id" id="quick-edit-id">
                    
                    <div class="filter-group">
                        <label class="filter-label">Trạng thái bán hàng</label>
                        <select name="trang_thai" id="quick-edit-status" class="filter-select">
                            <option value="ban_manh">Bán mạnh</option>
                            <option value="tam_dung_ban">Tạm dừng bán</option>
                            <option value="dung_ban">Dừng bán</option>
                            <option value="da_ban">Đã bán</option>
                            <option value="tang_chao">Tăng chào</option>
                            <option value="ha_chao">Hạ chào</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tình trạng xét duyệt</label>
                        <select name="tinh_trang_duyet" id="quick-edit-approval" class="filter-select">
                            <option value="cho_duyet">⏳ Chờ duyệt</option>
                            <option value="da_duyet">✅ Đã duyệt</option>
                            <option value="tu_choi">🚫 Từ chối</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="document.getElementById('quick-edit-modal').style.display='none'">Hủy</button>
                        <button type="submit" class="btn-apply">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        function openQuickEditModal(id, currentStatus, currentApproval) {
            document.getElementById('quick-edit-id').value = id;
            
            const statusSelect = document.getElementById('quick-edit-status');
            if (statusSelect) statusSelect.value = currentStatus;
            
            const approvalSelect = document.getElementById('quick-edit-approval');
            if (approvalSelect) approvalSelect.value = currentApproval || 'cho_duyet';
            
            document.getElementById('quick-edit-modal').style.display = 'block';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('quick-edit-modal');
            let filterModal = document.getElementById('filter-modal');
            let saveModal = document.getElementById('save-collection-modal');
            
            if (event.target == modal) modal.style.display = "none";
            if (event.target == filterModal) filterModal.style.display = "none";
            if (event.target == saveModal) saveModal.style.display = "none";
        }

        document.addEventListener('DOMContentLoaded', function() {
            function qs(sel) { return document.querySelector(sel); }
            function qsa(sel) { return document.querySelectorAll(sel); }

            var filterModal = qs('#filter-modal');
            var saveModal = qs('#save-collection-modal');
            window.currentPropertyId = null;

            qsa('.icon-save').forEach(function(el) {
                el.addEventListener('click', function(ev) {
                    ev.stopPropagation();
                    var tr = el.closest('tr');
                    window.currentPropertyId = tr ? tr.getAttribute('data-id') : null;
                    if (saveModal) {
                        saveModal.style.display = 'flex';
                        qsa('#save-collection-modal input[name="collection[]"]').forEach(function(cb) { cb.checked = false; });
                        fetch(window.BASE_URL + '/superadmin/get-property-collections?id=' + window.currentPropertyId)
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.success && data.collection_ids) {
                                    data.collection_ids.forEach(function(cid) {
                                        var cb = qs('#save-collection-modal input[name="collection[]"][value="' + cid + '"]');
                                        if (cb) cb.checked = true;
                                    });
                                }
                            })
                            .catch(function(e) { console.error(e); });
                    }
                });
            });

            var confirmSaveBtn = qs('#confirm-save-collection');
            if (confirmSaveBtn) {
                confirmSaveBtn.addEventListener('click', function(event) {
                    event.stopImmediatePropagation();
                    if (!window.currentPropertyId) return;
                    var selected = [];
                    qsa('#save-collection-modal input[name="collection[]"]:checked').forEach(function(cb) { selected.push(parseInt(cb.value)); });
                    var metaCsrf = qs('meta[name="csrf-token"]');
                    var csrfToken = metaCsrf ? metaCsrf.getAttribute('content') : '';
                    var payload = { property_id: parseInt(window.currentPropertyId), collections: selected, _csrf: csrfToken };
                    fetch(window.BASE_URL + '/superadmin/save-to-collections', {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload)
                        })
                        .then(function(res) { return res.text().then(function(text) { try { return JSON.parse(text); } catch (e) { throw new Error("Server Error"); } }); })
                        .then(function(json) {
                            if (json.ok || json.success) {
                                saveModal.style.display = 'none';
                                var tr = qs('tr[data-id="' + window.currentPropertyId + '"]');
                                if (tr) {
                                    var icon = tr.querySelector('.icon-save');
                                    if (icon) {
                                        if (selected.length > 0) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); icon.style.color = '#ffcc00'; } 
                                        else { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); icon.style.color = ''; }
                                    }
                                }
                            }
                        })
                        .catch(function(err) { console.error(err); });
                });
            }

            qsa('.btn-cancel, #close-filter, #close-save-collection').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (filterModal) filterModal.style.display = 'none';
                    if (saveModal) saveModal.style.display = 'none';
                });
            });

            window.addEventListener('click', function(e) {
                if (e.target == filterModal) filterModal.style.display = 'none';
                if (e.target == saveModal) saveModal.style.display = 'none';
            });

            var applyFilter = qs('#apply-filter');
            if (applyFilter) {
                applyFilter.addEventListener('click', function() {
                    var status = qs('#filter-status').value;
                    var address = qs('#filter-address').value;
                    var approval = qs('#filter-approval').value; // Lấy giá trị lọc xét duyệt

                    // Sử dụng window.location.href để lấy URL hiện tại
                    // Thay vì dùng đường dẫn cứng '/superadmin/management-resource-rent'
                    var url = new URL(window.location.href);
                    
                    url.searchParams.set('page', '1');
                    
                    if (status && status !== 'all') {
                        url.searchParams.set('status', status);
                    } else {
                        url.searchParams.delete('status');
                    }

                    if (address) {
                        url.searchParams.set('address', address);
                    } else {
                        url.searchParams.delete('address');
                    }

                    if (approval && approval !== 'all') {
                        url.searchParams.set('approval', approval);
                    } else {
                        url.searchParams.delete('approval');
                    }

                    window.location.href = url.toString();
                });
            }

            var btnFilter = qs('#btn-filter');
            if (btnFilter) btnFilter.addEventListener('click', function() {
                filterModal.style.display = 'flex';
            });
        });
    </script>
</body>
</html>