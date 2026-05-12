<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/TherapistController.php';

if (empty($_SESSION['user_id']) || strtolower((string)($_SESSION['role'] ?? '')) !== 'therapist') {
    header('Location: /clinic/controllers/auth_run.php?action=login');
    exit;
}

$controller = new TherapistController();
$therapistId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? 'dashboard';
$sessionId = (int)($_GET['id'] ?? $_POST['session_id'] ?? 0);

switch ($action) {
    case 'moodReports':
        $controller->moodReports($therapistId);
        break;
    case 'moodReport':
        $patientId = (int)($_GET['patient_id'] ?? 0);
        $controller->moodReport($therapistId, $patientId);
        break;
    case 'availability':
        $controller->availability($therapistId);
        break;
    case 'session':
        $controller->viewSession($therapistId, $sessionId);
        break;
    case 'startSession':
        $controller->startSession($therapistId, $sessionId);
        break;
    case 'endSession':
        $controller->endSession($therapistId, $sessionId);
        break;
    case 'notes':
        $controller->notes($therapistId);
        break;
    case 'saveNote':
        $controller->saveNote($therapistId, $sessionId);
        break;
    case 'profile':
        $controller->profile($therapistId);
        break;
    case 'patients':
        $controller->patients($therapistId);
        break;
    case 'sendManagerNote':
        $controller->sendManagerNote($therapistId);
        break;
    case 'dashboard':
    default:
        $controller->dashboard($therapistId);
        break;
}
