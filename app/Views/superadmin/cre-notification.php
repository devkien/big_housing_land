<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng thông báo vụ chốt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 150px !important;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/notification" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Đăng thông báo vụ chốt</div>
            <div style="width: 20px;"></div>
        </header>

        <div style="padding: 20px 15px 80px 15px;">

            <?php
            $errors = $errors ?? [];
            $old = $old ?? [];
            ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="margin-bottom:12px; padding:10px; border-radius:6px; background:#fee; color:#700;">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/superadmin/cre-notification" enctype="multipart/form-data">

                <label class="label-bold-small">Tiêu đề</label>
                <input type="text" name="tieu_de" class="input-simple-border" placeholder="Nhập tiêu đề" style="width: 100%; margin-bottom:10px;" value="<?= htmlspecialchars($old['tieu_de'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <label class="label-bold-small">Nội dung</label>
                <textarea id="editor-notify-content" name="noi_dung"><?= htmlspecialchars($old['noi_dung'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

                <input type="text" name="ma_nhan_vien" class="input-simple-border" placeholder="Nhập mã nhân viên chốt" style="width: 200px; margin-top:10px;" value="<?= htmlspecialchars($old['ma_nhan_vien'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="upload-box-dashed" id="upload-box-notify" style="position: relative;">
                    <div id="upload-initial-notify" style="display: flex; flex-direction: column; align-items: center;">
                        <div style="position: absolute; top: 10px; right: 10px; font-size: 10px; color: #0044cc;">
                            <i class="fa-solid fa-circle-info"></i> Tải hình ảnh/video
                        </div>
                        <div style="position: relative;">
                            <i class="fa-solid fa-camera upload-icon-camera"></i>
                            <i class="fa-solid fa-plus" style="position: absolute; top: -5px; right: -8px; font-size: 14px; color: #0044cc; font-weight: bold;"></i>
                        </div>
                    </div>

                    <div id="preview-container-notify" style="display: none; width: 100%; height: 100%; overflow-y: auto; padding: 10px; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center;"></div>

                    <input type="file" id="file-upload-notify" name="images[]" style="display: none;" accept="image/*,video/*" multiple>
                </div>

                <div style="margin-top:12px;">
                    <button class="btn-submit-blue" type="submit">Đăng</button>
                </div>

            </form>

        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor-notify-content'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'bulletedList', 'numberedList', '|',
                        'undo', 'redo'
                    ]
                },
                placeholder: 'Nhập nội dung tại đây...'
            })
            .catch(error => {
                console.error(error);
            });

        // Ensure CKEditor content is present in the textarea named `noi_dung` before submit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    // CKEditor already binds to the textarea by name, so value is submitted.
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-notify');
            const fileInput = document.getElementById('file-upload-notify');
            const initialView = document.getElementById('upload-initial-notify');
            const previewContainer = document.getElementById('preview-container-notify');

            if (uploadBox && fileInput) {
                uploadBox.addEventListener('click', function(e) {
                    if (!e.target.closest('.btn-remove-preview')) {
                        fileInput.click();
                    }
                });

                fileInput.addEventListener('change', function(e) {
                    const files = Array.from(e.target.files);
                    if (files.length > 0) {
                        initialView.style.display = 'none';
                        previewContainer.style.display = 'flex';
                        previewContainer.innerHTML = '';

                        files.forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(evt) {
                                const wrapper = document.createElement('div');
                                wrapper.style.position = 'relative';
                                wrapper.style.width = '80px';
                                wrapper.style.height = '80px';

                                const media = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
                                media.src = evt.target.result;
                                media.style.width = '100%';
                                media.style.height = '100%';
                                media.style.objectFit = 'cover';
                                media.style.borderRadius = '4px';

                                const btnRemove = document.createElement('div');
                                btnRemove.className = 'btn-remove-preview';
                                btnRemove.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                                btnRemove.style.cssText = 'position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; background: red; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; z-index: 10;';

                                btnRemove.onclick = function(ev) {
                                    ev.stopPropagation();
                                    wrapper.remove();
                                    if (previewContainer.children.length === 0) {
                                        initialView.style.display = 'flex';
                                        previewContainer.style.display = 'none';
                                        fileInput.value = '';
                                    }
                                };

                                wrapper.appendChild(media);
                                wrapper.appendChild(btnRemove);
                                previewContainer.appendChild(wrapper);
                            }
                            reader.readAsDataURL(file);
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>