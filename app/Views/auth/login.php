<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng nhập - Big Housing Land</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        /* Fix lỗi giao diện bị lắc lư khi vuốt trên mobile */
        html,
        body {
            overscroll-behavior: none;
            /* Tắt hiệu ứng đàn hồi */
            overflow-x: hidden;
            /* Ngăn cuộn ngang */
            width: 100%;
            height: 100%;
            margin: 0;
        }

        .login-page {
            background: linear-gradient(180deg, #0137AE 0%, #158EFF 33%, #000557 100%) !important;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
        }

        .btn-login {
            background-color: #6ADBFD !important;
            /* Màu xanh đậm nổi bật */
            color: #013354 !important;
        }

        .login-options {
            align-items: center;
            /* Căn giữa theo chiều dọc cho cả hàng */
        }

        .login-options label {
            display: flex;
            align-items: center;
            /* Căn giữa checkbox và chữ */
            gap: 5px;
            /* Khoảng cách giữa checkbox và chữ */
        }

        /* Make alerts on the login page more prominent only here */
        .login-page .alert-wrapper {
            max-width: 420px;
            margin: 0;
            padding: 0 8px;
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            z-index: 1000;
        }

        .login-page .alert {
            background: #ffffff;
            color: #071133;
            border-left: 6px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.18);
            border-radius: 12px;
            padding: 8px;
        }

        .login-page .alert-inner {
            gap: 10px;
            padding: 12px 14px;
            align-items: center;
        }

        .login-page .alert-icon {
            font-size: 20px;
            width: 30px;
            margin-top: 0;
        }

        .login-page .alert-message {
            font-size: 15px;
            font-weight: 700;
            color: #071133;
        }

        .login-page .alert-close {
            color: rgba(7, 17, 51, 0.45);
        }

        /* Slightly stronger colors per type on login */
        .login-page .alert--error {
            border-left-color: #DC2626;
        }

        .login-page .alert--success {
            border-left-color: #16A34A;
        }

        .login-page .alert--warning {
            border-left-color: #D97706;
        }

        .login-page .alert--info {
            border-left-color: #2563EB;
        }
    </style>
</head>

<body>
    <div class="app-container login-page">
        <?php require_once __DIR__ . '/../partials/alert.php'; ?>
        <div class="login-header">
            <div class="logo-icon">
                <img src="images/Logo.png" alt="logo">
            </div>
            <div class="logo-login">
                <img src="images/toanha1.png" alt="login">
            </div>
            <div class="logo-login">
                <img src="images/toanha2.png" alt="login">
            </div>
        </div>

        <form action="<?= BASE_URL ?>/login" method="POST" class="login-form">

            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" class="input-field" name="identity" placeholder="Số điện thoại" value="<?= htmlspecialchars($identity ?? '') ?>">
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" class="input-field" name="password" placeholder="Mật khẩu" value="<?= htmlspecialchars($password ?? '') ?>">
                <i class="fa-regular fa-eye-slash toggle-password"></i>
            </div>
            <div class="login-options">
                <label><input type="checkbox" name="remember" <?= !empty($remember) ? 'checked' : '' ?>> Lưu mật khẩu</label>
                <a href="<?= BASE_URL ?>/forgot-password">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
            <div class="register-link-wrapper">
                <a href="<?= BASE_URL ?>/register" class="register-link">Đăng ký tài khoản</a>
            </div>
        </form>

        <div class="city-bg"></div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = togglePassword.previousElementSibling; // Lấy ô input ngay trước icon

        togglePassword.addEventListener('click', function() {
            // Chuyển đổi thuộc tính type giữa 'password' và 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Đổi icon từ mắt gạch chéo sang mắt mở và ngược lại
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
    <script>
        // If server set the 'bh_clear_form' cookie after successful registration,
        // clear the saved register form in localStorage so old inputs don't persist.
        (function() {
            try {
                const cookies = document.cookie.split(';').map(c => c.trim());
                const found = cookies.find(c => c.indexOf('bh_clear_form=') === 0);
                if (found) {
                    localStorage.removeItem('bh_register_v1');
                    // delete cookie
                    document.cookie = 'bh_clear_form=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT';
                }
            } catch (e) {
                // ignore
            }
        })();
    </script>
</body>

</html>