<?php
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::connect();

    $stmt = $db->query("SELECT 1");
    echo "Kết nối DB thành công!";
} catch (Exception $e) {
    echo "❌ Kết nối DB thất bại: " . $e->getMessage();
}
