<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>

<body>
    <div class="app-container register-page">
        <?php
        require_once __DIR__ . '/../partials/alert.php';
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
        ?>

        <header class="register-header">
            <a href="<?= BASE_URL ?>/login" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h3>Đăng ký</h3>
            <div style="width: 18px;"></div>
        </header>

        <form action="<?= BASE_URL ?>/register" method="POST" enctype="multipart/form-data">

            <div class="section-label">1. Thông tin đăng nhập</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="so_dien_thoai" placeholder="Nhập số điện thoại" pattern="[0-9]{10}" value="<?= htmlspecialchars($old['so_dien_thoai'] ?? '') ?>" required>
            </div>

            <div class="reg-box-input">
                <i class="fa-solid fa-lock" style="font-size: 12px;"></i> <input type="password" name="password" placeholder="Vui lòng nhập mật khẩu" required>
            </div>

            <div class="section-label">2. Thông tin cơ bản</div>

            <div class="reg-box-input">
                <i class="fa-solid fa-location-arrow"></i> <input type="text" name="ho_ten" placeholder="Nhập họ và tên" value="<?= htmlspecialchars($old['ho_ten'] ?? '') ?>" required>
            </div>


            <label class="reg-box-input">
                <i class="fa-regular fa-calendar"></i>
                <input type="number" name="nam_sinh" placeholder="Nhập năm sinh" min="1900" max="2025" value="<?= htmlspecialchars($old['nam_sinh'] ?? '') ?>" required>
            </label>
            <div style="height: 10px;"></div>
            <div class="reg-box-input">
                <i class="fa-solid fa-location-dot icon-location"></i>
                <input type="text" name="dia_chi" placeholder="Địa chỉ" value="<?= htmlspecialchars($old['dia_chi'] ?? '') ?>" required>
            </div>
            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-transgender"></i>
                <select name="gioi_tinh" required>
                    <option value="" disabled <?= empty($old['gioi_tinh']) ? 'selected' : '' ?>>Chọn giới tính</option>
                    <option value="nam" <?= ($old['gioi_tinh'] ?? '') === 'nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="nu" <?= ($old['gioi_tinh'] ?? '') === 'nu' ? 'selected' : '' ?>>Nữ</option>
                    <option value="khac" <?= ($old['gioi_tinh'] ?? '') === 'khac' ? 'selected' : '' ?>>Khác</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-box-input">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Nhập email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="reg-box-input">
                <i class="fa-brands fa-facebook"></i>
                <input type="text" name="link_fb" placeholder="Nhập link fb" value="<?= htmlspecialchars($old['link_fb'] ?? '') ?>" required>
            </div>
            <div class="reg-box-input">
                <i class="fa-solid fa-user icon-user"></i>
                <input type="text" name="ma_gioi_thieu" placeholder="Mã giới thiệu" value="<?= htmlspecialchars($old['ma_gioi_thieu'] ?? '') ?>" required>
            </div>


            <div class="section-label">3. Thông tin Big Housing</div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-book"></i>
                <select name="loai_tai_khoan" required>
                    <option value="" disabled <?= empty($old['loai_tai_khoan']) ? 'selected' : '' ?>>Chọn loại tài khoản</option>
                    <option value="nhan_vien" <?= ($old['loai_tai_khoan'] ?? '') === 'nhan_vien' ? 'selected' : '' ?>>Đầu khách</option>
                    <option value="quan_ly" <?= ($old['loai_tai_khoan'] ?? '') === 'quan_ly' ? 'selected' : '' ?>>Đầu chủ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="reg-line-select">
                <i class="icon-left fa-solid fa-map-marker-alt"></i>
                <select name="vi_tri" required>
                    <option value="" disabled <?= empty($old['vi_tri']) ? 'selected' : '' ?>>Chọn vị trí</option>
                    <option value="kho_nha_dat" <?= ($old['vi_tri'] ?? '') === 'kho_nha_dat' ? 'selected' : '' ?>>Kho nhà đất</option>
                    <option value="kho_nha_cho_thue" <?= ($old['vi_tri'] ?? '') === 'kho_nha_cho_thue' ? 'selected' : '' ?>>Kho nhà cho thuê</option>
                    <option value="ca_hai" <?= ($old['vi_tri'] ?? '') === 'ca_hai' ? 'selected' : '' ?>>Kho nhà đất và kho nhà cho thuê</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

        <div class="reg-line-select">
                <i class="icon-left fa-solid fa-fire"></i>
                <select name="phong_ban" required>
                    <option value="" disabled <?= empty($old['phong_ban']) ? 'selected' : '' ?>>Chọn phòng ban</option>
                    
                    <option value="Thiện Chiến" <?= ($old['phong_ban'] ?? '') === 'Thiện Chiến' ? 'selected' : '' ?>>Thiện Chiến</option>
                    
                    <option value="Hùng Phát" <?= ($old['phong_ban'] ?? '') === 'Hùng Phát' ? 'selected' : '' ?>>Hùng Phát</option>
                    
                    <option value="Tinh Nhuệ" <?= ($old['phong_ban'] ?? '') === 'Tinh Nhuệ' ? 'selected' : '' ?>>Tinh Nhuệ</option>
                </select>
                <i class="arrow-right fa-solid fa-chevron-down"></i>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row"><span>4.Mặt trước CCCD</span></div>
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
                <div class="reg-box-input">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="so_cccd" placeholder="Nhập số CCCD" pattern="[0-9]{12}" value="<?= htmlspecialchars($old['so_cccd'] ?? '') ?>" required>
                </div>
            </div>

            <button class="btn-save">Đăng ký</button>

        </form>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. KHAI BÁO BIẾN DOM ---
        const uploadBox = document.getElementById('upload-box-cccd');
        const fileInput = document.getElementById('file-upload-cccd');
        const previewContainer = document.getElementById('preview-container-cccd');
        const previewImg = document.getElementById('preview-img-cccd');
        const btnRemove = document.getElementById('btn-remove-img-cccd');
        const iconCamera = document.getElementById('icon-camera-cccd');
        const iconPlus = document.getElementById('icon-plus-cccd');
        const hintText = uploadBox.querySelector('.upload-hint-text');

        // Key lưu trữ LocalStorage
        const FORM_KEY = 'bh_register_v1';
        const form = document.querySelector('form[action="<?= BASE_URL ?>/register"]');

        // --- 2. HÀM RESET ẢNH (Xóa sạch ảnh và đưa về trạng thái ban đầu) ---
        function resetUploadState() {
            // 1. Xóa giá trị file trong input
            if (fileInput) fileInput.value = '';

            // 2. Ẩn khung xem trước và xóa đường dẫn ảnh
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
            if (previewImg) {
                previewImg.src = '';
                previewImg.removeAttribute('src'); // Gỡ bỏ hoàn toàn src
            }

            // 3. Hiển thị lại các icon Camera/Dấu cộng ban đầu
            if (iconCamera) iconCamera.style.display = 'block';
            if (iconPlus) iconPlus.style.display = 'block';
            if (hintText) hintText.style.display = 'block';
            if (uploadBox) uploadBox.style.backgroundColor = '#f2f6ff';
        }

        // --- 3. CHỈ LƯU VĂN BẢN (Không lưu ảnh) ---
        function saveFormToLocal() {
            if (!form) return;
            const data = {};
            const elements = form.querySelectorAll('input[name], select[name], textarea[name]');
            elements.forEach(el => {
                // Bỏ qua input file và password
                if (el.type === 'password' || el.type === 'file') return;
                
                // Chỉ lưu text, radio, checkbox
                data[el.name] = (el.type === 'checkbox') ? el.checked : el.value;
            });
            // LƯU Ý: Đã xóa đoạn code lưu ảnh preview tại đây
            localStorage.setItem(FORM_KEY, JSON.stringify(data));
        }

        function restoreFormFromLocal() {
            if (!form) return;
            const data = JSON.parse(localStorage.getItem(FORM_KEY) || 'null');
            if (!data) return;

            const elements = form.querySelectorAll('input[name], select[name], textarea[name]');
            elements.forEach(el => {
                if (data[el.name] !== undefined && el.type !== 'file') {
                    if (el.type === 'checkbox') el.checked = !!data[el.name];
                    else el.value = data[el.name];
                }
            });
            // LƯU Ý: Đã xóa đoạn code khôi phục ảnh tại đây
        }

        // --- 4. SỰ KIỆN NGƯỜI DÙNG ---
        
        // Click vào box để chọn ảnh
        uploadBox.addEventListener('click', function() {
            if (previewContainer.style.display === 'none') fileInput.click();
        });

        // Khi chọn file xong -> Hiện ảnh preview (chỉ hiện tạm thời lúc đó)
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewContainer.style.display = 'flex';
                    iconCamera.style.display = 'none';
                    iconPlus.style.display = 'none';
                    if (hintText) hintText.style.display = 'none';
                    uploadBox.style.backgroundColor = 'white';
                    previewImg.src = evt.target.result;
                    // Không gọi saveFormToLocal ở đây nữa để tránh lưu ảnh cache
                }
                reader.readAsDataURL(file);
            }
        });

        // Nút Xóa ảnh: Gọi hàm Reset
        btnRemove.addEventListener('click', function(e) {
            e.preventDefault(); 
            e.stopPropagation();
            resetUploadState(); 
        });

        // Lưu text khi nhập liệu
        form.querySelectorAll('input, select').forEach(el => {
            if (el.type !== 'file') el.addEventListener('change', saveFormToLocal);
        });

        // --- 5. LOGIC KHI TRANG VỪA TẢI XONG (QUAN TRỌNG NHẤT) ---
        
        // Bước A: Kiểm tra xem có phải đăng ký thành công không
        const successAlert = document.querySelector('.alert--success');
        
        if (successAlert) {
            // Nếu Thành công: Xóa sạch cả Text lẫn Ảnh
            localStorage.removeItem(FORM_KEY);
            resetUploadState(); 
        } else {
            // Nếu Thất bại hoặc Load thường: 
            // 1. Khôi phục Text (để user đỡ phải gõ lại tên, sđt...)
            restoreFormFromLocal();
            
            // 2. NHƯNG LUÔN LUÔN XÓA ẢNH (theo yêu cầu của bạn)
            // Bất kể lỗi hay không, cứ load lại trang là ảnh bay màu
            resetUploadState();
        }
    });
</script>
</body>

</html>

<!DOCTYPE html>
<html