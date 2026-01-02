<?php

class Property extends Model
{
    // Create a new property and return the inserted ID (int) or false on failure
    public static function create(array $data)
    {
        $db = self::db();
        // Note: `created_at` has DB default; don't include it in explicit column list
        $stmt = $db->prepare(
            "INSERT INTO properties (
                user_id,
                ma_hien_thi,
                phong_ban,
                tieu_de,
                loai_bds,
                loai_kho,
                phap_ly,
                ma_so_so,
                ma_so_thue,
                dien_tich,
                don_vi_dien_tich,
                chieu_dai,
                chieu_rong,
                so_tang,
                gia_chao,
                don_vi_gia,
                trich_thuong_gia_tri,
                trich_thuong_don_vi,
                tinh_thanh,
                quan_huyen,
                xa_phuong,
                dia_chi_chi_tiet,
                trang_thai,
                tinh_trang_duyet,
                mo_ta,
                is_visible
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        // Ensure ma_hien_thi exists (DB requires non-null)
        // Use a stronger random id (time + 8 random bytes => 16 hex chars) to reduce collision risk.
        $ma_hien_thi = $data['ma_hien_thi'] ?? null;
        $duyetStatus = isset($data['tinh_trang_duyet']) && $data['tinh_trang_duyet'] === 'da_duyet' 
                       ? 'da_duyet' 
                       : 'cho_duyet';
        // Prepare params - placeholder for ma_hien_thi will be filled below
        $params = [
            $data['user_id'] ?? null,
            null, // ma_hien_thi placeholder -> set before execute
            $data['phong_ban'] ?? null,
            $data['tieu_de'] ?? null,
            $data['loai_bds'] ?? null,
            $data['loai_kho'] ?? null,
            $data['phap_ly'] ?? null,
            $data['ma_so_so'] ?? null,
            $data['ma_so_thue'] ?? null,
            isset($data['dien_tich']) ? $data['dien_tich'] : null,
            $data['don_vi_dien_tich'] ?? null,
            $data['chieu_dai'] ?? null,
            $data['chieu_rong'] ?? null,
            $data['so_tang'] ?? null,
            isset($data['gia_chao']) ? $data['gia_chao'] : null,
            $data['don_vi_gia'] ?? null,
            $data['trich_thuong_gia_tri'] ?? null,
            $data['trich_thuong_don_vi'] ?? null,
            $data['tinh_thanh'] ?? null,
            $data['quan_huyen'] ?? null,
            $data['xa_phuong'] ?? null,
            $data['dia_chi_chi_tiet'] ?? null,
            $data['trang_thai'] ?? null,
            $data['tinh_trang_duyet'] ?? 'cho_duyet',
            $data['mo_ta'] ?? null,
            isset($data['is_visible']) ? (int)$data['is_visible'] : 1
        ];

        // Try to execute; if duplicate ma_hien_thi occurs (unique constraint), retry few times
        $tries = 0;
        $maxTries = 5;
        while ($tries < $maxTries) {
            if (empty($ma_hien_thi)) {
                $ma_hien_thi = 'P' . time() . bin2hex(random_bytes(8));
            }
            $params[1] = $ma_hien_thi; // fill ma_hien_thi param
            try {
                $ok = $stmt->execute($params);
                if ($ok) break;
                else {
                    $err = $stmt->errorInfo();
                    $log = __DIR__ . '/../../storage/logs/property_create_errors.log';
                    @file_put_contents($log, date('Y-m-d H:i:s') . " - execute returned false; errorInfo=" . var_export($err, true) . " params=" . json_encode($params) . "\n", FILE_APPEND);
                }
            } catch (PDOException $e) {
                $errLog = __DIR__ . '/../../storage/logs/property_create_errors.log';
                @file_put_contents($errLog, date('Y-m-d H:i:s') . " - PDOException code=" . $e->getCode() . " msg=" . $e->getMessage() . " errorInfo=" . var_export($e->errorInfo, true) . " params=" . json_encode($params) . "\n", FILE_APPEND);
                // SQLSTATE 23000 often indicates duplicate key; inspect which key and retry appropriately
                if ($e->getCode() === '23000') {
                    $msg = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
                    // If duplicate on ma_hien_thi, regenerate it and retry
                    if (stripos($msg, 'ma_hien_thi') !== false) {
                        $ma_hien_thi = null; // force regenerate
                        $tries++;
                        continue;
                    }
                    // If duplicate on ma_so_so (user-supplied), clear it and retry once
                    if (stripos($msg, 'ma_so_so') !== false) {
                        $params[7] = null; // ma_so_so is at index 7 in params
                        $tries++;
                        continue;
                    }
                }
                throw $e; // rethrow other DB exceptions
            }
            $tries++;
        }
        if (empty($ok)) return false;
        $dbh = self::db();
        return (int)$dbh->lastInsertId();
    }

    // Insert media rows for a property. $media is array of ['type'=>'image'|'video','path'=>string]
    public static function addMedia(int $propertyId, array $media)
    {
        if (empty($media)) return true;
        $db = self::db();
        $stmt = $db->prepare(
            "INSERT INTO property_media (property_id, media_type, media_path, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())"
        );

        $order = 0;
        foreach ($media as $m) {
            $order++;
            $type = $m['type'] ?? 'image';
            $path = $m['path'] ?? '';
            $stmt->execute([$propertyId, $type, $path, $order]);
        }
        return true;
    }

    // Fetch media rows for a single property
    public static function getMedia(int $propertyId)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT media_type, media_path FROM property_media WHERE property_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFirstImagePath(int $propertyId)
    {
        $media = self::getMedia($propertyId);
        return !empty($media) ? ($media[0]['media_path'] ?? null) : null;
    }

    // Get properties by loai_kho with optional search, pagination
    public static function getByLoaiKho(string $loai_kho, int $limit = 20, int $offset = 0, ?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $limit = (int)$limit;
        $offset = (int)$offset;

        $params = [];
        $params[] = $loai_kho;

        $sql = "SELECT * FROM properties WHERE loai_kho = ?";

        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)
                      ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        } else {
            $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public static function countByLoaiKho(string $loai_kho, ?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $params = [];
        $params[] = $loai_kho;

        $sql = "SELECT COUNT(*) FROM properties WHERE loai_kho = ?";
        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Get ALL properties (both sale and rent)
    public static function getAll(int $limit = 20, int $offset = 0, ?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT * FROM properties WHERE 1=1";

        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll(?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT COUNT(*) FROM properties WHERE 1=1";

        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Get properties for a specific user, optionally filtering by approval status
    public static function getForUser(int $userId, int $limit = 20, int $offset = 0, ?string $search = null, ?string $tinh_trang_duyet = null)
    {
        $db = self::db();
        $params = [$userId];
        $sql = "SELECT * FROM properties WHERE user_id = ?";

        if ($tinh_trang_duyet) {
            $sql .= " AND tinh_trang_duyet = ?";
            $params[] = $tinh_trang_duyet;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countForUser(int $userId, ?string $search = null, ?string $tinh_trang_duyet = null)
    {
        $db = self::db();
        $params = [$userId];
        $sql = "SELECT COUNT(*) FROM properties WHERE user_id = ?";

        if ($tinh_trang_duyet) {
            $sql .= " AND tinh_trang_duyet = ?";
            $params[] = $tinh_trang_duyet;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Find a single property by its visible code `ma_hien_thi`
    public static function findByMaHienThi(string $ma)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM properties WHERE ma_hien_thi = ? LIMIT 1");
        $stmt->execute([$ma]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Find a single property by its primary id
    public static function findById(int $id)
    {
        $db = self::db();

        // SỬA LẠI: Đổi 'user' thành 'users' (có chữ s)
        $sql = "SELECT properties.*, 
                       users.ho_ten, 
                       users.avatar, 
                       users.so_dien_thoai as user_phone, 
                       users.link_fb 
                FROM properties 
                LEFT JOIN users ON properties.user_id = users.id 
                WHERE properties.id = ? 
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Update the status (trang_thai) of a property by id.
    public static function updateStatus(int $id, string $trang_thai)
    {
        $allowed = [
            'ban_manh',
            'tam_dung_ban',
            'dung_ban',
            'da_ban',
            'tang_chao',
            'ha_chao'
        ];
        if (!in_array($trang_thai, $allowed, true)) {
            return false;
        }

        $db = self::db();
        $stmt = $db->prepare("UPDATE properties SET trang_thai = ?, updated_at = NOW() WHERE id = ?");
        try {
            return (bool)$stmt->execute([$trang_thai, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
    public static function quickUpdate($id, $trangThai, $tinhTrangDuyet)
    {
        // Validate trang_thai
        $allowedStatus = [
            'ban_manh', 'tam_dung_ban', 'dung_ban', 'da_ban', 'tang_chao', 'ha_chao'
        ];
        if (!in_array($trangThai, $allowedStatus)) return false;

        // Validate tinh_trang_duyet
        $allowedApproval = [
            'cho_duyet', 'da_duyet', 'tu_choi'
        ];
        if (!in_array($tinhTrangDuyet, $allowedApproval)) return false;

        $db = self::db();
        $sql = "UPDATE properties SET trang_thai = ?, tinh_trang_duyet = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        try {
            return $stmt->execute([$trangThai, $tinhTrangDuyet, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cập nhật thông tin tài nguyên
    public static function update($id, $data)
    {
        $db = self::db();
        $fields = [];
        $params = [];
        foreach ($data as $key => $val) {
            $fields[] = "$key = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE properties SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    // Xóa tài nguyên và dữ liệu liên quan
    public static function delete($id)
    {
        $db = self::db();
        // 1. Xóa file ảnh vật lý
        $media = self::getMedia($id);
        foreach ($media as $m) {
            if (!empty($m['media_path']) && file_exists(__DIR__ . '/../../public/' . $m['media_path'])) {
                @unlink(__DIR__ . '/../../public/' . $m['media_path']);
            }
        }
        $db->prepare("DELETE FROM property_media WHERE property_id = ?")->execute([$id]);

        // 2. Xóa liên kết trong bộ sưu tập
        $db->prepare("DELETE FROM collection_items WHERE resource_id = ? AND (resource_type = 'bat_dong_san' OR resource_type IS NULL)")->execute([$id]);

        // 3. Xóa property
        $stmt = $db->prepare("DELETE FROM properties WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
