<?php

class Customer extends Model
{
    public static function create($data)
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO customers (
                ho_ten,
                nam_sinh,
                so_dien_thoai,
                cccd,
                note,
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([
            $data['ho_ten'],
            $data['nam_sinh'],
            $data['so_dien_thoai'],
            $data['cccd'],
            $data['note']
        ])) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function addImage($customerId, $path)
    {
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO customer_images (customer_id, image_path, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$customerId, $path]);
    }
}
