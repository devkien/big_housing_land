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
            <a href="<?= BASE_URL ?>/superadmin/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Đăng tin</div>
            <div class="header-icon-btn"></div>
        </header>

        <?php require_once __DIR__ . '/../../Helpers/functions.php'; ?>
        <form action="<?= BASE_URL ?>/superadmin/management-resource-post" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="post-form-scroll" style="padding-bottom: 80px;">
                <div class="alert-wrapper">
                    <?php require_once __DIR__ . '/../partials/alert.php'; ?>
                </div>

                <div class="form-section-title">Người đăng</div>
                <div class="form-group">
                    <?php $currentName = \Auth::user()['ho_ten'] ?? ''; ?>
                    <input type="text" name="ho_ten" class="form-input focus-blue" value="<?= htmlspecialchars($currentName ?: 'Họ tên đầu chủ', ENT_QUOTES, 'UTF-8') ?>" <?= $currentName ? 'readonly' : '' ?>>
                </div>
                <div class="form-group">
                    <select class="form-input" name="phong_ban">
                        <option value="">Chọn Phòng Ban</option>
                        <option value="thien_chien">Thiện Chiến</option>
                        <option value="hung_phat">Hùng Phát</option>
                        <option value="tinh_nhue">Tinh Nhuệ</option>
                    </select>
                </div>

                <div class="form-section-title">Thông tin BĐS</div>
                <div class="form-group" style="position: relative;">
                    <input type="text" name="tieu_de" class="form-input" placeholder=" " required>
                    <span class="fake-placeholder">Tiêu đề <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="loai_bds" class="form-input" required>
                        <option value="" disabled selected></option>
                        <option value="ban">Bán</option>
                        <option value="cho_thue">Cho thuê</option>
                    </select>
                    <span class="fake-placeholder">Chọn Loại tin BĐS <span class="required-star">*</span></span>
                </div>
                <div class="form-group" style="position: relative;">
                    <select name="phap_ly" class="form-input" required>
                        <option value="" disabled selected></option>
                        <option value="co_so">Có sổ</option>
                        <option value="khong_so">Không sổ</option>
                    </select>
                    <span class="fake-placeholder">Pháp lý <span class="required-star">*</span></span>
                </div>
                <div class="form-group" id="ma-so-so-group" style="display: none; position: relative;">
                    <input type="text" name="ma_so_so" class="form-input" placeholder=" ">
                    <span class="fake-placeholder">Mã số sổ <span class="required-star">*</span></span>
                </div>
                <div class="form-section-title">Diện tích & giá <span class="required-star">*</span></div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="dien_tich" step="any" class="form-input" placeholder=" " required>
                        <span class="fake-placeholder">Diện tích đất</span>
                    </div>
                    <select name="don_vi_dien_tich" class="unit-select">
                        <option>m²</option>
                        <option>ha</option>
                    </select>
                </div>

                <div class="form-row-2col">
                    <div class="col-half">
                        <input type="number" name="chieu_rong" step="any" class="form-input" placeholder="Chiều ngang">
                    </div>
                    <div class="col-half">
                        <input type="number" name="chieu_dai" step="any" class="form-input" placeholder="Chiều dài">
                    </div>
                </div>

                <div class="form-group" style="position: relative;">
                    <select name="so_tang" class="form-input" required>
                        <option value="" disabled selected></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>
                    <span class="fake-placeholder">Số tầng</span>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="number" name="gia_chao" step="any" class="form-input" placeholder=" ">
                        <span class="fake-placeholder">Giá chào</span>
                    </div>
                    <select name="don_vi_gia" class="unit-select">
                        <option value="nguyen_can">Nguyên căn</option>
                        <option value="m2">m²</option>
                    </select>
                </div>

                <div class="form-group input-with-unit">
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="trich_thuong_gia_tri" class="form-input" placeholder=" ">
                        <span class="fake-placeholder">Trích thưởng</span>
                    </div>
                    <select name="trich_thuong_don_vi" class="unit-select">
                        <option>%</option>
                        <option>VND</option>
                    </select>
                </div>
                <div class="form-section-title">Địa chỉ</div>
                <div class="form-group" style="position: relative;">
                    <select id="select-province" name="tinh_thanh" class="form-input" required>
                        <option value="" disabled selected>-- Chọn Tỉnh / Thành --</option>
                    </select>

                </div>
                <div class="form-group" style="position: relative;">
                    <select id="select-ward" name="xa_phuong" class="form-input">
                        <option value="" disabled selected>-- Chọn Xã / Phường (nếu có) --</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="dia_chi_chi_tiet" class="form-input" placeholder="Số nhà, tên đường">
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="showAddress" name="is_visible" value="1" checked>
                    <label for="showAddress">Hiển thị số nhà</label>
                </div>
                <div class="form-group">
                    <textarea name="mo_ta" class="form-textarea" placeholder="Thêm mô tả:"></textarea>
                    <div class="char-counter">0/1500 ký tự</div>
                </div>
                <div class="upload-box" onclick="document.getElementById('file-upload').click()">
                    <i class="fa-solid fa-camera upload-icon"></i>
                    <div class="upload-text"><i class="fa-solid fa-plus"></i> Tải hình ảnh/video</div>
                    <input type="file" id="file-upload" name="media[]" style="display: none;" accept="image/*,video/*" multiple onchange="previewMedia(this)">
                </div>
                <div id="media-preview-container" style="display: flex; gap: 10px; padding: 0 15px; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 10px; margin-bottom: 15px;"></div>

                <button type="submit" class="btn-submit-blue">ĐĂNG NGAY</button>

            </div>
        </form>

    </div>

    <div id="bottom-nav-container">
        <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
    </div>

    </div>

    <script>
        (function() {
            // Prefer application BASE_URL -> /{base}/api/locations.php
            const urls = [
                '<?= BASE_URL ?>' + '/api/locations.php',
                '<?= BASE_URL ?>' + '/public/api/locations.php',
                '/api/locations.php',
                'api/locations.php'
            ];

            const $prov = document.getElementById('select-province');
            const $ward = document.getElementById('select-ward');

            function clearSelect(el, placeholder) {
                el.innerHTML = '';
                const o = document.createElement('option');
                o.value = '';
                o.disabled = true;
                o.selected = true;
                o.textContent = placeholder || '-- Chọn --';
                el.appendChild(o);
            }

            function populateProvinces(data) {
                clearSelect($prov, '-- Chọn Tỉnh / Thành --');
                Object.keys(data).forEach(slug => {
                    const opt = document.createElement('option');
                    opt.value = slug;
                    opt.textContent = data[slug].name || slug;
                    $prov.appendChild(opt);
                });
            }

            function populateWards(data, provSlug) {
                clearSelect($ward, '-- Chọn Xã / Phường (nếu có) --');
                if (!provSlug || !data[provSlug]) return;
                const districts = data[provSlug].districts || {};
                // Aggregate all wards (xã/phường) from every district under the province
                const allWards = [];
                Object.keys(districts).forEach(dslug => {
                    const wards = districts[dslug].wards || [];
                    wards.forEach(w => allWards.push(w));
                });
                // Remove duplicates by id/name if any, then populate
                const seen = new Set();
                allWards.forEach(w => {
                    const id = w.id || w.name;
                    if (seen.has(id)) return;
                    seen.add(id);
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = w.name || id;
                    $ward.appendChild(opt);
                });
            }

            function tryFetch(index) {
                if (index >= urls.length) return console.warn('Locations API not found');
                fetch(urls[index]).then(r => {
                    if (!r.ok) throw new Error('fetch failed');
                    return r.json();
                }).then(data => {
                    if (!data || Object.keys(data).length === 0) return console.warn('Locations data empty');
                    window._locationsData = data;
                    populateProvinces(data);
                    // If a province is already selected (e.g., preserved value), populate wards immediately
                    if ($prov.value) populateWards(window._locationsData || {}, $prov.value);
                }).catch(() => tryFetch(index + 1));
            }

            $prov.addEventListener('change', function() {
                const prov = this.value;
                populateWards(window._locationsData || {}, prov);
            });

            // init
            tryFetch(0);

            // Toggle Mã số sổ input when Pháp lý changes
            const phapLySelect = document.querySelector('select[name="phap_ly"]');
            const maSoGroup = document.getElementById('ma-so-so-group');
            const maSoInput = maSoGroup ? maSoGroup.querySelector('input[name="ma_so_so"]') : null;

            function toggleMaSo() {
                if (!phapLySelect || !maSoGroup || !maSoInput) return;
                const show = (phapLySelect.value === 'co_so');
                maSoGroup.style.display = show ? 'block' : 'none';
                maSoInput.required = show;
            }
            if (phapLySelect) {
                phapLySelect.addEventListener('change', toggleMaSo);
                // initial state
                toggleMaSo();
            }
        })();
    </script>

</body>

</html>