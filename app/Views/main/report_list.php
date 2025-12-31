<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <title>Báo cáo dẫn khách</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
    <style>
        /* Form section heading */
        .form-section-heading {
            font-weight: 700;
            margin: 18px 0 10px;
            color: #0f172a;
            font-size: 14px;
        }

        /* Wrapper for each row */
        .edit-form-group {
            margin-bottom: 14px;
        }

        .edit-label-row {
            margin-bottom: 6px;
            color: var(--text-black, #111827);
            font-size: 13px;
        }

        /* Input box with icon */
        .edit-input-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid #e6e9ee;
            border-radius: 8px;
            background: #ffffff;
        }

        .edit-input-box i {
            color: #4b5563;
            font-size: 16px;
            flex-shrink: 0;
        }

        .edit-input-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 6px 0;
            font-size: 14px;
            background: transparent;
            color: #0f172a;
        }

        .edit-input-box input[readonly] {
            color: #6b7280;
        }

        /* Textarea: lighter, more professional */
        .textarea-gray-bg {
            width: 100%;
            min-height: 120px;
            background-color: #f3f4f6;
            /* nhẹ, dễ nhìn */
            color: #0f172a;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e6e9ee;
            resize: vertical;
            font-size: 14px;
        }

        .textarea-gray-bg::placeholder {
            color: #9ca3af;
        }

        /* Upload box adjustments */
        .upload-report-box {
            width: 100%;
            max-width: 520px;
            height: 150px;
            border: 1px dashed #c7d2fe;
            background-color: #ffffff;
            border-radius: 8px;
            position: relative;
            cursor: pointer;
            margin-top: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
            padding: 10px;
            box-shadow: 0 2px 6px rgba(2, 6, 23, 0.04);
        }

        .upload-hint-text {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 12px;
            color: #2563eb;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .camera-wrapper {
            position: relative;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-camera-big {
            font-size: 44px;
        }

        .icon-plus-small {
            position: absolute;
            top: -6px;
            right: -8px;
            font-size: 18px;
            background: white;
            border-radius: 50%;
        }

        /* Submit button */
        .btn-submit-blue {
            display: block;
            width: 100%;
            max-width: 520px;
            margin: 10px auto 36px;
            padding: 12px 16px;
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(3, 51, 204, 0.14);
        }

        @media (max-width: 480px) {
            .edit-input-box {
                padding: 8px;
            }

            .form-section-heading {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: white;">

        <header class="detail-header">
            <a href="<?= BASE_URL ?>/home" class="header-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="detail-title">Báo cáo dẫn khách</div>
            <div style="width: 20px;"></div>
        </header>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <form method="POST" action="<?= BASE_URL ?>/report_list" enctype="multipart/form-data" style="padding: 0 15px 80px 15px;">

            <div class="form-section-heading">Thông tin người dẫn khách</div>

            <div class="edit-form-group">
                <div class="edit-label-row"><span>1. Họ và tên</span></div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" value="<?= isset($user['ho_ten']) ? htmlspecialchars($user['ho_ten']) : '' ?>" readonly style="background-color: #e9ecef;">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>2. Số điện thoại</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" value="<?= isset($user['so_dien_thoai']) ? htmlspecialchars($user['so_dien_thoai']) : '' ?>" readonly style="background-color: #e9ecef;">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>3. Ghi chú</span>
                </div>
                <textarea class="textarea-gray-bg" name="ghi_chu_nguoi_dan" placeholder="Nhập ghi chú..."></textarea>
            </div>


            <div class="form-section-heading">Thông tin khách:</div>

            <div class="edit-form-group">
                <div class="edit-label-row"><span>1. Họ và tên</span></div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="ho_ten" placeholder="Nhập họ và tên khách" required>
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>2. Năm sinh</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="text" name="nam_sinh_khach" placeholder="Nhập năm sinh">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>3. Số điện thoại</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="so_dien_thoai" placeholder="Nhập số điện thoại" required>
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>4. Căn Cước công dân</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-id-card"></i>
                    <input type="text" name="cccd_khach" placeholder="Nhập số CCCD">
                </div>
            </div>

            <div class="upload-report-box" id="upload-box-report">
                <div class="upload-hint-text">
                    <i class="fa-solid fa-circle-info"></i> Tải tối đa 3 hình ảnh
                </div>

                <div id="upload-initial-view">
                    <div class="camera-wrapper">
                        <i class="fa-solid fa-camera icon-camera-big"></i>
                        <i class="fa-solid fa-plus icon-plus-small"></i>
                    </div>
                </div>

                <div id="upload-preview-view" style="display: none; width: 100%; height: 100%; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px;"></div>
                <input type="file" name="images[]" id="file-input-report" style="display: none;" accept="image/*" multiple>
            </div>

            <button type="submit" class="btn-submit-blue" style="background-color: #0033cc;">Gửi</button>

        </form>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box-report');
            const fileInput = document.getElementById('file-input-report');
            const initialView = document.getElementById('upload-initial-view');
            const previewView = document.getElementById('upload-preview-view');

            if (uploadBox && fileInput) {
                uploadBox.addEventListener('click', function(e) {
                    if (!e.target.closest('.btn-remove-preview')) {
                        fileInput.click();
                    }
                });

                fileInput.addEventListener('change', function(e) {
                    const files = Array.from(e.target.files);
                    if (files.length > 3) {
                        alert('Vui lòng chỉ chọn tối đa 3 ảnh');
                        this.value = '';
                        return;
                    }

                    if (files.length > 0) {
                        initialView.style.display = 'none';
                        previewView.style.display = 'flex';
                        previewView.innerHTML = '';

                        files.forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(evt) {
                                const imgWrap = document.createElement('div');
                                imgWrap.style.position = 'relative';
                                imgWrap.style.width = '60px';
                                imgWrap.style.height = '60px';

                                const img = document.createElement('img');
                                img.src = evt.target.result;
                                img.style.width = '100%';
                                img.style.height = '100%';
                                img.style.objectFit = 'cover';
                                img.style.borderRadius = '4px';
                                img.style.border = '1px solid #ddd';

                                const btnRemove = document.createElement('div');
                                btnRemove.className = 'btn-remove-preview';
                                btnRemove.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                                btnRemove.style.cssText = 'position: absolute; top: -5px; right: -5px; width: 18px; height: 18px; background: red; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer;';

                                btnRemove.onclick = function(ev) {
                                    ev.stopPropagation();
                                    imgWrap.remove();
                                    if (previewView.children.length === 0) {
                                        initialView.style.display = 'flex';
                                        previewView.style.display = 'none';
                                        fileInput.value = '';
                                    }
                                };

                                imgWrap.appendChild(img);
                                imgWrap.appendChild(btnRemove);
                                previewView.appendChild(imgWrap);
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