<?php

class Controller
{
    public function __construct()
    {
        // Base constructor intentionally left blank so child controllers
        // can call parent::__construct() safely when needed.
    }

    protected function view($path, $data = [])
    {
        extract($data);
        require_once __DIR__ . '/../app/Views/' . $path . '.php';
    }

    // Yêu cầu role trước khi vào action
    protected function requireRole(array $roles)
    {
        require_once __DIR__ . '/Auth.php';
        if (!\Auth::inRoles($roles)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
