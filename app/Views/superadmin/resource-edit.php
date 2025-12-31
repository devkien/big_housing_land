<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tin đăng</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">
        <header class="detail-header">
            <a href="<?= BASE_URL ?>/superadmin/management-resource" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Sửa tin đăng</div>
            <div class="header-icon-btn"></div>
        </header>

        <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
        <form action="<?= BASE_URL ?>/superadmin/management-resource-edit?id=<?= $property['id'] ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $property['id'] ?>">

            <div class="post-form-scroll" style="padding-bottom: 80px;">
                <div class="alert-wrapper">
                    <?php require_once __DIR__ . '/../partials/alert.php'; ?>
                </div>

                <div class="form-section-title">Thông tin BĐS</div>
                <div class="form-group" style="position: relative;">
                    <input type="text" name="tieu_de" class="form-input" placeholder=" " required value="<?= htmlspecialchars($property['tieu_de'] ?? '') ?>">
                    <span class="fake-placeholder">Tiêu đề <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="loai_bds" class="form-input" required>
                        <option value="" disabled <?= empty($property['loai_bds']) ? 'selected' : '' ?>></option>
                        <option value="ban" <?= ($property['loai_bds'] ?? '') == 'ban' ? 'selected' : '' ?>>Bán</option>
                        <option value="cho_thue" <?= ($property['loai_bds'] ?? '') == 'cho_thue' ? 'selected' : '' ?>>Cho thuê</option>
                    </select>
                    <span class="fake-placeholder">Chọn Loại tin BĐS <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="phap_ly" id="phap_ly_select" class="form-input" required>
                        <option value="" disabled <?= empty($property['phap_ly']) ? 'selected' : '' ?>></option>
                        <option value="co_so" <?= ($property['phap_ly'] ?? '') == 'co_so' ? 'selected' : '' ?>>Có sổ</option>
                        <option value="khong_so" <?= ($property['phap_ly'] ?? '') == 'khong_so' ? 'selected' : '' ?>>Không sổ</option>
                    </select>
                    <span class="fake-placeholder">Pháp lý <span class="required-star">*</span></span>
                </div>
                <div class="form-group" id="ma-so-so-group" style="display: <?= ($property['phap_ly'] ?? '') == 'co_so' ? 'block' : 'none' ?>; position: relative;">
                    <input type="text" name="ma_so_so" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['ma_so_so'] ?? '') ?>">
                    <span class="fake-placeholder">Mã số sổ <span class="required-star">*</span></span>
                </div>
                <div class="form-section-title">Diện tích & giá <span class="required-star">*</span></div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="dien_tich" step="any" class="form-input" placeholder=" " required value="<?= htmlspecialchars($property['dien_tich'] ?? '') ?>">
                        <span class="fake-placeholder">Diện tích đất</span>
                    </div>
                    <select name="don_vi_dien_tich" class="unit-select">
                        <option <?= ($property['don_vi_dien_tich'] ?? '') == 'm²' ? 'selected' : '' ?>>m²</option>
                        <option <?= ($property['don_vi_dien_tich'] ?? '') == 'ha' ? 'selected' : '' ?>>ha</option>
                    </select>
                </div>

                <div class="form-row-2col">
                    <div class="col-half">
                        <input type="number" name="chieu_rong" step="any" class="form-input" placeholder="Chiều ngang" value="<?= htmlspecialchars($property['chieu_rong'] ?? '') ?>">
                    </div>
                    <div class="col-half">
                        <input type="number" name="chieu_dai" step="any" class="form-input" placeholder="Chiều dài" value="<?= htmlspecialchars($property['chieu_dai'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" style="position: relative;">
                    <select name="so_tang" class="form-input" required>
                        <option value="" disabled <?= empty($property['so_tang']) ? 'selected' : '' ?>></option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?= $i ?>" <?= ($property['so_tang'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                    <span class="fake-placeholder">Số tầng</span>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="gia_chao" step="any" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['gia_chao'] ?? '') ?>">
                        <span class="fake-placeholder">Giá chào</span>
                    </div>
                    <select name="don_vi_gia" class="unit-select">
                        <option value="nguyen_can" <?= ($property['don_vi_gia'] ?? '') == 'nguyen_can' ? 'selected' : '' ?>>Nguyên căn</option>
                        <option value="m2" <?= ($property['don_vi_gia'] ?? '') == 'm2' ? 'selected' : '' ?>>m²</option>
                    </select>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="trich_thuong_gia_tri" class="form-input" placeholder=" " value="<?= htmlspecialchars($property['trich_thuong_gia_tri'] ?? '') ?>">
                        <span class="fake-placeholder">Trích thưởng</span>
                    </div>
                    <select name="trich_thuong_don_vi" class="unit-select">
                        <option <?= ($property['trich_thuong_don_vi'] ?? '') == '%' ? 'selected' : '' ?>>%</option>
                        <option <?= ($property['trich_thuong_don_vi'] ?? '') == 'VND' ? 'selected' : '' ?>>VND</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="dia_chi_chi_tiet" class="form-input" placeholder="Số nhà, tên đường" value="<?= htmlspecialchars($property['dia_chi_chi_tiet'] ?? '') ?>">
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="showAddress" name="is_visible" value="1" <?= !empty($property['is_visible']) ? 'checked' : '' ?>>
                    <label for="showAddress">Hiển thị số nhà</label>
                </div>
                <div class="form-group">
                    <textarea name="mo_ta" class="form-textarea" placeholder="Thêm mô tả:"><?= htmlspecialchars($property['mo_ta'] ?? '') ?></textarea>
                    <div class="char-counter">0/1500 ký tự</div>
                </div>
                <div class="upload-box" onclick="document.getElementById('file-upload').click()">
                    <i class="fa-solid fa-camera upload-icon"></i>
                    <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                    <input type="file" id="file-upload" name="media[]" style="display: none;" accept="image/*,video/*" multiple onchange="previewMedia(this)">
                </div>
                <div id="media-preview-container" style="display: flex; gap: 10px; padding: 0 15px; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px; margin-bottom: 15px;"></div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" class="form-input">
                        <option value="ban_manh" <?= ($property['trang_thai'] ?? '') == 'ban_manh' ? 'selected' : '' ?>>Bán mạnh</option>
                        <option value="tam_dung_ban" <?= ($property['trang_thai'] ?? '') == 'tam_dung_ban' ? 'selected' : '' ?>>Tạm dừng bán</option>
                        <option value="dung_ban" <?= ($property['trang_thai'] ?? '') == 'dung_ban' ? 'selected' : '' ?>>Dừng bán</option>
                        <option value="da_ban" <?= ($property['trang_thai'] ?? '') == 'da_ban' ? 'selected' : '' ?>>Đã bán</option>
                        <option value="tang_chao" <?= ($property['trang_thai'] ?? '') == 'tang_chao' ? 'selected' : '' ?>>Tăng chào</option>
                        <option value="ha_chao" <?= ($property['trang_thai'] ?? '') == 'ha_chao' ? 'selected' : '' ?>>Hạ chào</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit-blue">LƯU THAY ĐỔI</button>
            </div>
        </form>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phapLySelect = document.getElementById('phap_ly_select');
            const maSoGroup = document.getElementById('ma-so-so-group');

            if (phapLySelect) {
                phapLySelect.addEventListener('change', function() {
                    if (this.value === 'co_so') {
                        maSoGroup.style.display = 'block';
                    } else {
                        maSoGroup.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>

</html>