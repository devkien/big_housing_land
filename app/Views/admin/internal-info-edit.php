<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chỉnh sửa thông tin nội bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>

    <style>
        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #eee !important;
        }

        /* Chỉnh lại chiều cao CKEditor cho giống ảnh (khá dài) */
        .ck-editor__editable {
            min-height: 250px !important;
            background-color: white !important;
        }

        /* Position the plus icon relative to the camera (center-right of camera) */
        .icon-plus-absolute {
            position: absolute;
            font-size: 18px;
            color: #2b6be6;
            display: block;
            left: 50%;
            top: 50%;
            transform: translate(36px, -34px);
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: #f0f4f8;">

        <div class="header-blue-solid">
            <a href="<?= BASE_URL ?>/admin/internal-info-list" class="back-btn-white"><i class="fa-solid fa-chevron-left"></i></a>
            Chỉnh sửa thông tin nội bộ
        </div>

        <div style="padding: 0 15px 100px 15px;">

            <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
            <?php $post = $post ?? null; ?>
            <form action="<?= BASE_URL ?>/admin/internal-info-edit?id=<?= (int)($post['id'] ?? 0) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="alert-wrapper">
                    <?php require_once __DIR__ . '/../partials/alert.php'; ?>
                </div>

                <label class="label-bold-black">Tiêu đề thông tin</label>
                <div style="background: white; padding: 5px; border-radius: 4px;">
                    <input type="text" name="tieu_de" class="input-title-box input-title-large"
                        value="<?= htmlspecialchars($post['tieu_de'] ?? '') ?>"
                        style="padding: 15px; box-shadow: none;">
                </div>

                <label class="label-bold-black">Nội dung thông tin nội bộ</label>
                <textarea id="editor-edit-content" name="noi_dung"><?= $post['noi_dung'] ?? '' ?></textarea>

                <label class="label-bold-black" style="margin-top:12px;">Hình ảnh / Video hiện có</label>
                <style>
                    /* Row container with horizontal scrolling */
                    .img-row {
                        display: flex;
                        gap: 12px;
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        padding-bottom: 6px;
                    }

                    .img-row::-webkit-scrollbar {
                        height: 8px;
                    }

                    .img-row::-webkit-scrollbar-thumb {
                        background: rgba(0, 0, 0, 0.12);
                        border-radius: 4px;
                    }

                    .img-wrap {
                        position: relative;
                        width: 120px;
                        flex: 0 0 auto;
                    }

                    .img-wrap img {
                        width: 100%;
                        height: 80px;
                        object-fit: cover;
                        border-radius: 6px;
                        display: block;
                    }

                    .img-delete-btn {
                        position: absolute;
                        top: 6px;
                        right: 6px;
                        width: 26px;
                        height: 26px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.95);
                        border: 1px solid rgba(0, 0, 0, 0.08);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-weight: 700;
                        color: #333;
                        font-size: 14px;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
                    }

                    .img-wrap.marked img {
                        opacity: 0.45;
                        filter: grayscale(80%);
                    }

                    .img-wrap.marked .img-delete-btn {
                        background: #ffebeb;
                        color: #d00;
                        border-color: rgba(208, 0, 0, 0.15);
                    }
                </style>

                <div class="img-row" style="padding:10px; border-radius:6px;">
                    <?php if (!empty($post['images']) && is_array($post['images'])): ?>
                        <?php foreach ($post['images'] as $img):
                            $path = $img['image_path'] ?? '';
                            $imgUrl = (preg_match('/^https?:\/\//i', $path) || strpos($path, '/') === 0) ? $path : rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
                        ?>
                            <div class="img-wrap">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="">
                                <button type="button" class="img-delete-btn" title="Đánh dấu xóa">×</button>
                                <input type="checkbox" class="img-delete-checkbox" name="remove_images[]" value="<?= (int)$img['id'] ?>" style="display:none;">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="color:#666;">Chưa có hình ảnh nào.</div>
                    <?php endif; ?>
                </div>

                <label class="label-bold-black" style="margin-top:12px;">Tải ảnh/video mới (nhiều file)</label>
                <div class="upload-box-large-center" id="upload-box" style="border-style: dashed; position: relative; cursor: pointer; border-radius:8px; padding:20px;">

                    <div class="upload-hint-text" style="z-index: 2;">
                        <i class="fa-solid fa-circle-info"></i> Tải hình ảnh/video
                    </div>

                    <i class="fa-solid fa-camera icon-camera-large" id="icon-camera" style="font-size:40px; display:block; margin:10px auto 0 auto;"></i>
                    <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus" style="margin: 8px 0px 0px -20px"></i>

                    <div class="upload-preview-container" id="preview-container" style="display:none; justify-content:center;">
                        <img src="" class="upload-preview-img" id="preview-img" alt="Preview" style="max-width:200px; max-height:160px; display:block; margin:0 auto;">
                        <button class="btn-remove-image" id="btn-remove-img" type="button" style="position:absolute; top:8px; right:8px; background:#fff;border:none;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <input type="file" id="file-upload-edit" name="media[]" style="display: none;" accept="image/*,video/*" multiple>
                </div>

                <div style="margin-top:18px;">
                    <button type="submit" class="btn-submit-blue" style="background-color: #0033cc; padding:12px;">Lưu</button>
                </div>
            </form>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        // Initialize CKEditor with existing textarea content and synchronize on form submit
        (function() {
            let editorInstance = null;
            ClassicEditor
                .create(document.querySelector('#editor-edit-content'), {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', '|',
                            'bulletedList', 'numberedList', '|',
                            'undo', 'redo'
                        ]
                    }
                })
                .then(editor => {
                    editorInstance = editor;
                    // Ensure editor starts with the current textarea value from server
                    const ta = document.getElementById('editor-edit-content');
                    if (ta && ta.value.trim() !== '') {
                        editor.setData(ta.value);
                    }
                })
                .catch(err => console.error('CKEditor init error', err));

            // On form submit, copy editor data back to textarea so server receives updated HTML
            const form = document.querySelector('form[action*="internal-info-edit"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (editorInstance) {
                        const ta = document.getElementById('editor-edit-content');
                        if (ta) ta.value = editorInstance.getData();
                    }
                    // allow submit to continue
                });
            }
            // Image delete overlay buttons: toggle hidden checkbox and visual marked state
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.img-delete-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const wrap = this.closest('.img-wrap');
                        if (!wrap) return;
                        const cb = wrap.querySelector('.img-delete-checkbox');
                        if (!cb) return;
                        cb.checked = !cb.checked;
                        wrap.classList.toggle('marked', cb.checked);
                        this.textContent = cb.checked ? '✓' : '×';
                    });
                });

                // Upload new files using legacy upload box selectors
                const uploadBox = document.getElementById('upload-box');
                const fileInput = document.getElementById('file-upload-edit');
                const previewContainer = document.getElementById('preview-container');
                const previewImg = document.getElementById('preview-img');
                const btnRemove = document.getElementById('btn-remove-img');
                const iconCamera = document.getElementById('icon-camera');
                const iconPlus = document.getElementById('icon-plus');

                if (uploadBox && fileInput) {
                    uploadBox.addEventListener('click', function() {
                        fileInput.click();
                    });
                }

                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        const f = (e.target.files && e.target.files[0]) || null;
                        if (!f) return;
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            // show preview container
                            if (previewContainer) previewContainer.style.display = 'flex';
                            if (previewImg) {
                                if (f.type.startsWith('video/')) {
                                    // show video element instead
                                    const vid = document.createElement('video');
                                    vid.controls = true;
                                    vid.src = evt.target.result;
                                    vid.style.maxWidth = '200px';
                                    vid.style.maxHeight = '160px';
                                    vid.style.display = 'block';
                                    // replace existing preview image
                                    previewContainer.innerHTML = '';
                                    previewContainer.appendChild(vid);
                                    // add remove button
                                    if (btnRemove) previewContainer.appendChild(btnRemove);
                                } else {
                                    previewImg.src = evt.target.result;
                                    previewImg.style.display = 'block';
                                    if (btnRemove && !previewContainer.contains(btnRemove)) previewContainer.appendChild(btnRemove);
                                }
                            }
                            if (iconCamera) iconCamera.style.display = 'none';
                            if (iconPlus) iconPlus.style.display = 'none';
                        };
                        reader.readAsDataURL(f);
                    });
                }

                if (btnRemove) {
                    btnRemove.addEventListener('click', function(e) {
                        e.stopPropagation();
                        // clear file input
                        if (fileInput) fileInput.value = '';
                        if (previewContainer) previewContainer.style.display = 'none';
                        if (previewImg) previewImg.src = '';
                        if (iconCamera) iconCamera.style.display = 'block';
                        if (iconPlus) iconPlus.style.display = 'block';
                    });
                }
            });
        })();
    </script>
</body>

</html>