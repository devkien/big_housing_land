<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng thông báo vụ chốt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <div class="app-container" style="background: white;">
        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Đăng thông báo vụ chốt</div>
            <div style="width: 20px;"></div>
        </header>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <form action="<?= BASE_URL ?>/superadmin/cre-notification" method="POST" enctype="multipart/form-data" style="padding: 15px;">
            <?php require_once __DIR__ . '/../../Helpers/functions.php'; echo csrf_field(); ?>

            <label class="label-bold-small">Nội dung thông báo <span style="color:red">*</span></label>
            <textarea name="noi_dung" class="textarea-large" placeholder="Nhập nội dung vụ chốt..." required></textarea>
            <div class="char-counter-right">0/500</div>

            <label class="label-bold-small">Mã nhân viên <span style="color:red">*</span></label>
            <input type="text" name="ma_nhan_su" class="input-simple-border" placeholder="Nhập mã nhân viên (VD: MNV01)" required>

            <label class="label-bold-small">Hình ảnh minh họa</label>
            <div class="upload-box-dashed" id="upload-box">
                <i class="fa-solid fa-camera upload-icon-camera" id="icon-camera"></i>
                <i class="fa-solid fa-plus upload-icon-plus" id="icon-plus"></i>
                <div class="upload-text" id="upload-text">Tải ảnh lên</div>
                
                <div class="upload-preview-container" id="preview-container" style="display: none;">
                    <img src="" class="upload-preview-img" id="preview-img">
                    <button class="btn-remove-image" id="btn-remove-img" type="button"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <input type="file" name="image" id="file-input" style="display: none;" accept="image/*">
            </div>

            <button type="submit" class="btn-submit-blue">Đăng thông báo</button>
        </form>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box');
            const fileInput = document.getElementById('file-input');
            const previewContainer = document.getElementById('preview-container');
            const previewImg = document.getElementById('preview-img');
            const btnRemove = document.getElementById('btn-remove-img');
            const iconCamera = document.getElementById('icon-camera');
            const iconPlus = document.getElementById('icon-plus');
            const uploadText = document.getElementById('upload-text');

            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewImg.src = evt.target.result;
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        uploadText.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                }
            });

            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                previewContainer.style.display = 'none';
                previewImg.src = '';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                uploadText.style.display = 'block';
            });
        });
    </script>
</body>
</html>