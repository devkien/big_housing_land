<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đổi mật khẩu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</head>

<body>
    <div class="app-container" style="background: white;">

        <div class="header-title-left">
            Đổi mật khẩu
        </div>
        <?php $user = $user ?? ($_SESSION['user'] ?? null); ?>
        <div class="profile-card-banner" onclick="window.location.href='<?= BASE_URL ?>/detailprofile'">
            <?php
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
            <img src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar-large" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
            <div class="profile-info">
                <h3><?= htmlspecialchars($user['ho_ten'] ?? ($user['ten'] ?? 'Người dùng')) ?></h3>
                <?php
                $roleLabel = 'Cấp đầu khách';
                if (isset($user['quyen']) && $user['quyen'] === 'admin') {
                    $roleLabel = 'Cấp đầu chủ';
                }
                ?>
                <div class="profile-role"><?= $roleLabel ?></div>
                <div class="office-badge"><?= htmlspecialchars($officeBadge ?? 'TRỤ SỞ - HÀ NỘI') ?></div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <div class="alert-wrapper">
            <?php require_once __DIR__ . '/../partials/alert.php'; ?>
        </div>

        <form method="post" action="<?= BASE_URL ?>/changepassword">

            <div class="edit-form-group" style="margin-bottom: 20px;">
                <div class="edit-label-row">
                    <span>1. Mật khẩu hiện tại</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại">
                </div>
                <div class="counter-text" style="text-align: right; margin-top: 5px;">0/10</div>
            </div>

            <div class="edit-form-group" style="margin-bottom: 20px;">
                <div class="label-row-with-counter">
                    <span>2. Mật khẩu mới</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" name="new_password" placeholder="Nhập mật khẩu mới">
                </div>
                <div class="counter-text" style="text-align: right; margin-top: 5px;">0/10</div>
            </div>
            <div class="edit-form-group" style="margin-bottom: 20px;">
                <div class="label-row-with-counter">
                    <span>3. Xác nhận lại mật khẩu</span>
                </div>
                <div class="edit-input-box">
                    <i class="fa-solid fa-check-double"></i>
                    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới">
                </div>
                <div class="counter-text" style="text-align: right; margin-top: 5px;">0/10</div>
            </div>

            <button class="btn-save-change" type="submit">Lưu thay đổi</button>

        </form>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

</body>

</html>