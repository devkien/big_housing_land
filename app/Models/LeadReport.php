<?php

class LeadReport extends Model
{
    public static function getList(int $limit = 10, int $offset = 0, ?string $search = null, ?string $managerCode = null)
    {
        $db = self::db();
        $params = [];

        $sql = "SELECT lr.*, c.ho_ten AS customer_name, c.so_dien_thoai AS customer_phone, u.ho_ten AS sender_name, m.ho_ten AS manager_name
                FROM lead_reports lr
                LEFT JOIN customers c ON lr.customer_id = c.id
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN users m ON u.ma_gioi_thieu = m.ma_nhan_su
                WHERE 1=1";

        if ($managerCode) {
            $sql .= " AND u.ma_gioi_thieu = ?";
            $params[] = $managerCode;
        }

        if ($search) {
            $sql .= " AND (c.ho_ten LIKE ? OR c.so_dien_thoai LIKE ? OR lr.note LIKE ? )";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY lr.created_at DESC";
        // PDO/MySQL can produce syntax errors when LIMIT/OFFSET are passed as bound params
        // (some MySQL versions or drivers don't accept quoted params in LIMIT). Cast
        // to int and interpolate directly to ensure valid numeric literals.
        $sql .= " LIMIT " . ((int)$limit) . " OFFSET " . ((int)$offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll(?string $search = null, ?string $managerCode = null)
    { 
        $db = self::db();
        $params = [];
        $sql = "SELECT COUNT(*) FROM lead_reports lr 
                LEFT JOIN customers c ON lr.customer_id = c.id
                LEFT JOIN users u ON lr.user_id = u.id
                WHERE 1=1";
        
        if ($managerCode) {
            $sql .= " AND u.ma_gioi_thieu = ?";
            $params[] = $managerCode;
        }

        if ($search) {
            $sql .= " AND (c.ho_ten LIKE ? OR c.so_dien_thoai LIKE ? OR lr.note LIKE ? )";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getById(int $id)
    {
        $db = self::db();
        $sql = "SELECT lr.*, c.*, u.ho_ten AS sender_name
                FROM lead_reports lr
                LEFT JOIN customers c ON lr.customer_id = c.id
                LEFT JOIN users u ON lr.user_id = u.id
                WHERE lr.id = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // fetch customer images from customer_images table if available
            if (!empty($row['customer_id'])) {
                try {
                    $stmt2 = $db->prepare("SELECT image_path FROM customer_images WHERE customer_id = ? ORDER BY id ASC");
                    $stmt2->execute([(int)$row['customer_id']]);
                    $imgs = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                    $row['customer_images'] = $imgs ?: [];
                } catch (Exception $ex) {
                    $row['customer_images'] = [];
                }
            } else {
                $row['customer_images'] = [];
            }
        }

        return $row ?: null;
    }

    public static function create($data)
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO lead_reports (user_id, customer_id, note, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([
            $data['user_id'] ?? null,
            $data['customer_id'] ?? null,
            $data['note'] ?? null,
            $data['status'] ?? 0
        ]);
    }
    public static function delete($id)
    {
        $db = self::db();
        $stmt = $db->prepare("DELETE FROM lead_reports WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
