<?php

class DealPost extends Model
{
    public static function getList(int $limit = 20, int $offset = 0, ?string $search = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT dp.*, u.ho_ten AS author_name FROM deal_posts dp LEFT JOIN users u ON dp.user_id = u.id WHERE 1=1";
        if ($search) {
            $sql .= " AND (dp.noi_dung LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
        }
        $sql .= " ORDER BY dp.created_at DESC LIMIT " . ((int)$limit) . " OFFSET " . ((int)$offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // attach images for each post
        foreach ($posts as &$p) {
            $p['images'] = [];
            if (!empty($p['id'])) {
                $stmt2 = $db->prepare("SELECT image_path FROM deal_post_images WHERE deal_post_id = ? ORDER BY id ASC");
                $stmt2->execute([(int)$p['id']]);
                $imgs = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                $p['images'] = $imgs ?: [];
            }
        }

        return $posts;
    }

    public static function getById(int $id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT dp.*, u.ho_ten AS author_name FROM deal_posts dp LEFT JOIN users u ON dp.user_id = u.id WHERE dp.id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) return null;

        $stmt2 = $db->prepare("SELECT * FROM deal_post_images WHERE deal_post_id = ? ORDER BY id ASC");
        $stmt2->execute([(int)$id]);
        $post['images'] = $stmt2->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $post;
    }

    /**
     * Get posts by a specific user with attached images
     */
    public static function getByUser(int $userId, int $limit = 10, int $offset = 0)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT dp.*, u.ho_ten AS author_name FROM deal_posts dp LEFT JOIN users u ON dp.user_id = u.id WHERE dp.user_id = ? ORDER BY dp.created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as &$p) {
            $p['images'] = [];
            if (!empty($p['id'])) {
                $stmt2 = $db->prepare("SELECT image_path FROM deal_post_images WHERE deal_post_id = ? ORDER BY id ASC");
                $stmt2->execute([(int)$p['id']]);
                $imgs = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                $p['images'] = $imgs ?: [];
            }
        }

        return $posts;
    }

    public static function create(array $data)
    {
        $db = self::db();
        $sql = "INSERT INTO deal_posts (user_id, bat_dong_san_id, tieu_de, noi_dung, trang_thai, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $userId = $data['user_id'] ?? null;
        $bdsId = $data['bat_dong_san_id'] ?? null;
        $tieuDe = $data['tieu_de'] ?? null;
        $noiDung = $data['noi_dung'] ?? null;
        $trangThai = isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1;
        $stmt->execute([$userId, $bdsId, $tieuDe, $noiDung, $trangThai]);
        return (int)$db->lastInsertId();
    }

    public static function addImages(int $dealPostId, array $imagePaths)
    {
        if (empty($imagePaths)) return false;
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO deal_post_images (deal_post_id, image_path, created_at) VALUES (?, ?, NOW())");
        foreach ($imagePaths as $p) {
            $stmt->execute([(int)$dealPostId, $p]);
        }
        return true;
    }

    public static function update(int $id, array $data)
    {
        $db = self::db();
        $sql = "UPDATE deal_posts SET tieu_de = ?, noi_dung = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $data['tieu_de'] ?? null,
            $data['noi_dung'] ?? null,
            $id
        ]);
    }

    public static function deleteImageById(int $imageId)
    {
        $db = self::db();
        // First, get the path to delete the file from disk
        $stmt = $db->prepare("SELECT image_path FROM deal_post_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $path = $stmt->fetchColumn();
        if ($path && file_exists(__DIR__ . '/../../public/' . $path)) {
            @unlink(__DIR__ . '/../../public/' . $path);
        }
        // Then, delete the record from the database
        $stmt = $db->prepare("DELETE FROM deal_post_images WHERE id = ?");
        return $stmt->execute([$imageId]);
    }

    public static function deleteById(int $id)
    {
        $db = self::db();
        try {
            $db->beginTransaction();

            // First, delete associated images to avoid foreign key constraints
            $stmt1 = $db->prepare("DELETE FROM deal_post_images WHERE deal_post_id = ?");
            $stmt1->execute([$id]);

            // Then, delete the post itself
            $stmt2 = $db->prepare("DELETE FROM deal_posts WHERE id = ?");
            $stmt2->execute([$id]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
