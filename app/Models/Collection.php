<?php

class Collection extends Model
{
    // Return collections with item counts, optional search
    // If $userId is provided, return only collections owned by that user.
    public static function allWithCount(?string $search = null, ?int $userId = null)
    {
        $db = self::db();
        $params = [];

        $sql = "SELECT c.id, c.user_id, c.ten_bo_suu_tap, c.anh_dai_dien, c.mo_ta, c.is_default, c.trang_thai, COUNT(ci.id) AS item_count
                FROM collections c
                LEFT JOIN collection_items ci ON ci.collection_id = c.id";

        $clauses = [];
        if ($search) {
            $clauses[] = "(c.ten_bo_suu_tap LIKE ? OR c.mo_ta LIKE ? )";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($userId !== null) {
            $clauses[] = "c.user_id = ?";
            $params[] = (int)$userId;
        }
        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::allWithCount error: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return [];
        }
    }

    public static function getForUser($userId, $search = null)
    {
        $db = self::db();
        $params = [(int)$userId];
        $sql = "SELECT c.id, c.ten_bo_suu_tap, c.anh_dai_dien, c.mo_ta, c.is_default, c.trang_thai, COUNT(ci.id) AS item_count
                FROM collections c
                LEFT JOIN collection_items ci ON ci.collection_id = c.id
                WHERE c.user_id = ?";

        if ($search) {
            $sql .= " AND (c.ten_bo_suu_tap LIKE ? OR c.mo_ta LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Save associations between a property/resource and multiple collections belonging to $userId.
    // Uses `resource_id` and `resource_type` columns (DB schema).
    // Returns number of inserted rows on success, or false on error.
    public static function savePropertyToCollections(int $propertyId, array $collectionIds, int $userId, string $resourceType = 'bat_dong_san')
    {
        $db = self::db();
        try {
            $db->beginTransaction();

            // 1. Get all collections owned by the user to validate against
            $stmt = $db->prepare("SELECT id FROM collections WHERE user_id = ?");
            $stmt->execute([$userId]);
            $ownedCollectionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($ownedCollectionIds)) {
                // nothing to do
                $db->commit();
                return 0;
            }

            // 2. Delete all old associations for this property within the user's collections
            $ownedPlaceholders = implode(',', array_fill(0, count($ownedCollectionIds), '?'));
            $delStmt = $db->prepare("DELETE FROM collection_items WHERE resource_id = ? AND collection_id IN ($ownedPlaceholders) AND resource_type = ?");
            $delParams = array_merge([$propertyId], $ownedCollectionIds);
            // append resource_type at end for prepared statement
            $delParams[] = $resourceType;
            $delStmt->execute($delParams);

            // 3. Insert new associations, but only for collections the user owns.
            $validCollectionIds = array_values(array_intersect($collectionIds, $ownedCollectionIds));

            $inserted = 0;
            if (!empty($validCollectionIds)) {
                $insStmt = $db->prepare("INSERT INTO collection_items (collection_id, resource_id, resource_type, created_at) VALUES (?, ?, ?, NOW())");
                foreach ($validCollectionIds as $cid) {
                    if ($insStmt->execute([(int)$cid, $propertyId, $resourceType])) {
                        $inserted++;
                    }
                }
            }

            $db->commit();
            return $inserted;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    // Return collection ids for a given resource regardless of resource_type.
    public static function getCollectionIdsForProperty(int $propertyId, ?int $userId = null, ?string $resourceType = null)
    {
        $db = self::db();
        $sql = "SELECT ci.collection_id FROM collection_items ci JOIN collections c ON ci.collection_id = c.id WHERE ci.resource_id = ?";
        $params = [$propertyId];
        if ($resourceType !== null) {
            // Support legacy/legacy-default resource_type 'bat_dong_san' and NULL values
            $sql .= " AND (ci.resource_type = ? OR ci.resource_type = 'bat_dong_san' OR ci.resource_type IS NULL)";
            $params[] = $resourceType;
        }
        if ($userId !== null) {
            $sql .= " AND c.user_id = ?";
            $params[] = $userId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Return map of resource_id => count of collections that include it
    // If $userId is provided, count only collections owned by that user (used by main user views).
    public static function getCountsForProperties(array $propertyIds, string $resourceType = 'bat_dong_san', ?int $userId = null)
    {
        if (empty($propertyIds)) return [];
        $db = self::db();
        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
        // Join collections table so we can optionally filter by collection owner
        // Match either the explicit resourceType OR legacy 'bat_dong_san' or NULL resource_type values
        $sql = "SELECT ci.resource_id, COUNT(*) AS cnt FROM collection_items ci JOIN collections c ON ci.collection_id = c.id WHERE ci.resource_id IN ($placeholders) AND (ci.resource_type = ? OR ci.resource_type = 'bat_dong_san' OR ci.resource_type IS NULL)";
        $params = array_merge(array_values($propertyIds), [$resourceType]);
        if ($userId !== null) {
            $sql .= " AND c.user_id = ?";
            $params[] = (int)$userId;
        }
        $sql .= " GROUP BY ci.resource_id";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];
            foreach ($rows as $r) {
                $map[(int)$r['resource_id']] = (int)$r['cnt'];
            }
            return $map;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function create(array $data)
    {
        $db = self::db();
        $sql = "INSERT INTO collections (user_id, ten_bo_suu_tap, anh_dai_dien, mo_ta, is_default, trang_thai, created_at, updated_at)
                VALUES (:user_id, :ten, :anh, :mo_ta, :is_default, :trang_thai, :created_at, :updated_at)";
        $now = date('Y-m-d H:i:s');
        $params = [
            ':user_id' => $data['user_id'] ?? null,
            ':ten' => $data['ten_bo_suu_tap'] ?? null,
            ':anh' => $data['anh_dai_dien'] ?? null,
            ':mo_ta' => $data['mo_ta'] ?? null,
            ':is_default' => isset($data['is_default']) ? (int)$data['is_default'] : 0,
            ':trang_thai' => isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1,
            ':created_at' => $now,
            ':updated_at' => $now,
        ];

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::create error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function updateName(int $id, string $name)
    {
        $db = self::db();
        $sql = "UPDATE collections SET ten_bo_suu_tap = :ten, updated_at = :updated_at WHERE id = :id";
        $params = [':ten' => $name, ':updated_at' => date('Y-m-d H:i:s'), ':id' => $id];
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::updateName error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function deleteById(int $id)
    {
        $db = self::db();
        $sql = "DELETE FROM collections WHERE id = :id";
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::deleteById error: " . $e->getMessage() . " Params: {\"id\":$id}\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    // Get a single collection record by id
    public static function getById(int $id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM collections WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Get items belonging to a collection. If $resourceType is null, return items of any resource_type.
    // For property resources this joins `properties` table.
    public static function getItems(int $collectionId, ?string $resourceType = null, array $filters = [])
    {
        $db = self::db();
        // Build base SQL joining properties (we assume collection_items.resource_id points to properties.id for property resources)
        $sql = "SELECT p.* , ci.created_at AS saved_at, ci.id AS ci_id, ci.resource_type AS resource_type, ci.resource_id AS resource_id
                FROM collection_items ci
                JOIN properties p ON p.id = ci.resource_id
                WHERE ci.collection_id = ?";

        $params = [(int)$collectionId];

        // If specific resource_type requested, restrict to it
        if ($resourceType !== null) {
            $sql .= " AND ci.resource_type = ?";
            $params[] = $resourceType;
        }

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND p.trang_thai = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['address'])) {
            $sql .= " AND (p.dia_chi_chi_tiet LIKE ? OR p.tinh_thanh LIKE ? OR p.quan_huyen LIKE ? OR p.xa_phuong LIKE ? )";
            $like = '%' . $filters['address'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['q'])) {
            $sql .= " AND (p.ma_hien_thi LIKE ? OR p.tieu_de LIKE ? OR p.dia_chi_chi_tiet LIKE ? OR p.mo_ta LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY ci.created_at DESC";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::getItems error: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return [];
        }
    }

    // Backwards-compatible helper used by some tests/scripts: addItems(collectionIds, propertyId, resourceType)
    public static function addItems(array $collectionIds, int $propertyId, $resourceType = null)
    {
        $db = self::db();
        try {
            $db->beginTransaction();
            // remove existing links for this property and these collections to avoid duplicates
            if (!empty($collectionIds)) {
                $placeholders = implode(',', array_fill(0, count($collectionIds), '?'));
                $delParams = array_merge([$propertyId], $collectionIds);
                // append resource_type if provided
                if ($resourceType) {
                    $delStmt = $db->prepare("DELETE FROM collection_items WHERE resource_id = ? AND collection_id IN ($placeholders) AND resource_type = ?");
                    $delParams[] = $resourceType;
                } else {
                    $delStmt = $db->prepare("DELETE FROM collection_items WHERE resource_id = ? AND collection_id IN ($placeholders)");
                }
                $delStmt->execute($delParams);
            }

            $inserted = 0;
            if (!empty($collectionIds)) {
                if ($resourceType) {
                    $ins = $db->prepare("INSERT INTO collection_items (collection_id, resource_id, resource_type, created_at) VALUES (?, ?, ?, NOW())");
                    foreach ($collectionIds as $cid) {
                        if ($ins->execute([(int)$cid, $propertyId, $resourceType])) $inserted++;
                    }
                } else {
                    // fallback for legacy schema expecting property_id
                    $ins = $db->prepare("INSERT INTO collection_items (collection_id, property_id, created_at) VALUES (?, ?, NOW())");
                    foreach ($collectionIds as $cid) {
                        if ($ins->execute([(int)$cid, $propertyId])) $inserted++;
                    }
                }
            }
            $db->commit();
            return $inserted;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    // Sync associations for a resource across collections.
    // After this call, the resource will belong ONLY to the provided $collectionIds
    // (for the given resource_type). Returns number of inserted rows on success, or
    // false on error.
    public static function syncItems(array $collectionIds, int $propertyId, string $resourceType = 'bat_dong_san')
    {
        $db = self::db();
        try {
            $db->beginTransaction();

            // 1) Delete any existing links for this resource and resource_type
            //    that are NOT in the new set of collection IDs. If the new set
            //    is empty, delete all links for this resource/resource_type.
            if (!empty($collectionIds)) {
                $placeholders = implode(',', array_fill(0, count($collectionIds), '?'));
                $delSql = "DELETE FROM collection_items WHERE resource_id = ? AND resource_type = ? AND collection_id NOT IN ($placeholders)";
                $delParams = array_merge([$propertyId, $resourceType], $collectionIds);
            } else {
                $delSql = "DELETE FROM collection_items WHERE resource_id = ? AND resource_type = ?";
                $delParams = [$propertyId, $resourceType];
            }
            $delStmt = $db->prepare($delSql);
            $delStmt->execute($delParams);

            // 2) Insert new links for collections in the provided list if they don't exist.
            $inserted = 0;
            if (!empty($collectionIds)) {
                // Use a safe INSERT ... SELECT ... WHERE NOT EXISTS pattern to avoid duplicates
                $insSql = "INSERT INTO collection_items (collection_id, resource_id, resource_type, created_at)
                           SELECT ?, ?, ?, NOW() WHERE NOT EXISTS (SELECT 1 FROM collection_items WHERE collection_id = ? AND resource_id = ? AND resource_type = ? )";
                $insStmt = $db->prepare($insSql);
                foreach ($collectionIds as $cid) {
                    $params = [(int)$cid, $propertyId, $resourceType, (int)$cid, $propertyId, $resourceType];
                    $insStmt->execute($params);
                    $inserted += $insStmt->rowCount();
                }
            }

            $db->commit();
            return $inserted;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    // Remove a single resource from a collection, validating that the collection belongs to the user
    public static function removeItem(int $collectionId, int $resourceId, int $userId, string $resourceType = 'bat_dong_san', bool $force = false)
    {
        $db = self::db();
        try {
            // Ensure the collection belongs to the user, unless forced (e.g., super_admin)
            if (!$force) {
                $stmt = $db->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ? LIMIT 1");
                $stmt->execute([$collectionId, $userId]);
                $exists = $stmt->fetchColumn();
                if (!$exists) return false;
            }

            $del = $db->prepare("DELETE FROM collection_items WHERE collection_id = ? AND resource_id = ? AND resource_type = ?");
            return (bool)$del->execute([$collectionId, $resourceId, $resourceType]);
        } catch (Exception $e) {
            return false;
        }
    }
}
