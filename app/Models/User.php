<?php

class User extends Model
{
    public static function findForLogin($identity)
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE email = ?
               OR so_dien_thoai = ?
               OR so_cccd = ?
            LIMIT 1
        ");
        $stmt->execute([$identity, $identity, $identity]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByEmail($email)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id)
    {
        $db = self::db();
        // Include a calculated field `so_vu_chot` (closed deals count)
        // Use a correlated subquery to avoid GROUP BY and ensure correct count.
        $stmt = $db->prepare(
            "SELECT u.*, (
                SELECT COUNT(*) FROM deal_posts dp WHERE dp.user_id = u.id AND dp.trang_thai = 1
            ) AS so_vu_chot,
            (
                SELECT ROUND(AVG(rating),1) FROM user_ratings ur WHERE ur.rated_user_id = u.id
            ) AS rating,
            (
                SELECT COUNT(*) FROM user_ratings ur2 WHERE ur2.rated_user_id = u.id
            ) AS rating_count
            FROM users u
            WHERE u.id = ?
            LIMIT 1"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && !isset($user['so_vu_chot'])) {
            $user['so_vu_chot'] = 0;
        }
        return $user;
    }

    public static function create($data)
    {
        $db = self::db();
        // The original implementation contained a SQL typo and mismatched
        // placeholders which could cause runtime DB errors. Delegate to the
        // proven `createWithRole` implementation to keep a single source of
        // truth for user creation logic.
        return self::createWithRole($data);
    }

    // ===== CHECK PHONE =====
    public static function findByPhone($phone)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "SELECT id FROM users WHERE so_dien_thoai = ? LIMIT 1"
        );
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }

    // Find user by employee code (ma_nhan_su)
    public static function findByMaNhanSu($code)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM users WHERE ma_nhan_su = ? LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    // ===== CHECK CCCD =====
    public static function findByCCCD($cccd)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT id FROM users WHERE so_cccd = ? LIMIT 1");
        $stmt->execute([$cccd]);
        return $stmt->fetch();
    }

    // ===== UPDATE PROFILE =====
    public static function update($id, $data)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "UPDATE users SET ho_ten = ?, so_dien_thoai = ?, email = ?, nam_sinh = ?, so_cccd = ?, dia_chi = ?, link_fb = ?, updated_at = NOW() WHERE id = ?"
        );

        // Normalize empty strings to NULL for constrained fields to avoid
        // inserting duplicate empty values (MySQL UNIQUE treats empty string
        // as a concrete value). In particular, `so_cccd` may be UNIQUE.
        $ho_ten = isset($data['ho_ten']) && $data['ho_ten'] !== '' ? $data['ho_ten'] : null;
        $so_dien_thoai = isset($data['so_dien_thoai']) && $data['so_dien_thoai'] !== '' ? $data['so_dien_thoai'] : null;
        $email = isset($data['email']) && $data['email'] !== '' ? $data['email'] : null;
        $nam_sinh = isset($data['nam_sinh']) && $data['nam_sinh'] !== '' ? $data['nam_sinh'] : null;
        $so_cccd = isset($data['so_cccd']) && $data['so_cccd'] !== '' ? $data['so_cccd'] : null;
        $dia_chi = isset($data['dia_chi']) && $data['dia_chi'] !== '' ? $data['dia_chi'] : null;
        $link_fb = isset($data['link_fb']) && $data['link_fb'] !== '' ? $data['link_fb'] : null;

        return $stmt->execute([
            $ho_ten,
            $so_dien_thoai,
            $email,
            $nam_sinh,
            $so_cccd,
            $dia_chi,
            $link_fb,
            $id
        ]);
    }

    // Update avatar path for a user. $avatarPath should be relative to the `uploads/` folder
    // e.g. `avatars/12/abc123.jpg` so views can build the URL as BASE_URL . '/uploads/' . $avatar
    public static function updateAvatar($id, $avatarPath)
    {
        $db = self::db();
        // Attempt to remove previous avatar file (if stored as relative path)
        try {
            $stmtOld = $db->prepare('SELECT avatar FROM users WHERE id = ? LIMIT 1');
            $stmtOld->execute([$id]);
            $old = $stmtOld->fetchColumn();
            if ($old && stripos($old, 'http://') !== 0 && stripos($old, 'https://') !== 0) {
                $oldFile = __DIR__ . '/../../public/uploads/' . ltrim($old, '/');
                if (file_exists($oldFile) && is_file($oldFile)) @unlink($oldFile);
            }
        } catch (Exception $e) {
            // ignore deletion errors
        }

        $stmt = $db->prepare(
            "UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?"
        );

        // Normalize stored avatar path:
        // - If it's an absolute URL (http/https) keep as-is.
        // - If it's a relative path and doesn't already include 'uploads/', prefix with 'uploads/'
        if (!empty($avatarPath) && stripos($avatarPath, 'http://') !== 0 && stripos($avatarPath, 'https://') !== 0) {
            $p = ltrim($avatarPath, '/');
            if (strpos($p, 'uploads/') !== 0) {
                $p = 'uploads/' . $p;
            }
            $avatarPath = $p;
        }

        return $stmt->execute([$avatarPath, $id]);
    }


    // ===== RESET PASSWORD =====

    // File User.php
    public static function createResetToken($email, $token) // Đủ 2 tham số
    {
        $db = self::db();

        // Xóa token cũ (quan trọng để tránh lỗi duplicate key nếu email đã tồn tại)
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        // Thêm token mới
        $stmt = $db->prepare(
            "INSERT INTO password_resets (email, token) VALUES (?, ?)" // Bỏ created_at nếu bảng tự sinh, hoặc thêm NOW()
        );
        // Nếu bảng có cột created_at tự động (DEFAULT CURRENT_TIMESTAMP) thì bỏ NOW() đi cũng được.
        // Nhưng tốt nhất nên ghi rõ:
        // "INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())"

        $stmt->execute([$email, $token]);
    }

    public static function getEmailByToken($token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "SELECT email FROM password_resets WHERE token = ? LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetchColumn();
    }

    // app/Models/User.php

    public static function updatePassword($email, $password)
    {
        $db = self::db();
        // Cập nhật mật khẩu mới dựa trên Email
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
        return $stmt->execute([$password, $email]);
    }

    // Update password by user id (used for change-password by authenticated user)
    public static function updatePasswordById($id, $password)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$password, $id]);
    }

    public static function deleteToken($token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "DELETE FROM password_resets WHERE token = ?"
        );
        $stmt->execute([$token]);
    }

    // ===== ROLE / LISTING HELPERS =====
    // Get users by role (quyen) with optional pagination and search
    public static function getByRole($role, $limit = 10, $offset = 0, $search = null)
    {
        $db = self::db();
        // Some MySQL/PDO drivers don't allow binding LIMIT/OFFSET as parameters.
        // Interpolate the integer values directly into the SQL to avoid syntax errors.
        $limit = (int) $limit;
        $offset = (int) $offset;
        if ($search) {
            $sql = "SELECT * FROM users WHERE quyen = ? AND (ho_ten LIKE ? OR so_dien_thoai LIKE ? OR ma_nhan_su LIKE ?) ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $like = '%' . $search . '%';
            $stmt->execute([$role, $like, $like, $like]);
        } else {
            $sql = "SELECT * FROM users WHERE quyen = ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute([$role]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countByRole($role, $search = null)
    {
        $db = self::db();
        if ($search) {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM users WHERE quyen = ? AND (ho_ten LIKE ? OR so_dien_thoai LIKE ? OR ma_nhan_su LIKE ?)"
            );
            $like = '%' . $search . '%';
            $stmt->execute([$role, $like, $like, $like]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE quyen = ?");
            $stmt->execute([$role]);
        }
        return (int) $stmt->fetchColumn();
    }

    // ===== DELETE USER =====
    public static function deleteById($id)
    {
        $db = self::db();

        // 1. Xóa Deal Posts (Tin chốt) của user
        if (file_exists(__DIR__ . '/DealPost.php')) {
            require_once __DIR__ . '/DealPost.php';
            $stmt = $db->prepare("SELECT id FROM deal_posts WHERE user_id = ?");
            $stmt->execute([$id]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($ids as $dpId) {
                DealPost::deleteById($dpId);
            }
        }

        // 2. Xóa Internal Posts (Tin nội bộ) của user
        if (file_exists(__DIR__ . '/InternalPost.php')) {
            require_once __DIR__ . '/InternalPost.php';
            $stmt = $db->prepare("SELECT id FROM internal_posts WHERE user_id = ?");
            $stmt->execute([$id]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($ids as $ipId) {
                InternalPost::deleteById($ipId);
            }
        }

        // 3. Xóa Properties (Bất động sản) của user
        $stmt = $db->prepare("SELECT id FROM properties WHERE user_id = ?");
        $stmt->execute([$id]);
        $propIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($propIds)) {
            foreach ($propIds as $pId) {
                // Xóa media (ảnh/video) của BĐS
                $stmtMedia = $db->prepare("SELECT media_path FROM property_media WHERE property_id = ?");
                $stmtMedia->execute([$pId]);
                $paths = $stmtMedia->fetchAll(PDO::FETCH_COLUMN);
                foreach ($paths as $path) {
                    $file = __DIR__ . '/../../public/' . $path;
                    if (file_exists($file) && is_file($file)) {
                        @unlink($file);
                    }
                }
                $db->prepare("DELETE FROM property_media WHERE property_id = ?")->execute([$pId]);
                // Xóa liên kết trong bộ sưu tập
                $db->prepare("DELETE FROM collection_items WHERE resource_id = ? AND (resource_type = 'bat_dong_san' OR resource_type IS NULL)")->execute([$pId]);
            }
            // Xóa properties
            $placeholders = implode(',', array_fill(0, count($propIds), '?'));
            $db->prepare("DELETE FROM properties WHERE id IN ($placeholders)")->execute($propIds);
        }

        // 4. Xóa Collections (Bộ sưu tập) của user
        if (file_exists(__DIR__ . '/Collection.php')) {
            require_once __DIR__ . '/Collection.php';
            $stmt = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmt->execute([$id]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($ids as $cId) {
                // Xóa các item trong collection trước
                $db->prepare("DELETE FROM collection_items WHERE collection_id = ?")->execute([$cId]);
                Collection::deleteById($cId);
            }
        }

        // 5. Xóa dữ liệu phụ (Lead Reports, Ratings, Ảnh CCCD)
        $db->prepare("DELETE FROM lead_reports WHERE user_id = ?")->execute([$id]);
        $db->prepare("DELETE FROM user_ratings WHERE rated_user_id = ?")->execute([$id]);

        $stmt = $db->prepare("SELECT anh_cccd FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $cccdPath = $stmt->fetchColumn();
        if ($cccdPath) {
            $file = __DIR__ . '/../../public/uploads/' . basename($cccdPath);
            if (file_exists($file)) @unlink($file);
        }

        // 6. Xóa User
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Update a set of profile fields (used by admin update form)
    public static function updateProfile($id, $data)
    {
        $db = self::db();
        // Also persist 'quyen' and 'loai_tai_khoan' when provided.
        $stmt = $db->prepare(
            "UPDATE users SET ma_nhan_su = ?, so_dien_thoai = ?, ho_ten = ?, nam_sinh = ?, email = ?, so_cccd = ?, phong_ban = ?, dia_chi = ?, link_fb = ?, ma_gioi_thieu = ?, anh_cccd = ?, trang_thai = ?, quyen = ?, loai_tai_khoan = ?, vi_tri = ?, updated_at = NOW() WHERE id = ?"
        );

        // Derive loai_tai_khoan if not explicitly provided (keep compatibility)
        $loai = $data['loai_tai_khoan'] ?? ((($data['quyen'] ?? '') === 'admin') ? 'admin' : 'nhan_vien');

        // Đảm bảo trạng thái là số nguyên (0, 1, 2)
        $trangThai = isset($data['trang_thai']) ? (int)$data['trang_thai'] : 0;

        // Handle vi_tri, allowing null
        $viTri = isset($data['vi_tri']) && $data['vi_tri'] !== '' ? (int)$data['vi_tri'] : null;

        return $stmt->execute([
            $data['ma_nhan_su'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['ho_ten'] ?? null,
            $data['nam_sinh'] ?? null,
            $data['email'] ?? null,
            $data['so_cccd'] ?? null,
            $data['phong_ban'] ?? null,
            $data['dia_chi'] ?? null,
            $data['link_fb'] ?? null,
            $data['ma_gioi_thieu'] ?? null,
            $data['anh_cccd'] ?? null,
            $trangThai,
            $data['quyen'] ?? 'user',
            $loai,
            $viTri,
            $id
        ]);
    }

    // Create user and set both quyen (role) and loai_tai_khoan
    public static function createWithRole($data)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "INSERT INTO users (
                ma_nhan_su,
                so_dien_thoai,
                password,
                ho_ten,
                nam_sinh,
                email,
                gioi_tinh,
                loai_tai_khoan,
                quyen,
                phong_ban,
                so_cccd,
                dia_chi,
                link_fb,
                ma_gioi_thieu,
                anh_cccd,
                trang_thai,
                vi_tri,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $loai = $data['loai_tai_khoan'] ?? ((($data['quyen'] ?? '') === 'admin') ? 'admin' : 'nhan_vien');

        return $stmt->execute([
            $data['ma_nhan_su'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['password'] ?? null,
            $data['ho_ten'] ?? null,
            $data['nam_sinh'] ?? null,
            $data['email'] ?? null,
            $data['gioi_tinh'] ?? null,
            $loai,
            $data['quyen'] ?? 'user',
            $data['phong_ban'] ?? null,
            $data['so_cccd'] ?? null,
            $data['dia_chi'] ?? null,
            $data['link_fb'] ?? null,
            $data['ma_gioi_thieu'] ?? null,
            $data['anh_cccd'] ?? null,
            $data['trang_thai'] ?? 1,
            $data['vi_tri'] ?? null,
        ]);
    }
}
