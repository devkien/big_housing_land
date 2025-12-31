<?php

class Product
{
    public static function all()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM products");
        return $stmt->fetchAll();
    }
}
