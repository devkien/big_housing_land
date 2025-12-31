<?php
// CLI test script to create a property using the Property model
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/Models/Property.php';
require_once __DIR__ . '/../app/Models/User.php';

// simple test data - adjust if your DB requires different enum values
// Create a test user to satisfy foreign key (if not exists)
$phone = '090' . rand(1000000, 9999999);
$userData = [
    'so_dien_thoai' => $phone,
    'password' => password_hash('secret123', PASSWORD_BCRYPT),
    'ho_ten' => 'Test User',
    'nam_sinh' => 1990,
    'loai_tai_khoan' => 'nhan_vien',
    'quyen' => 'user',
    'trang_thai' => 1
];

User::createWithRole($userData);
$createdUser = User::findByPhone($phone);
$userId = $createdUser['id'] ?? null;

$data = [
    'user_id' => $userId,
    'phong_ban' => 'kd1',
    'tieu_de' => 'Test property created by script',
    'loai_bds' => 'ban',
    'loai_kho' => 'kho_nha_dat',
    'phap_ly' => 'co_so',
    'dien_tich' => 100.5,
    'don_vi_dien_tich' => 'm2',
    'chieu_dai' => 10.5,
    'chieu_rong' => 9.6,
    'so_tang' => 2,
    'gia_chao' => 2500000000,
    'trich_thuong_gia_tri' => '1000000',
    'trich_thuong_don_vi' => 'VND',
    'tinh_thanh' => 'hn',
    'quan_huyen' => 'quan1',
    'xa_phuong' => 'phuong1',
    'dia_chi_chi_tiet' => 'Số 1, đường Test',
    'mo_ta' => 'Mô tả test',
    'is_visible' => 1,
    'trang_thai' => 'ban_manh'
];

try {
    $id = Property::create($data);
    if ($id) {
        echo "Property created with id: $id\n";
    } else {
        echo "Property::create returned false\n";
    }
} catch (PDOException $e) {
    echo "PDOException: " . $e->getMessage() . "\n";
}
