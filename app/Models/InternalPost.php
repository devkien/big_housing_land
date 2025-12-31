<?php

class InternalPost extends Model
{
    // Get active posts (trang_thai = 1) ordered by newest first
    public static function getActive($limit = 50, $offset = 0, $search = null)
    {
        $db = self::db();
        $limit = (int)$limit;
        $offset = (int)$offset;
        $params = [];
        $sql = "SELECT * FROM internal_posts WHERE trang_thai = 1";

        if ($search) {
            $sql .= " AND (tieu_de LIKE ? OR noi_dung LIKE ? OR ma_hien_thi LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countActive($search = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT COUNT(*) FROM internal_posts WHERE trang_thai = 1";

        if ($search) {
            $sql .= " AND (tieu_de LIKE ? OR noi_dung LIKE ? OR ma_hien_thi LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Get images for a post ordered by sort_order
    public static function getImages($postId)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM internal_post_images WHERE internal_post_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([(int)$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get pinned posts ordered by pinned_at desc
    public static function getPinned($limit = 5)
    {
        $db = self::db();
        $limit = (int)$limit;
        $sql = "SELECT * FROM internal_posts WHERE trang_thai = 1 AND is_pinned = 1 ORDER BY pinned_at DESC LIMIT " . $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Convenience: get first image path or null
    public static function getFirstImagePath($postId)
    {
        $images = self::getImages($postId);
        if (!$images) return null;
        return $images[0]['image_path'] ?? null;
    }

    public static function create($data)
    {
        $db = self::db();
        // Tạo mã hiển thị duy nhất để tránh lỗi Duplicate entry
        $ma_hien_thi = 'NB' . time() . rand(100, 999);

        $stmt = $db->prepare("INSERT INTO internal_posts (user_id, tieu_de, noi_dung, trang_thai, created_at, ma_hien_thi) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([
            $data['user_id'],
            $data['tieu_de'],
            $data['noi_dung'],
            $data['trang_thai'],
            $ma_hien_thi
        ]);
        return $db->lastInsertId();
    }

    public static function addImages($id, $images)
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO internal_post_images (internal_post_id, image_path) VALUES (?, ?)");
        foreach ($images as $path) {
            $stmt->execute([$id, $path]);
        }
    }

    public static function getById($id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM internal_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post) {
            $post['images'] = self::getImages($id);
        }
        return $post;
    }

    public static function update($id, $data)
    {
        $db = self::db();
        $stmt = $db->prepare("UPDATE internal_posts SET tieu_de = ?, noi_dung = ?, trang_thai = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$data['tieu_de'], $data['noi_dung'], $data['trang_thai'], $id]);
    }

    // Set or unset the pinned flag for a post
    public static function setPinned(int $id, bool $pinned)
    {
        $db = self::db();
        if ($pinned) {
            $stmt = $db->prepare("UPDATE internal_posts SET is_pinned = 1, pinned_at = NOW(), updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare("UPDATE internal_posts SET is_pinned = 0, pinned_at = NULL, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }

    public static function deleteById(int $id)
    {
        $db = self::db();

        // 1. Xóa hình ảnh vật lý và record trong DB
        $images = self::getImages($id);
        foreach ($images as $img) {
            if (!empty($img['image_path'])) {
                $file = __DIR__ . '/../../public/' . $img['image_path'];
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }

        $stmt = $db->prepare("DELETE FROM internal_post_images WHERE internal_post_id = ?");
        $stmt->execute([$id]);

        // Xóa thư mục chứa ảnh nếu rỗng
        $dir = __DIR__ . '/../../public/uploads/internal/' . $id;
        if (is_dir($dir)) {
            @rmdir($dir);
        }

        // 2. Xóa bài viết
        $stmt = $db->prepare("DELETE FROM internal_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteImageById($id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT image_path FROM internal_post_images WHERE id = ?");
        $stmt->execute([$id]);
        $path = $stmt->fetchColumn();

        if ($path) {
            $file = __DIR__ . '/../../public/' . $path;
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        $stmt = $db->prepare("DELETE FROM internal_post_images WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
