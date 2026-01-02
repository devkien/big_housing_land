<?php
$currentUser = $_SESSION['user'] ?? [];
$viTri = isset($currentUser['vi_tri']) ? (int)$currentUser['vi_tri'] : 0;
$resourceLink = BASE_URL . '/management-resource';
if ($viTri === 1) {
    $resourceLink = BASE_URL . '/management-resource-rent';
} elseif ($viTri === 2) {
    $resourceLink = BASE_URL . '/management-resource-sum';
}
?>
<div class="grid-menu">
    <div class="menu-item" onclick="window.location.href='<?= $resourceLink ?>'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/tabler_home-check.svg" alt=""></div>
        <span>Kho tài<br>nguyên</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/collection'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/icon-park-outline_bookmark.svg" alt=""></div>
        <span>Bộ sưu tập</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/auto-match'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/ant-design_user-switch-outlined.svg" alt=""></div>
        <span>QL khách-<br>Tự khớp</span>
    </div>

    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/report_list'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/ic_round-bar-chart.svg" alt=""></div>
        <span>Báo cáo<br>dẫn khách</span>
    </div>
    <div class="menu-item" onclick="window.location.href='<?= BASE_URL ?>/notification'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/mdi_cart-outline.svg" alt=""></div>
        <span>Thông báo<br>vụ chốt</span>
    </div>
    <div class=" menu-item" onclick="window.location.href='<?= BASE_URL ?>/policy'">
        <div class="icon-box"><img src="<?= BASE_URL ?>/icon/mdi_scale-balance.svg" alt=""></div>
        <span>Quy định và<br>hướng dẫn</span>
    </div>
</div>