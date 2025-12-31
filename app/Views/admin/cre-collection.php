<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tạo bộ sưu tập</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: #f9f9f9;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/collection" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Tạo bộ sưu tập</div>
            <div class="header-icon-btn"></div>
        </header>

        <?php if (!empty($_SESSION['error'])): ?>
            <div style="padding: 10px 15px; background-color: #ffebee; color: #c62828; margin: 10px 15px; border-radius: 4px;">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/admin/cre-collection" enctype="multipart/form-data" style="padding: 20px 0;">
            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>Tên bộ sưu tập</span>
                    <span class="counter-text">0/50</span>
                </div>
                <div class="edit-input-box">
                    <input type="text" name="ten_bo_suu_tap" placeholder="Nhập tên bộ sưu tập..." class="input-placeholder-gray" style="padding-left: 0;" required>
                </div>
            </div>

            <div class="edit-form-group" style="padding: 0 15px;">
                <div class="edit-label-row">
                    <span>Ảnh bìa</span>
                </div>
                <div class="upload-box-large-center" id="upload-box-collection" style="position: relative; cursor: pointer; margin: 20px auto;">
                    <div class="upload-hint-text" style="z-index: 2;">
                        <i class="fa-solid fa-circle-info"></i> Tải hình ảnh
                    </div>

                    <i class="fa-solid fa-camera icon-camera-large" id="icon-camera-collection"></i>
                    <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus-collection"></i>

                    <div class="upload-preview-container" id="preview-container-collection" style="display: none;">
                        <img src="" class="upload-preview-img" id="preview-img-collection" alt="Preview">
                        <button class="btn-remove-image" id="btn-remove-img-collection" type="button"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <input type="file" id="file-upload-collection" name="anh_dai_dien" style="display: none;" accept="image/*">
                </div>
            </div>

            <div style="padding: 0 15px; margin-top: 10px;">
                <label>Mô tả (tùy chọn)</label>
                <textarea name="mo_ta" rows="3" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ddd;"></textarea>
            </div>

            <div style="padding: 20px;">
                <button type="submit" class="btn-save-change">Tạo</button>
            </div>
        </form>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-collection');
            const fileInput = document.getElementById('file-upload-collection');
            const previewContainer = document.getElementById('preview-container-collection');
            const previewImg = document.getElementById('preview-img-collection');
            const btnRemove = document.getElementById('btn-remove-img-collection');
            const iconCamera = document.getElementById('icon-camera-collection');
            const iconPlus = document.getElementById('icon-plus-collection');

            // Xử lý click vào box để tải ảnh
            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            // Xử lý khi chọn file
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';
                        previewImg.src = evt.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Xử lý click nút Xóa
            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                previewContainer.style.display = 'none';
                previewImg.src = '';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                uploadBox.style.backgroundColor = '#f2f6ff';
            });
        });
    </script>
</body>

</html>