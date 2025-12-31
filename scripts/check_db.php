<?php
require __DIR__ . '/../core/Database.php';
try {
    $db = Database::connect();
    echo 'OK';
} catch (Exception $e) {
    echo 'ERR: ' . $e->getMessage();
}
