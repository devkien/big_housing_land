<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thông tin nội bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background-color: #f5f7fa;">

        <header class="simple-blue-header" style="display: flex; align-items: center; justify-content: space-between;">
            <a href="<?= BASE_URL ?>/superadmin/home" style="color: white; font-size: 18px;"><i class="fa-solid fa-chevron-left"></i></a>
            <div style="flex: 1; text-align: center;">Thông tin nội bộ</div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="<?= BASE_URL ?>/superadmin/add-internal-info" style="color: white; font-size: 18px;"><i class="fa-solid fa-plus"></i></a>
            </div>
        </header>

        <div style="padding: 10px 15px; display: flex; justify-content: flex-end;">
            <button id="manage-posts-btn" style="background:none; border:none; color:#f0f0f0; font-size: 14px; font-weight: 600; cursor: pointer; padding: 0; white-space: nowrap;background-color: #0033cc;padding: 8px 12px 8px 12px;border-radius: 8px;" onclick="window.location.href='<?= BASE_URL ?>/superadmin/internal-info-list'">Quản lý tin</button>
        </div>

        <div class="info-list">
            <?php
            // Posts should be provided by controller as $posts
            $posts = $posts ?? [];
            foreach ($posts as $post):
                // try to get first image via model helper if available
                $thumb = null;
                if (class_exists('InternalPost')) {
                    $img = InternalPost::getFirstImagePath($post['id']);
                    if ($img) $thumb = $img;
                }
                // excerpt from noi_dung
                $excerpt = strip_tags($post['noi_dung'] ?? '');
                if (strlen($excerpt) > 220) $excerpt = mb_substr($excerpt, 0, 220) . '...';
                $created = !empty($post['created_at']) ? date('d/m/Y', strtotime($post['created_at'])) : '';
            ?>
                <article class="info-card" onclick="window.location.href='<?= BASE_URL ?>/superadmin/internal-info-detail?id=<?= $post['id'] ?>'" style="cursor: pointer; position: relative;">
                    <?php if ($thumb):
                        // Normalize image URL: if it's a full URL or starts with '/', use as-is.
                        // Otherwise prefix with BASE_URL so path points to public uploads folder.
                        $imgUrl = '';
                        if (preg_match('/^https?:\/\//i', $thumb) || strpos($thumb, '/') === 0) {
                            $imgUrl = $thumb;
                        } else {
                            $imgUrl = rtrim(BASE_URL, '/') . '/' . ltrim($thumb, '/');
                        }
                    ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>" class="info-thumb" alt="<?= htmlspecialchars($post['tieu_de'] ?? '') ?>">
                    <?php else: ?>
                        <div class="info-thumb" style="background:#ddd;display:flex;align-items:center;justify-content:center;color:#666;font-weight:600;">No image</div>
                    <?php endif; ?>
                    <div class="info-body">
                        <div class="info-date"><?= htmlspecialchars($created) ?></div>
                        <h3 class="info-title"><?= htmlspecialchars($post['tieu_de'] ?? '') ?></h3>
                        <div class="info-desc"><?= htmlspecialchars($excerpt) ?></div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (empty($posts)): ?>
                <div style="padding: 30px; text-align: center; color: #666;">Chưa có thông tin nội bộ nào.</div>
            <?php endif; ?>
        </div>

        <!-- Modal Quản lý tin -->
        <div id="manage-posts-modal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Quản lý tin nội bộ</h3>
                <div id="manage-list-container" style="max-height: 400px; overflow-y: auto; margin-bottom: 15px;">
                    <!-- Danh sách sẽ được tạo tự động bằng JS -->
                </div>
                <div class="modal-actions">
                    <button id="close-manage-modal" class="btn-cancel" style="width: 100%;">Đóng</button>
                </div>
            </div>
        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const manageBtn = document.getElementById('manage-posts-btn');
            const manageModal = document.getElementById('manage-posts-modal');
            const closeManageBtn = document.getElementById('close-manage-modal');
            const listContainer = document.getElementById('manage-list-container');

            if (manageBtn && manageModal) {
                manageBtn.addEventListener('click', function() {
                    listContainer.innerHTML = '';
                    const cards = document.querySelectorAll('.info-card');

                    cards.forEach((card, index) => {
                        const title = card.querySelector('.info-title').innerText;
                        const item = document.createElement('div');
                        item.className = 'manage-item';
                        item.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;';

                        item.innerHTML = `
                            <div style="flex: 1; font-size: 14px; font-weight: 500; margin-right: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${title}</div>
                            <div style="display: flex; gap: 15px;">
                                <i class="fa-solid fa-pen" style="color: #0033cc; cursor: pointer;" onclick="window.location.href='edit_internal_info.html'"></i>
                                <i class="fa-solid fa-trash" style="color: #ff3333; cursor: pointer;" data-index="${index}"></i>
                            </div>
                        `;
                        listContainer.appendChild(item);
                    });

                    // Xử lý sự kiện xóa trong modal
                    listContainer.querySelectorAll('.fa-trash').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('Bạn có chắc chắn muốn xóa tin này?')) {
                                const idx = this.getAttribute('data-index');
                                if (cards[idx]) cards[idx].remove(); // Xóa card ở ngoài
                                this.closest('.manage-item').remove(); // Xóa dòng trong modal
                            }
                        });
                    });

                    manageModal.style.display = 'flex';
                });
            }

            if (closeManageBtn) {
                closeManageBtn.onclick = () => manageModal.style.display = 'none';
            }
        });
    </script>
</body>

</html>