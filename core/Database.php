<?php

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        try {
            $this->conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die('Lỗi kết nối DB: ' . $e->getMessage());
        }
    }

    public static function connect()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
