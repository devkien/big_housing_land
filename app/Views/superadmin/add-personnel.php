<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thêm nhân sự</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        /* --- CODE SỬA LỖI 2 CON MẮT --- */
        /* Ẩn icon con mắt mặc định "bé xíu" của trình duyệt Edge/IE */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }

        /* Ẩn trên một số trình duyệt khác nếu có */
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }

        /* Đảm bảo option hiển thị bằng kích thước select */
        select option {
            width: 20%;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: white;">

        <div class="edit-header">
            <a href="<?= BASE_URL ?>/superadmin/management-owner" style="position: absolute; left: 15px; color: black;"><i class="fa-solid fa-chevron-left"></i></a>
            Thêm nhân sự
        </div>
        <div style="border-bottom: 1px solid #D9D9D9;"></div>

        <div style="padding-bottom: 80px; padding-top: 10px;">

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert--error" style="max-width:720px; margin:10px auto;">
                    <div class="alert-inner">
                        <div class="alert-message"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert--success" style="max-width:720px; margin:10px auto;">
                    <div class="alert-inner">
                        <div class="alert-message"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/superadmin/add-personnel" enctype="multipart/form-data">
                <?php require_once __DIR__ . '/../../Helpers/functions.php';
                echo csrf_field(); ?>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>1. Họ và tên</span></div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="ho_ten" value="<?php echo old('ho_ten'); ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>2. Số điện thoại</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" name="so_dien_thoai" value="<?php echo old('so_dien_thoai'); ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>3. Email</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" value="<?php echo old('email'); ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row">
                        <span>4. Ngày sinh</span>
                        <span class="counter-text">0/10</span>
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-regular fa-calendar"></i>
                        <input type="text" name="nam_sinh" value="<?php echo old('nam_sinh'); ?>">
                    </div>
                </div>

                <div class="form-row-split">
                    <div class="col-left-60">
                        <div class="edit-label-row"><span>5. Phòng ban</span></div>
                        <div class="input-relative">
                            <select class="custom-select" name="phong_ban">
                                <option value="">-- Chọn phòng ban --</option>
                                <option value="Thiện Chiến">Thiện Chiến</option>
                                <option value="Hùng Phát">Hùng Phát</option>
                                <option value="Tinh Nhuệ">Tinh Nhuệ</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                        </div>
                    </div>
                    <div class="col-left-60">
                        <div class="edit-label-row"><span>6. Vị trí</span></div>
                        <div class="input-relative">
                            <select class="custom-select" name="vi_tri">
                                <option value="">-- Chọn vị trí --</option>
                                <option value="kho_nha_dat">Kho nhà đất</option>
                                <option value="kho_nha_cho_thue">Kho nhà cho thuê</option>
                                <option value="ca_hai">Kho nhà đất và kho nhà cho thuê</option>
                            </select>
                            <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                        </div>
                    </div>



                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>7. Phân loại</span></div>
                    <div class="edit-input-box input-relative">
                        <i class="fa-solid fa-key icon-key" style="transform: scaleX(-1); display: inline-block;"></i>
                        <select class="custom-select select-type-fix" name="quyen">
                            <option value="admin" <?php echo (old('quyen') === 'admin') ? 'selected' : ''; ?>>Đầu chủ</option>
                            <option value="user" <?php echo (old('quyen') === 'user') ? 'selected' : ''; ?>>Đầu khách</option>
                        </select>
                        <i class="fa-solid fa-chevron-down arrow-down-absolute"></i>
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>8. Địa chỉ</span></div>
                    <div class="edit-input-box">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" name="dia_chi" value="<?php echo old('dia_chi'); ?>">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>9. Người giới thiệu</span></div>
                    <div class="edit-input-box">
                        <input type="text" name="nguoi_gioi_thieu" value="<?php echo old('nguoi_gioi_thieu'); ?>" style="padding-left: 5px;">
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>10. Mật khẩu</span></div>
                    <div class="edit-input-box input-relative">
                        <input type="password" name="password" value="" id="password-input" style="letter-spacing: 2px;">

                        <i class="fa-regular fa-eye-slash" id="toggle-password" style="color:#000; cursor: pointer;"></i>
                    </div>
                </div>

                <div class="edit-form-group">
                    <div class="edit-label-row"><span>11. Căn Cước công dân</span></div>
                    <div class="upload-box-large-center" id="upload-box-cccd" style="position: relative; cursor: pointer; margin-top: 10px;">
                        <div class="upload-hint-text" style="z-index: 2;">
                            <i class="fa-solid fa-circle-info"></i> Tải hình ảnh
                        </div>

                        <i class="fa-solid fa-camera icon-camera-large" id="icon-camera-cccd"></i>
                        <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus-cccd"></i>

                        <div class="upload-preview-container" id="preview-container-cccd" style="display: none;">
                            <img src="" class="upload-preview-img" id="preview-img-cccd" alt="Preview">
                            <button class="btn-remove-image" id="btn-remove-img-cccd" type="button"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <input type="file" id="file-upload-cccd" name="anh_cccd" style="display: none;" accept="image/*">
                    </div>
                    <div class="edit-input-box">
                        <i class="fa-regular fa-id-card"></i>
                        <input type="text" name="so_cccd" value="<?php echo old('so_cccd'); ?>">
                    </div>
                </div>

                <button class="btn-save-change">Lưu thay đổi</button>

            </form>

        </div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-cccd');
            const fileInput = document.getElementById('file-upload-cccd');
            const previewContainer = document.getElementById('preview-container-cccd');
            const previewImg = document.getElementById('preview-img-cccd');
            const btnRemove = document.getElementById('btn-remove-img-cccd');
            const iconCamera = document.getElementById('icon-camera-cccd');
            const iconPlus = document.getElementById('icon-plus-cccd');
            const hintText = uploadBox ? uploadBox.querySelector('.upload-hint-text') : null;

            if (!uploadBox || !fileInput) return;

            uploadBox.addEventListener('click', function() {
                if (previewContainer && previewContainer.style.display === 'flex') return;
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        if (previewContainer) previewContainer.style.display = 'flex';
                        if (iconCamera) iconCamera.style.display = 'none';
                        if (iconPlus) iconPlus.style.display = 'none';
                        if (hintText) hintText.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';
                        if (previewImg) previewImg.src = evt.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            if (btnRemove) {
                btnRemove.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fileInput.value = '';
                    if (previewContainer) previewContainer.style.display = 'none';
                    if (previewImg) previewImg.src = '';
                    if (iconCamera) iconCamera.style.display = 'block';
                    if (iconPlus) iconPlus.style.display = 'block';
                    if (hintText) hintText.style.display = 'block';
                    uploadBox.style.backgroundColor = '#f2f6ff';
                });
            }
        });
    </script>
</body>

</html>