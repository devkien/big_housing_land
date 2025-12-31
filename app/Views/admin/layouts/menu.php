<?php
$currentUser = $_SESSION['user'] ?? [];
$viTri = isset($currentUser['vi_tri']) ? (int)$currentUser['vi_tri'] : 0;
$resourceLink = BASE_URL . '/admin/management-resource';
if ($viTri === 1) {
    $resourceLink = BASE_URL . '/admin/management-resource-rent';
} elseif ($viTri === 2) {
    $resourceLink = BASE_URL . '/admin/management-resource-sum';
}
?>
<div class="grid-menu">

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/management-resource-post'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Vector (1).png" alt=""></div>
        <span>Đăng tin</span>
    </div>
    <div class="menu-item" onclick="window.location.href='<?= $resourceLink ?>'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Group.png" alt=""></div>
        <span>Kho tài<br>nguyên</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/collection'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Group (1).png" alt=""></div>
        <span>Bộ sưu tập</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/auto-match'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Vector (2).png" alt=""></div>
        <span>QL khách-<br>Tự khớp</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/report_list'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Vector (3).png" alt=""></div>
        <span>Báo cáo<br>dẫn khách</span>
    </div>
    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/notification'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/Vector (4).png" alt=""></div>
        <span>Thông báo<br>vụ chốt</span>
    </div>
    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/admin/policy'">
        <div class="icon-box"><i class="fa-solid fa-scale-balanced"></i></div>
        <span>Quy định và<br>hướng dẫn</span>
    </div>
</div>