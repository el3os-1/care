<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/encryption.php';
require_once __DIR__ . '/AdminController.php';
$conn = Database::getConnection();

if (empty($_SESSION['user_id']) || strtolower((string)($_SESSION['role'] ?? '')) !== 'admin') {
    header('Location: /clinic/controllers/auth_run.php?action=login');
    exit;
}

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'sendManagerNote':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message'] ?? '');
            if ($message !== '') {
                $stmt = $conn->prepare("
                    INSERT INTO Notification (UserId, Message, Type)
                    SELECT ur.UserId, ?, 'admin_manager_note'
                    FROM UserRoles ur
                    JOIN Roles r ON ur.RoleId = r.RoleId
                    WHERE r.RoleName = 'manager'
                ");
                if ($stmt) {
                    $payload = 'Admin note: ' . $message;
                    $stmt->bind_param('s', $payload);
                    $stmt->execute();
                }
            }
        }
        header('Location: /clinic/controllers/admin_run.php?action=dashboard&msg=note_sent');
        exit;
    case 'users':
        require __DIR__ . '/../views/admin/users.php';
        break;
    case 'updateUser':
        require __DIR__ . '/../views/admin/UpdateUser.php';
        break;
    case 'dashboard':
    default:
        require __DIR__ . '/../views/admin/dashboard.php';
        break;
}
