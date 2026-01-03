<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Danh mục tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        /* CSS cho Modal xác nhận xóa tài khoản */
        .modal-confirm-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-confirm-box {
            background: white;
            width: 90%;
            max-width: 320px;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .modal-confirm-title {
            font-size: 16px;
            font-weight: 600;
            color: #3b5998; /* Màu xanh tiêu đề */
            margin-bottom: 10px;
        }

        .modal-confirm-desc {
            font-size: 14px;
            color: #333;
            margin-bottom: 20px;
        }

        .modal-confirm-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-confirm {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 14px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-confirm-agree {
            background-color: #3b5998;
            color: white;
            border: none;
        }

        .btn-confirm-cancel {
            background-color: white;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .btn-confirm-cancel:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: #F9F9F9;">

        <div class="page-big-title">Danh mục tài khoản</div>

        <div class="profile-card-banner" onclick="window.location.href='<?= BASE_URL ?>/superadmin/detailprofile'">
            <?php
            $avatarSrc = rtrim(BASE_URL, '/') . '/icon/menuanhdaidien.png';
            if (!empty($user['avatar'])) {
                $avatar = $user['avatar'];
                if (strpos($avatar, 'http') === 0 || strpos($avatar, '/') === 0) {
                    $avatarSrc = $avatar;
                } else {
                    $avatarSrc = BASE_URL . '/' . $avatar;
                }
            }
            ?>
            <img src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>" class="profile-avatar-large" onerror="this.onerror=null;this.src='<?= rtrim(BASE_URL, '/') ?>/icon/menuanhdaidien.png';">
            <div class="profile-info">
                <h3><?php echo isset($user['ho_ten']) ? htmlspecialchars($user['ho_ten'], ENT_QUOTES, 'UTF-8') : '---'; ?></h3>
                <div class="profile-role">Cấp quản lý</div>
                <div class="office-badge">TRỤ SỞ - HÀ NỘI</div>
            </div>
            <i class="fa-solid fa-chevron-right arrow-right-absolute"></i>
        </div>

        <div class="quick-access-grid">
            <a href="<?= BASE_URL ?>/superadmin/management-resource" class="quick-card">
                <i class="fa-solid fa-house-chimney quick-icon"></i>
                <span class="quick-text">Kho tài nguyên</span>
            </a>
            <div class="quick-card">
                <i class="fa-regular fa-clipboard quick-icon"></i>
                <span class="quick-text">Quy định và hướng dẫn</span>
            </div>
            <a href="<?= BASE_URL ?>/superadmin/notification" class="quick-card">
                <i class="fa-solid fa-money-bill-1-wave quick-icon"></i>
                <span class="quick-text">Thông báo vụ chốt</span>
            </a>
            <a href="<?= BASE_URL ?>/superadmin/report-list" class="quick-card">
                <i class="fa-solid fa-chart-simple quick-icon"></i>
                <span class="quick-text">Báo cáo dẫn khách</span>
            </a>
        </div>

        <div class="settings-group">
            <div class="setting-item-header">
                <div class="setting-left">
                    <i class="fa-solid fa-gear setting-icon"></i> Cài đặt
                </div>
                <i class="fa-solid fa-chevron-up" style="font-size:12px;"></i>
            </div>

            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/superadmin/changepassword'">
                <span>Đổi mật khẩu</span>
                <i class="fa-solid fa-chevron-right" style="font-size:12px; color:#999;"></i>
            </div>

            <div class="sub-setting-item" onclick="document.getElementById('modal-delete-account').style.display='flex'">
                <span style="color: #dc3545;">Xóa tài khoản</span>
                <i class="fa-solid fa-chevron-right" style="font-size:12px; color:#999;"></i>
            </div>
        </div>

        <div class="settings-group">
            <div class="setting-item-header">
                <div class="setting-left">
                    <i class="fa-solid fa-shield-halved setting-icon"></i> Điều khoản & chính sách
                </div>
                <i class="fa-solid fa-chevron-up" style="font-size:12px;"></i>
            </div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/superadmin/terms-service'">Điều khoản dịch vụ</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/superadmin/privacy-policy'">Chính sách bảo mật</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/superadmin/payment-policy'">Chính sách hoàn tiền/đổi trả</div>
            <div class="sub-setting-item" onclick="window.location.href='<?= BASE_URL ?>/superadmin/cookie-policy'">Chính sách Cookie</div>
        </div>
        <button class="btn-logout" onclick="window.location.href='<?= BASE_URL ?>/superadmin/logout'">Đăng xuất</button>
        <div style="height: 60px;"></div>
        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>
    </div>

    <div id="modal-delete-account" class="modal-confirm-overlay">
        <div class="modal-confirm-box">
            <div class="modal-confirm-title">Xoá tài khoản</div>
            <div class="modal-confirm-desc">Bạn chắc chắn sẽ xoá tài khoản này?</div>
            <div class="modal-confirm-actions">
                <button class="btn-confirm btn-confirm-agree" onclick="window.location.href='<?= BASE_URL ?>/superadmin/logout'">Đồng ý</button>
                <button class="btn-confirm btn-confirm-cancel" onclick="document.getElementById('modal-delete-account').style.display='none'">Huỷ bỏ</button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/script.js"></script>
</body>

</html>