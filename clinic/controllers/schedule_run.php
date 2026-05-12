<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/ScheduleController.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'manager'
) {
    header('Location: ../auth/login.php');
    exit;
}

$controller = new ScheduleController();

$action = $_GET['action'] ?? 'viewSchedule';

$allowed = [
    'viewSchedule',
    'createAppointment',
    'cancelAppointment'
];

if (in_array($action, $allowed, true)) {
    $controller->$action();
} else {
    $controller->viewSchedule();
}