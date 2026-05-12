<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/ManagerController.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'manager'
) {
    header('Location: ../auth/login.php');
    exit;
}

$controller = new ManagerController();

$action = $_GET['action'] ?? 'dashboard';

$allowed = [
    'dashboard',
    'assignTherapist',
    'verifyTherapists',
    'verifyIntakeForms',
    'reports',
    'crisisAlerts',
    'notes'
];

if (in_array($action, $allowed, true)) {
    $controller->$action();
} else {
    $controller->dashboard();
}