<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chỉnh sửa hồ sơ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <div class="edit-header">
            Chỉnh sửa hồ sơ
        </div>

        <?php $user = $user ?? ($_SESSION['user'] ?? null); ?>

        <?php require_once __DIR__ . '/../partials/alert.php'; ?>

        <?php
        // Normalize avatar path: support full URL, root-relative path, or stored relative path
        $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
        $avatar = $user['avatar'] ?? null;
        if (!empty($avatar)) {
            $trim = ltrim($avatar);
            if (stripos($trim, 'http') === 0) {
                $avatarSrc = $trim;
            } elseif (strpos($trim, '/') === 0) {
                $avatarSrc = rtrim(BASE_URL, '/') . $trim;
            } else {
                $avatarSrc = rtrim(BASE_URL, '/') . '/' . ltrim($trim, '/');
            }
        }
        ?>

        <div class="profile-card-banner">
            <div class="avatar-upload-wrapper" style="display:inline-block;position:relative;">
                <img id="currentAvatar" src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar-large" style="cursor:pointer;" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
                <div class="avatar-overlay" style="position:absolute;bottom:4px;left:43%;transform:translateX(-50%);pointer-events:none;">
                    <img src="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/public/icon/Vector.svg', ENT_QUOTES, 'UTF-8'); ?>" alt="avatar-icon" style="width:36px;height:36px;display:block;object-fit:contain;">
                </div>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['ho_ten'] ?? 'Người dùng'); ?></h3>
                <div class="profile-role">Cấp người dùng</div>
                <div class="office-badge"><?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>
        <form action="<?php echo BASE_URL; ?>/editprofile" method="post" enctype="multipart/form-data">

            <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>1. Họ và tên</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($user['ho_ten'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>2. Số điện thoại</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>3. Email</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>4. Ngày sinh</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="text" name="nam_sinh" value="<?php echo htmlspecialchars($user['nam_sinh'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>5. Căn Cước công dân</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-regular fa-id-card"></i>
                    <input type="text" name="so_cccd" value="<?php echo htmlspecialchars($user['so_cccd'] ?? ''); ?>">
                </div>
            </div>

            <div class="edit-form-group">
                <div class="edit-label-row">
                    <span>6. Địa chỉ thường trú</span>
                    <span class="counter-text">0/10</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?>">
                </div>
                <div class="counter-text" style="text-align: right; margin-top: 5px;">0/10</div>
            </div>

            <button class="btn-save-change">Lưu thay đổi</button>

        </form>

        <div style="height: 60px;"></div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>
    <script>
        (function() {
            const avatar = document.getElementById('currentAvatar');
            const input = document.getElementById('avatarInput');
            if (!avatar || !input) return;
            avatar.addEventListener('click', function() {
                input.click();
            });
            input.addEventListener('change', function(e) {
                const f = e.target.files && e.target.files[0];
                if (!f) return;
                const url = URL.createObjectURL(f);
                avatar.src = url;
            });
        })();
    </script>
</body>

</html>