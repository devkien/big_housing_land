<nav class="bottom-nav">
    <a href="<?= BASE_URL ?>/superadmin/home" class="nav-item" id="nav-home">
        <img src="<?= BASE_URL ?>/icon/nhanhanha.png" alt="">
    </a>
    <a href="<?= BASE_URL ?>/superadmin/collection" class="nav-item" id="nav-collection">
        <img src="<?= BASE_URL ?>/icon/menubosuutam.png" alt="">
    </a>
    <a href="<?= BASE_URL ?>/superadmin/info" id="nav-info">
        <img src="<?= BASE_URL ?>/icon/menuthongtin.png" alt="">
    </a>
    <a href="<?= BASE_URL ?>/superadmin/notification" class="nav-item" id="nav-notify">
        <img src="<?= BASE_URL ?>/icon/menuthongbao.png" alt="">
    </a>
    <?php
    if (session_status() === PHP_SESSION_NONE) @session_start();
    // Use session user to display the correct avatar (not the viewed profile's $user)
    $sessionUser = $_SESSION['user'] ?? [];
    $avatar = $sessionUser['avatar'] ?? '';
    $avatarUrl = '';
    if (!empty($avatar)) {
        $p = trim($avatar);
        if (stripos($p, 'http://') === 0 || stripos($p, 'https://') === 0) {
            $avatarUrl = $p;
        } elseif (strpos($p, '/') === 0) {
            $avatarUrl = rtrim(BASE_URL, '/') . $p;
        } elseif (strpos($p, 'uploads/') === 0) {
            $avatarUrl = rtrim(BASE_URL, '/') . '/' . $p;
        } else {
            $avatarUrl = rtrim(BASE_URL, '/') . '/uploads/' . ltrim($p, '/');
        }
    } else {
        $avatarUrl = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
    }
    ?>
    <a href="<?= BASE_URL ?>/superadmin/profile" class="nav-item" id="nav-profile">
        <img src="<?= htmlspecialchars($avatarUrl) ?>" style="width:35px; height:35px; border-radius:50%;">
    </a>
</nav>