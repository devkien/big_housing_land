<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bộ sưu tập</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: #f9f9f9; display: flex; flex-direction: column; height: 100vh;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Bộ sưu tập</div>
            <div class="header-icon-btn"></div>
        </header>

        <div class="search-collection-box">
            <input type="text" id="search-collection-input" placeholder="Nhập thông tin tìm kiếm..." style="border: none; outline: none; background: transparent; flex: 1;">
            <button type="button" id="btn-search-collection" style="border: none; background: transparent; padding: 0; cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>

        <div style="flex: 1; overflow-y: auto;">
            <?php if (empty($collections)) : ?>
                <div id="no-result-message" style="text-align: center; padding: 20px; color: #666; font-size: 14px;">Không tìm thấy bộ sưu tập nào.</div>
            <?php else : ?>
                <?php foreach ($collections as $c) :
                    $name = htmlspecialchars($c['ten_bo_suu_tap'] ?? '');
                    $count = (int)($c['item_count'] ?? 0);
                    // thumbnail: normalize path to absolute URL using BASE_URL
                    $thumbHtml = '';
                    if (!empty($c['anh_dai_dien'])) {
                        $img = $c['anh_dai_dien'];
                        if (stripos($img, 'http://') === 0 || stripos($img, 'https://') === 0) {
                            $src = $img;
                        } else {
                            $src = rtrim(BASE_URL, '/') . '/' . ltrim($img, '/');
                        }
                        $thumbHtml = '<img src="' . htmlspecialchars($src) . '" alt="thumb" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">';
                    } else {
                        $thumbHtml = '<div style="display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;background:#666;width:48px;height:48px;border-radius:6px;">' . mb_strtoupper(mb_substr($name, 0, 1)) . '</div>';
                    }
                ?>
                    <div class="collection-card" data-id="<?= (int)$c['id'] ?>">
                        <div class="collection-thumb"><?= $thumbHtml ?></div>
                        <div class="collection-info">
                            <div class="collection-name"><?= $name ?></div>
                            <div class="collection-count"><?= $count ?> tin</div>
                        </div>
                        <div class="btn-more-dots"><i class="fa-solid fa-ellipsis"></i></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <button class="btn-create-collection" onclick="window.location.href='<?= BASE_URL ?>/cre-collection'">
            Tạo bộ sưu tập
        </button>

        <!-- Modal Xác nhận xóa -->
        <div id="collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Tùy chọn</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button id="edit-collection" class="btn-save" style="background-color: #0033cc; width: 100%; margin: 0;">Sửa tên bộ sưu tập</button>
                    <button id="confirm-delete" class="btn-save" style="background-color: #ff3333; width: 100%; margin: 0;">Xóa bộ sưu tập</button>
                    <button id="cancel-delete" class="btn-cancel" style="width: 100%; text-align: center;">Hủy</button>
                </div>
            </div>
        </div>

        <!-- Modal Đổi tên -->
        <div id="rename-collection-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px;">Đổi tên bộ sưu tập</h3>
                <div class="filter-group">
                    <input type="text" id="rename-input" class="filter-input" placeholder="Nhập tên mới...">
                </div>
                <div class="modal-actions">
                    <button id="cancel-rename" class="btn-cancel">Hủy</button>
                    <button id="confirm-rename" class="btn-apply">Lưu</button>
                </div>
            </div>
        </div>

        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script>
        // Collection card actions: open modal, rename, delete
        (function() {
            let currentId = null;
            const collectionModal = document.getElementById('collection-modal');
            const renameModal = document.getElementById('rename-collection-modal');
            const renameInput = document.getElementById('rename-input');
            const btnEdit = document.getElementById('edit-collection');
            const btnConfirmDelete = document.getElementById('confirm-delete');
            const btnCancel = document.getElementById('cancel-delete');
            const btnCancelRename = document.getElementById('cancel-rename');
            const btnConfirmRename = document.getElementById('confirm-rename');

            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest && e.target.closest('.btn-more-dots');
                if (btn) {
                    e.stopPropagation();
                    const card = btn.closest('.collection-card');
                    if (!card) return;
                    currentId = card.getAttribute('data-id');
                    if (collectionModal) collectionModal.style.display = 'flex';
                }

                // Click on card (not on dots) -> open collection detail
                const cardClick = e.target.closest && e.target.closest('.collection-card');
                if (cardClick && !e.target.closest('.btn-more-dots')) {
                    const cid = cardClick.getAttribute('data-id');
                    if (cid) window.location.href = '<?= BASE_URL ?>/collection-detail?id=' + encodeURIComponent(cid);
                }
            });

            if (btnEdit) btnEdit.addEventListener('click', function() {
                if (!currentId) return;
                collectionModal.style.display = 'none';
                if (renameModal) renameModal.style.display = 'flex';
                // fill current name
                const card = document.querySelector('.collection-card[data-id="' + currentId + '"]');
                const nameEl = card ? card.querySelector('.collection-name') : null;
                renameInput.value = nameEl ? nameEl.innerText.trim() : '';
            });

            if (btnCancel) btnCancel.addEventListener('click', function() {
                if (collectionModal) collectionModal.style.display = 'none';
                currentId = null;
            });
            if (btnCancelRename) btnCancelRename.addEventListener('click', function() {
                if (renameModal) renameModal.style.display = 'none';
                currentId = null;
            });

            if (btnConfirmRename) btnConfirmRename.addEventListener('click', function() {
                const newName = (renameInput.value || '').trim();
                if (!currentId || newName === '') return;
                btnConfirmRename.disabled = true;
                fetch('<?= BASE_URL ?>/collection-rename', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(currentId) + '&ten_bo_suu_tap=' + encodeURIComponent(newName)
                }).then(r => r.json()).then(data => {
                    btnConfirmRename.disabled = false;
                    if (data && data.ok) {
                        const card = document.querySelector('.collection-card[data-id="' + currentId + '"]');
                        if (card) card.querySelector('.collection-name').innerText = newName;
                        if (renameModal) renameModal.style.display = 'none';
                        currentId = null;
                    } else {
                        alert('Không thể đổi tên');
                    }
                }).catch(() => {
                    btnConfirmRename.disabled = false;
                    alert('Lỗi mạng');
                });
            });

            if (btnConfirmDelete) btnConfirmDelete.addEventListener('click', function() {
                if (!currentId) return;
                btnConfirmDelete.disabled = true;
                fetch('<?= BASE_URL ?>/collection-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(currentId)
                }).then(r => r.json()).then(data => {
                    btnConfirmDelete.disabled = false;
                    if (data && data.ok) {
                        const card = document.querySelector('.collection-card[data-id="' + currentId + '"]');
                        if (card) card.remove();
                        if (collectionModal) collectionModal.style.display = 'none';
                        currentId = null;
                    } else {
                        alert('Không thể xóa');
                    }
                }).catch(() => {
                    btnConfirmDelete.disabled = false;
                    alert('Lỗi mạng');
                });
            });
        })();
    </script>
</body>

</html>