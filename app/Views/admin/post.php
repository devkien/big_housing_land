<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng tin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/admin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Đăng tin</div>
            <div class="header-icon-btn"></div>
        </header>

        <form class="post-form-scroll" style="padding-bottom: 80px;" method="POST" action="<?= BASE_URL ?>/admin/management-resource-post" enctype="multipart/form-data">
            <div class="form-section-title">Người đăng</div>
            <div class="form-group">
                <input type="text" class="form-input focus-blue" name="ho_ten_nguoi_dang" value="<?= htmlspecialchars($user['ho_ten'] ?? '') ?>" readonly>
            </div>
            <div class="form-group">
                <select class="form-input" name="phong_ban">
                    <option value="">Chọn Phòng Ban</option>
                    <option value="Thiện Chiến" <?= (isset($user['phong_ban']) && $user['phong_ban'] == 'Thiện Chiến') ? 'selected' : '' ?>>Thiện Chiến</option>
                    <option value="Hùng Phát" <?= (isset($user['phong_ban']) && $user['phong_ban'] == 'Hùng Phát') ? 'selected' : '' ?>>Hùng Phát</option>
                    <option value="Tinh Nhuệ" <?= (isset($user['phong_ban']) && $user['phong_ban'] == 'Tinh Nhuệ') ? 'selected' : '' ?>>Tinh Nhuệ</option>
                </select>
            </div>

            <div class="form-section-title">Thông tin BĐS</div>
            <div class="form-group" style="position: relative;">
                <input type="text" name="tieu_de" class="form-input" placeholder=" " required>
                <span class="fake-placeholder">Tiêu đề <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" name="loai_bds" required>
                    <option value="" disabled selected></option>
                    <option value="nha_pho">Nhà phố</option>
                    <option value="dat_nen">Đất nền</option>
                </select>
                <span class="fake-placeholder">Chọn Loại tin BĐS <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" name="phap_ly" required>
                    <option value="" disabled selected></option>
                    <option value="co_so">Có sổ</option>
                    <option value="khong_so">Không sổ</option>
                </select>
                <span class="fake-placeholder">Pháp lý <span class="required-star">*</span></span>
            </div>
            <div class="form-section-title">Diện tích & giá</div>

            <div class="form-group input-with-unit">
                <div style="position: relative; flex: 1;">
                    <input type="number" name="dien_tich" class="form-input" placeholder=" " required>
                    <span class="fake-placeholder">Diện tích đất <span class="required-star">*</span></span>
                </div>
                <select class="unit-select" name="don_vi_dien_tich">
                    <option>m²</option>
                    <option>ha</option>
                </select>
            </div>

            <div class="form-row-2col">
                <div class="col-half">
                    <input type="number" name="chieu_ngang" class="form-input" placeholder="Chiều ngang">
                </div>
                <div class="col-half">
                    <input type="number" name="chieu_dai" class="form-input" placeholder="Chiều dài">
                </div>
            </div>

            <div class="form-group" style="position: relative;">
                <select class="form-input" name="so_tang">
                    <option value="" disabled selected></option>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <span class="fake-placeholder">Số tầng</span>
            </div>

            <div class="form-group input-with-unit">
                <div style="position: relative; flex: 1;">
                    <input type="number" name="gia_chao" class="form-input" placeholder=" ">
                    <span class="fake-placeholder">Giá chào</span>
                </div>
                <select class="unit-select" name="don_vi_gia">
                    <option>Tỷ</option>
                    <option>VND</option>
                </select>
            </div>

            <div class="form-group">
                <input type="text" name="trich_thuong" class="form-input" placeholder="Trích thưởng">
            </div>

            <div class="form-section-title">Địa chỉ</div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" name="tinh_thanh" required>
                    <option value="" disabled selected></option>
                    <option value="hn">Hà Nội</option>
                    <option value="hcm">TP. Hồ Chí Minh</option>
                </select>
                <span class="fake-placeholder">Tỉnh / Thành <span class="required-star">*</span></span>
            </div>
            <div class="form-group" style="position: relative;">
                <select class="form-input" name="xa_phuong" required>
                    <option value="" disabled selected></option>
                    <option value="phuong1">Phường 1</option>
                    <option value="xa1">Xã A</option>
                </select>
                <span class="fake-placeholder">Xã / Phường <span class="required-star">*</span></span>
            </div>
            <div class="form-group">
                <input type="text" name="dia_chi_chi_tiet" class="form-input" placeholder="Số nhà, tên đường">
            </div>
            <div class="checkbox-row">
                <input type="checkbox" id="showAddress" name="hien_thi_so_nha">
                <label for="showAddress">Hiển thị số nhà</label>
            </div>
            <div class="form-group">
                <textarea class="form-textarea" name="mo_ta" placeholder="Thêm mô tả:"></textarea>
                <div class="char-counter">0/1500 ký tự</div>
            </div>
            <div class="upload-box" onclick="document.getElementById('file-upload').click()">
                <i class="fa-solid fa-camera upload-icon"></i>
                <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                <input type="file" name="media[]" id="file-upload" style="display: none;" accept="image/*,video/*" multiple onchange="previewMedia(this)">
            </div>
            <div id="media-preview-container" style="display: flex; gap: 10px; padding: 0 15px; flex-wrap: wrap; margin-bottom: 15px;"></div>

            <button type="submit" class="btn-submit-blue">XONG</button>

        </form>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
</body>

</html>