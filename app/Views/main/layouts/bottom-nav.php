<nav class="bottom-nav">
    <a href="<?= BASE_URL ?>/home" class="nav-item" id="nav-home">
        <img src="<?= BASE_URL ?>/icon/trangchu.svg" alt="">
    </a>
    <a href="<?= BASE_URL ?>/collection" class="nav-item" id="nav-collection">
        <img src="<?= BASE_URL ?>/icon/bosuutap.svg" alt="">
    </a>
    <a href="<?= BASE_URL ?>/info" class="nav-item" id="nav-info">
        <img src="<?= BASE_URL ?>/icon/thongtin.svg" alt="">
    </a>
    <a href="<?= BASE_URL ?>/notification" class="nav-item" id="nav-notify">
        <img src="<?= BASE_URL ?>/icon/thongbao.svg" alt="">
    </a>
    <?php
    if (session_status() === PHP_SESSION_NONE) @session_start();
    // Always prefer the logged-in session user for the bottom-nav avatar.
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
    <a href="<?= BASE_URL ?>/profile" class="nav-item" id="nav-profile">
        <img src="<?= htmlspecialchars($avatarUrl) ?>" style="width:35px; height:35px; border-radius:50%;">
    </a>
</nav>