<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quản lý tin nội bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: #f0f4f8;">

        <div class="header-blue-solid">
            <a href="<?= BASE_URL ?>/admin/info" class="back-btn-white"><i class="fa-solid fa-chevron-left"></i></a>
            Quản lý tin
        </div>

        <div style="background: white; min-height: calc(100vh - 130px);">
            <div style="padding: 15px 15px 0 15px; display: flex; gap: 10px;">
                <button class="btn-tool-outline" id="btn-filter-internal"><i class="fa-solid fa-filter"></i> Lọc</button>
                <button class="btn-tool-outline" id="btn-search-internal"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
            </div>

            <div style="overflow-x: auto;">
                <input type="hidden" id="csrf-token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <table class="hr-table table-manage-info" style="width: 100%; min-width: 350px;">
                    <thead>
                        <tr>
                            <th style="padding-left: 15px; width: 40px;">XOÁ</th>
                            <th style="width: 50px; text-align: center; color: #000;">GHIM</th>
                            <th style="color: #000;">MÃ tin nội Bộ</th>
                            <th style="color: #000;">Tiêu đề</th>
                            <th style="color: #000;">Thời Gian</th>
                            <th style="padding-right: 15px; text-align: right; color: #000;">Sửa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($posts) && is_array($posts)): ?>
                            <?php foreach ($posts as $p): ?>
                                <?php
                                $isPinned = !empty($p['is_pinned']) && $p['is_pinned'] == 1;
                                $pinColor = $isPinned ? '#0033cc' : '#ccc';
                                $pinTransform = $isPinned ? 'rotate(45deg)' : 'rotate(0deg)';
                                ?>
                                <tr data-id="<?= (int)$p['id'] ?>">
                                    <td style="padding-left: 15px; text-align: center;">
                                        <button class="btn-icon delete-btn" data-id="<?= (int)$p['id'] ?>" style="background:none;border:none;padding:0;cursor:pointer;">
                                            <i class="fa-regular fa-trash-can icon-trash-red"></i>
                                        </button>
                                    </td>
                                    <td style="text-align: center;">
                                        <i class="fa-solid fa-thumbtack pin-icon" data-id="<?= (int)$p['id'] ?>" data-pinned="<?= $isPinned ? '1' : '0' ?>" style="color: <?= $pinColor ?>; transform: <?= $pinTransform ?>; cursor: pointer; transition: all 0.2s;"></i>
                                    </td>
                                    <td><span class="text-id-blue"><?= htmlspecialchars($p['ma_hien_thi'] ?? '') ?></span></td>
                                    <td class="title-cell"><?= htmlspecialchars(mb_strimwidth(strip_tags($p['tieu_de'] ?? ''), 0, 80, '...')) ?></td>
                                    <td><?= !empty($p['created_at']) ? date('m/d/Y', strtotime($p['created_at'])) : '' ?></td>
                                    <td style="padding-right: 15px; text-align: right;">
                                        <a href="<?= BASE_URL ?>/admin/internal-info-edit?id=<?= (int)$p['id'] ?>"><i class="fa-solid fa-pen icon-edit-black"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px;">Không có thông tin nội bộ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container" style="background: white; padding-top: 20px;">
                <button class="page-btn nav-arrow-blue"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="page-btn active" style="background: #e6f0ff; border-color: #0044cc; color: #0044cc;">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn" style="border:none;">...</button>
                <button class="page-btn">9</button>
                <button class="page-btn">10</button>
                <button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
        <!-- Modal Xác nhận xóa -->
        <div id="delete-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Xác nhận xóa</h3>
                <p style="text-align: center; margin-bottom: 20px; font-size: 13px;">Bạn có chắc chắn muốn xóa thông tin này không?</p>
                <div class="modal-actions" style="justify-content: center;">
                    <button id="confirm-delete-btn" class="btn-save" style="background-color: #ff3333; margin: 0; width: auto; padding: 10px 30px;">Xóa</button>
                    <button id="cancel-delete-btn" class="btn-cancel">Hủy</button>
                </div>
            </div>
        </div>

        <!-- Modal Lọc -->
        <div id="filter-modal-internal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Bộ lọc</h3>
                <div class="filter-group">
                    <label class="filter-label">Thời gian</label>
                    <input type="date" id="filter-date-internal" class="filter-input">
                </div>
                <div class="modal-actions">
                    <button id="close-filter-internal" class="btn-cancel">Hủy</button>
                    <button id="apply-filter-internal" class="btn-apply">Áp dụng</button>
                </div>
            </div>
        </div>

        <!-- Modal Tìm kiếm -->
        <div id="search-modal-internal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Tìm kiếm</h3>
                <div class="filter-group">
                    <input type="text" id="search-input-internal" class="filter-input" placeholder="Nhập tiêu đề, mã tin...">
                </div>
                <div class="modal-actions">
                    <button id="close-search-internal" class="btn-cancel">Hủy</button>
                    <button id="apply-search-internal" class="btn-apply">Tìm kiếm</button>
                </div>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>


    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý Modal Lọc
            const filterModal = document.getElementById('filter-modal-internal');
            const btnFilter = document.getElementById('btn-filter-internal');
            const btnCloseFilter = document.getElementById('close-filter-internal');
            const btnApplyFilter = document.getElementById('apply-filter-internal');

            if (btnFilter) btnFilter.onclick = () => filterModal.style.display = 'flex';
            if (btnCloseFilter) btnCloseFilter.onclick = () => filterModal.style.display = 'none';
            if (btnApplyFilter) btnApplyFilter.onclick = () => {
                // Logic lọc theo ngày (nếu cần)
                filterModal.style.display = 'none';
            };

            // Xử lý Modal Tìm kiếm
            const searchModal = document.getElementById('search-modal-internal');
            const btnSearch = document.getElementById('btn-search-internal');
            const btnCloseSearch = document.getElementById('close-search-internal');
            const btnApplySearch = document.getElementById('apply-search-internal');
            const searchInput = document.getElementById('search-input-internal');

            if (btnSearch) btnSearch.onclick = () => {
                searchModal.style.display = 'flex';
                if (searchInput) searchInput.focus();
            };
            if (btnCloseSearch) btnCloseSearch.onclick = () => searchModal.style.display = 'none';
            if (btnApplySearch) {
                btnApplySearch.onclick = () => {
                    const keyword = searchInput.value.trim();
                    window.location.href = `<?= BASE_URL ?>/admin/internal-info-list?q=${encodeURIComponent(keyword)}`;
                };
            }

            // Xử lý sự kiện click vào icon ghim
            const pinIcons = document.querySelectorAll('.pin-icon');
            pinIcons.forEach(function(icon) {
                icon.addEventListener('click', function() {
                    const postId = this.getAttribute('data-id');
                    const isCurrentlyPinned = this.getAttribute('data-pinned') === '1';
                    const newPinState = isCurrentlyPinned ? 0 : 1;
                    const csrf = document.getElementById('csrf-token') ? document.getElementById('csrf-token').value : '';

                    this.style.pointerEvents = 'none';
                    const originalColor = this.style.color;
                    this.style.color = '#aaa';

                    fetch('<?= BASE_URL ?>/superadmin/internal-info-pin', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                id: postId,
                                pinned: newPinState,
                                _csrf: csrf
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.ok) {
                                this.setAttribute('data-pinned', newPinState);
                                this.style.color = newPinState ? '#0033cc' : '#ccc';
                                this.style.transform = newPinState ? 'rotate(45deg)' : 'rotate(0deg)';
                            } else {
                                this.style.color = originalColor;
                                alert('Lỗi: ' + (data.message || 'Không thể ghim bài viết.'));
                            }
                        })
                        .catch(err => {
                            this.style.color = originalColor;
                            alert('Đã xảy ra lỗi kết nối.');
                        })
                        .finally(() => {
                            this.style.pointerEvents = 'auto';
                        });
                });
            });

            // Xử lý xóa bài
            let selectedDeleteId = null;
            const deleteModal = document.getElementById('delete-modal');
            const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
            const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectedDeleteId = this.getAttribute('data-id');
                    if (deleteModal) deleteModal.style.display = 'flex';
                });
            });

            if (cancelDeleteBtn) cancelDeleteBtn.onclick = () => {
                selectedDeleteId = null;
                deleteModal.style.display = 'none';
            };

            if (confirmDeleteBtn) confirmDeleteBtn.onclick = function() {
                if (!selectedDeleteId) return;
                const csrf = document.getElementById('csrf-token') ? document.getElementById('csrf-token').value : '';
                fetch('<?= BASE_URL ?>/admin/internal-info-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: selectedDeleteId,
                        _csrf: csrf
                    })
                }).then(r => r.json()).then(data => {
                    if (data && data.ok) {
                        // remove row from table
                        const row = document.querySelector('tr[data-id="' + selectedDeleteId + '"]');
                        if (row) row.remove();
                    } else {
                        alert('Xóa không thành công');
                    }
                }).catch(err => {
                    alert('Lỗi server khi xóa');
                }).finally(() => {
                    selectedDeleteId = null;
                    if (deleteModal) deleteModal.style.display = 'none';
                });
            };
        });
    </script>
</body>

</html>