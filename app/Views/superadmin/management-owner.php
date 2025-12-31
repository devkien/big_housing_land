<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quản lý nhân sự</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Quản lý nhân sự</div>
            <div style="width: 20px;"></div>
        </header>

        <div class="hr-tab-container">
            <button class="hr-tab-btn active">Quản lý đầu chủ</button>
            <button class="hr-tab-btn" onclick="window.location.href='<?= BASE_URL ?>/superadmin/management-guest'">Quản lý đầu khách</button>
        </div>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <div class="hr-toolbar">
            <div style="display: flex; gap: 10px;">
                <button id="btn-hr-filter" class="btn-tool-outline"><i class="fa-solid fa-filter"></i> Lọc</button>
                <button id="btn-hr-search" class="btn-tool-outline"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </div>
            <a href="<?= BASE_URL ?>/superadmin/add-personnel" class="btn-add-blue" style="text-decoration: none;">
                <i class="fa-solid fa-user-plus"></i> Thêm nhân sự
            </a>
        </div>

        <div class="table-wrapper" style="margin-bottom: 0; overflow-x: auto;">
            <table class="hr-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="padding-left:10px; width: 40px;">XOÁ</th>
                        <th>MÃ NS</th>
                        <th>TRẠNG THÁI</th>
                        <th>HỌ TÊN</th>
                        <th>SĐT</th>
                        <th>Vị Trí</th>
                        <th>ĐỊA CHỈ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rows = $users ?? [];
                    if (count($rows) === 0) : ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">Không có dữ liệu</td>
                        </tr>
                        <?php else:
                        foreach ($rows as $u):
                            $st = (int)($u['trang_thai'] ?? 0);
                            if ($st === 1) {
                                $statusClass = 'status-active';
                                $statusText = 'Hoạt động';
                            } elseif ($st === 2) {
                                $statusClass = 'status-pause';
                                $statusText = 'Tạm dừng';
                            } else {
                                $statusClass = 'status-pause';
                                $statusText = 'Chờ duyệt';
                            }

                            $viTriVal = isset($u['vi_tri']) && $u['vi_tri'] !== '' ? (int)$u['vi_tri'] : null;
                            $viTriText = '';
                            if ($viTriVal === 0) $viTriText = 'Kho nhà đất';
                            elseif ($viTriVal === 1) $viTriText = 'Kho nhà cho thuê';
                            elseif ($viTriVal === 2) $viTriText = 'Kho nhà đất và cho thuê';
                        ?>
                            <tr>
                                <td style="padding-left:10px; text-align: center;">
                                    <form method="POST" action="<?= BASE_URL ?>/superadmin/management-delete" style="display:inline;">
                                        <?php require_once __DIR__ . '/../../Helpers/functions.php';
                                        echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                                        <button type="button" class="btn-delete-user" style="background:transparent;border:none;padding:0;cursor:pointer;">
                                            <i class="fa-regular fa-trash-can icon-trash-red"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a class="text-id-blue" href="<?= BASE_URL ?>/superadmin/update-personnel?id=<?= (int)($u['id'] ?? 0) ?>">
                                        <?= htmlspecialchars($u['ma_nhan_su'] ?? '') ?>
                                    </a>
                                </td>
                                <td class="<?= $statusClass ?>"><?= $statusText ?></td>
                                <td><?= htmlspecialchars($u['ho_ten'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['so_dien_thoai'] ?? '') ?></td>
                                <td><?= htmlspecialchars($viTriText) ?></td>
                                <td><?= htmlspecialchars($u['dia_chi'] ?? '') ?></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-container" style="text-align:center; margin-top:18px;">
            <?php 
            if (isset($pages) && $pages > 1): 
                $qs = [];
                if (!empty($search)) $qs['q'] = $search;
                if (isset($status) && $status !== null && $status !== '') $qs['status'] = $status;
                $qsStr = !empty($qs) ? '&' . http_build_query($qs) : '';
            ?>
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-owner?page=<?= $page - 1 ?><?= $qsStr ?>">&lt;</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <?php if ($p == $page): ?>
                        <span class="page-link active"><?= $p ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-owner?page=<?= $p ?><?= $qsStr ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $pages): ?>
                    <a class="page-link" href="<?= BASE_URL ?>/superadmin/management-owner?page=<?= $page + 1 ?><?= $qsStr ?>">&gt;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Shared modals (delete confirmation, success notification) -->
        <?php require_once __DIR__ . '/../partials/modals.php'; ?>

        <!-- Filter Modal -->
        <div id="filter-modal-local" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Bộ lọc</h3>
                <div class="filter-group">
                    <label class="filter-label">Trạng thái</label>
                    <select id="filter-status" class="filter-select">
                        <option value="all">Tất cả</option>
                        <option value="1">Hoạt động</option>
                        <option value="2">Tạm dừng</option>
                        <option value="0">Chờ duyệt</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button id="close-filter-local" class="btn-cancel">Hủy</button>
                    <button id="apply-filter-local" class="btn-apply">Áp dụng</button>
                </div>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterModal = document.getElementById('filter-modal-local');
            const btnFilter = document.getElementById('btn-hr-filter');
            const btnCloseFilter = document.getElementById('close-filter-local');
            const btnApplyFilter = document.getElementById('apply-filter-local');

            if (btnFilter) {
                btnFilter.addEventListener('click', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentStatus = urlParams.get('status');
                    const statusSelect = document.getElementById('filter-status');
                    if (statusSelect) {
                        statusSelect.value = (currentStatus !== null && currentStatus !== '') ? currentStatus : 'all';
                    }
                    if (filterModal) filterModal.style.display = 'flex';
                });
            }

            if (btnCloseFilter) {
                btnCloseFilter.addEventListener('click', () => { if (filterModal) filterModal.style.display = 'none'; });
            }

            if (btnApplyFilter) {
                btnApplyFilter.addEventListener('click', function() {
                    const statusValue = document.getElementById('filter-status').value;
                    const statusMap = {
                        '1': 'Hoạt động',
                        '2': 'Tạm dừng',
                        '0': 'Chờ duyệt'
                    };
                    const filterText = statusMap[statusValue];
                    const tableRows = document.querySelectorAll('.hr-table tbody tr');

                    tableRows.forEach(row => {
                        const cells = row.getElementsByTagName('td');
                        if (cells.length < 3) { // Bỏ qua các dòng không phải dữ liệu (ví dụ: "Không có dữ liệu")
                            row.style.display = '';
                            return;
                        }

                        const statusCell = cells[2]; // Cột thứ 3 là "TRẠNG THÁI"
                        const cellText = statusCell.textContent.trim();

                        if (statusValue === 'all' || cellText === filterText) {
                            row.style.display = ''; // Hiện dòng
                        } else {
                            row.style.display = 'none'; // Ẩn dòng
                        }
                    });

                    if (filterModal) {
                        filterModal.style.display = 'none';
                    }
                });
            }

            // Search Modal Logic
            const searchModal = document.getElementById('hr-search-modal');
            const btnSearch = document.getElementById('btn-hr-search');
            const btnCloseSearch = document.getElementById('close-hr-search');
            const btnApplySearch = document.getElementById('apply-hr-search');
            const searchInput = document.getElementById('hr-search-input');

            if (btnSearch) {
                btnSearch.addEventListener('click', function() {
                    if (searchModal) searchModal.style.display = 'flex';
                    if (searchInput) {
                        const urlParams = new URLSearchParams(window.location.search);
                        searchInput.value = urlParams.get('q') || '';
                        searchInput.focus();
                    }
                });
            }

            if (btnCloseSearch) {
                btnCloseSearch.addEventListener('click', () => { if (searchModal) searchModal.style.display = 'none'; });
            }

            if (btnApplySearch) {
                btnApplySearch.addEventListener('click', function() {
                    const keyword = searchInput.value.trim();
                    const url = new URL(window.location.href);
                    if (keyword) {
                        url.searchParams.set('q', keyword);
                    } else {
                        url.searchParams.delete('q');
                    }
                    url.searchParams.set('page', '1'); // Reset to first page on new search
                    window.location.href = url.toString();
                });
            }

            // --- Delete User Logic ---
            const deleteModal = document.getElementById('delete-modal');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
            let userToDeleteId = null;
            let rowToDelete = null;

            document.querySelectorAll('.btn-delete-user').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    userToDeleteId = form.querySelector('input[name="id"]').value;
                    rowToDelete = this.closest('tr');
                    
                    const modalMessage = document.getElementById('delete-modal-message');
                    if (modalMessage) modalMessage.textContent = 'Bạn có chắc chắn muốn xóa nhân sự này không?';
                    if (deleteModal) deleteModal.style.display = 'flex';
                });
            });

            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', () => {
                    if (deleteModal) deleteModal.style.display = 'none';
                });
            }

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (!userToDeleteId || !rowToDelete) return;

                    const form = rowToDelete.querySelector('form');
                    const csrfToken = form.querySelector('input[name="_csrf"]').value;
                    this.disabled = true;

                    fetch('<?= BASE_URL ?>/superadmin/management-delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ id: userToDeleteId, _csrf: csrfToken })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.disabled = false;
                        if (deleteModal) deleteModal.style.display = 'none';
                        if (data.ok) {
                            rowToDelete.remove();
                        } else {
                            alert(data.message || 'Có lỗi xảy ra, không thể xóa.');
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        if (deleteModal) deleteModal.style.display = 'none';
                        alert('Lỗi kết nối, vui lòng thử lại.');
                        console.error('Delete Error:', error);
                    });
                });
            }
        });
    </script>
</body>

</html>