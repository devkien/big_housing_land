<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thêm thông tin nội bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>

    <style>
        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #eee !important;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: #f0f4f8;">
        <div class="header-blue-solid">
            <a href="<?= BASE_URL ?>/superadmin/info" class="back-btn-white"><i class="fa-solid fa-chevron-left"></i></a>
            Thêm thông tin nội bộ
        </div>

        <div style="padding: 0 15px 100px 15px;">

            <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
            <form action="<?= BASE_URL ?>/superadmin/add-internal-info" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="alert-wrapper">
                    <?php require_once __DIR__ . '/../partials/alert.php'; ?>
                </div>

                <label class="label-bold-black">Tiêu đề thông tin</label>
                <div style="background: white; padding: 5px; border-radius: 4px;">
                    <input type="text" name="tieu_de" class="input-title-box" placeholder="Nhập tiêu đề thông tin" style="padding: 10px; box-shadow: none;" value="<?= old('tieu_de') ?>">
                    <div class="input-counter-right" style="padding-right: 10px; padding-bottom: 5px;">0/1500 kí tự</div>
                </div>

                <label class="label-bold-black">Nội dung thông tin nội bộ</label>
                <textarea id="editor-content" name="noi_dung" placeholder="Nhập văn bản thông tin"><?= old('noi_dung') ?></textarea>

                <div class="upload-box-large-center" id="upload-box" style="position: relative; cursor: pointer;">
                    <div class="upload-hint-text" style="z-index: 2;">
                        <i class="fa-solid fa-circle-info"></i> Tải hình ảnh/video
                    </div>

                    <i class="fa-solid fa-camera icon-camera-large" id="icon-camera"></i>
                    <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus"></i>

                    <div class="upload-preview-container" id="preview-container" style="display: none;">
                        <img src="" class="upload-preview-img" id="preview-img" alt="Preview" style="display: none;">
                        <video controls class="upload-preview-img" id="preview-video" style="display: none;"></video>
                        <button class="btn-remove-image" id="btn-remove-img" type="button"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <input type="file" id="file-upload-internal" name="media[]" style="display: none;" accept="image/*,video/*" multiple>
                </div>

                <button type="submit" class="btn-submit-blue" style="background-color: #0033cc;">Đăng</button>
            </form>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        ClassicEditor
            .create(document.querySelector('#editor-content'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'bulletedList', 'numberedList', '|',
                        'undo', 'redo'
                    ]
                },
                placeholder: 'Nhập văn bản thông tin...'
            })
            .then(editor => {
                console.log('CKEditor đã sẵn sàng.', editor);
            })
            .catch(error => {
                console.error('Lỗi khởi tạo CKEditor:', error);
            });

        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box');
            const fileInput = document.getElementById('file-upload-internal');
            const previewContainer = document.getElementById('preview-container');
            const previewImg = document.getElementById('preview-img');
            const previewVideo = document.getElementById('preview-video');
            const btnRemove = document.getElementById('btn-remove-img');
            const iconCamera = document.getElementById('icon-camera');
            const iconPlus = document.getElementById('icon-plus');

            // Xử lý click vào box để tải ảnh
            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            // Xử lý khi chọn file
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';

                        if (file.type.startsWith('video/')) {
                            previewImg.style.display = 'none';
                            previewVideo.style.display = 'block';
                            previewVideo.src = evt.target.result;
                        } else {
                            previewVideo.style.display = 'none';
                            previewImg.style.display = 'block';
                            previewImg.src = evt.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Xử lý click nút Xóa
            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn sự kiện click lan ra box cha
                fileInput.value = ''; // Reset input file
                previewContainer.style.display = 'none';
                previewImg.src = '';
                previewVideo.src = '';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                uploadBox.style.backgroundColor = '#f2f6ff'; // Reset màu nền
            });
        });
    </script>
</body>

</html>