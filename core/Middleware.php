<?php

require_once __DIR__ . '/Auth.php';

class Middleware
{
    // $spec examples: 'auth' or 'role:admin' or 'role:admin,super_admin'
    public static function handle($spec)
    {
        $parts = explode(':', $spec, 2);
        $name = trim($parts[0]);
        $arg = $parts[1] ?? null;

        if ($name === 'auth') {
            if (!Auth::check()) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            return true;
        }

        if ($name === 'role') {
            if (!$arg) return false;
            $roles = array_map('trim', explode(',', $arg));
            if (!Auth::inRoles($roles)) {
                $_SESSION['error'] = 'Bạn không có quyền truy cập';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            return true;
        }

        // Unknown middleware - allow by default
        return true;
    }
}
