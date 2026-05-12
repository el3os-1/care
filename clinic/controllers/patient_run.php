<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/PatientController.php';

if (empty($_SESSION['user_id']) || strtolower((string)($_SESSION['role'] ?? '')) !== 'patient') {
    header('Location: /clinic/controllers/auth_run.php?action=login');
    exit;
}

$controller = new PatientController();
$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'intakeForm':
        $controller->intakeForm($userId);
        break;
    case 'agreements':
        $controller->agreements($userId);
        break;
    case 'sessions':
        $controller->sessions($userId);
        break;
    case 'paySession':
        $controller->paySession($userId);
        break;
    case 'favorites':
        $controller->favorites($userId);
        break;
    case 'logMood':
        $controller->logMood($userId);
        break;
    case 'emergency':
        $controller->emergency($userId);
        break;
    case 'dashboard':
    default:
        $controller->dashboard($userId);
        break;
}
