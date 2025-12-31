<?php

class Auth
{
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    // Kiểm tra user có thuộc 1 trong các role truyền vào hay không
    public static function inRoles(array $roles)
    {
        $role = self::role();
        if (!$role) return false;
        return in_array($role, $roles, true);
    }

    // Lấy role canonical từ session user (normalize các giá trị tiếng Việt nếu cần)
    public static function role()
    {
        $u = self::user();
        if (!$u) return null;

        // role: loai_tai_khoan, quyen
        // Use `quyen` as the authoritative source for role as requested
        $raw = null;
        if (!empty($u['quyen'])) $raw = $u['quyen'];
        elseif (!empty($u['loai_tai_khoan'])) $raw = $u['loai_tai_khoan'];

        if (!$raw) return null;

        $raw = strtolower($raw);

        // Map các giá trị tiếng Việt sang canonical EN
        $map = [
            'nhan_vien' => 'user',
            'quan_ly' => 'admin',
            'admin' => 'admin',
            'super_admin' => 'super_admin',
            'superadmin' => 'super_admin',
            'user' => 'user'
        ];

        return $map[$raw] ?? $raw;
    }
}
