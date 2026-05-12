<?php

require_once __DIR__ . '/../config/db.php';

class BaseController {

    protected mysqli $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function view(string $path, array $data = []): void {
        extract($data);
        require __DIR__ . '/../views/' . $path . '.php';
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit();
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/clinic/controllers/auth_run.php?action=login');
        }
    }

    protected function requireRole(string $role): void {
        $this->requireLogin();
        if (strtolower((string)($_SESSION['role'] ?? '')) !== strtolower($role)) {
            $this->redirect('/clinic/controllers/auth_run.php?action=login');
        }
    }

}
