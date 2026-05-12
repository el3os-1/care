<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Patient.php';

class PatientController extends BaseController
{
    private Patient $patientModel;

    public function __construct()
    {
        parent::__construct();
        $this->patientModel = new Patient();
    }

    public function dashboard(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        $upcomingSessions = $this->patientModel->getUpcomingSessions((int)$patient['id']);
        $moodLogs = $this->patientModel->getMoodLogs((int)$patient['id']);
        $assignedTherapist = $this->patientModel->getAssignedTherapist((int)$patient['id']);
        $therapistNotes = $this->patientModel->getRecentTherapistNotes((int)$patient['id']);

        require __DIR__ . '/../views/patient/dashboard.php';
    }

    public function intakeForm(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $history = trim($_POST['medical_history'] ?? '');
            $this->patientModel->completeIntakeForm((int)$patient['id'], $history);
            $this->redirect('/clinic/controllers/patient_run.php?action=dashboard&msg=intake_saved');
        }

        require __DIR__ . '/../views/patient/intake_form.php';
    }

    public function agreements(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signature = trim($_POST['signature'] ?? '');
            $this->patientModel->signAgreement((int)$patient['id'], $signature);
            $this->redirect('/clinic/controllers/patient_run.php?action=dashboard&msg=agreement_signed');
        }

        require __DIR__ . '/../views/patient/agreements.php';
    }

    public function sessions(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);
        $sessions = $this->patientModel->getUpcomingSessions((int)$patient['id']);
        require __DIR__ . '/../views/patient/sessions.php';
    }

    public function paySession(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = (int)($_POST['payment_id'] ?? 0);
            if ($paymentId > 0) {
                $this->patientModel->payForSession((int)$patient['id'], $paymentId);
            }
        }

        $this->redirect('/clinic/controllers/patient_run.php?action=sessions&msg=paid');
    }

    public function favorites(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $therapistId = (int)($_POST['therapist_id'] ?? 0);
            if ($therapistId > 0) {
                $this->patientModel->addFavorite((int)$patient['id'], $therapistId);
            }
            $this->redirect('/clinic/controllers/patient_run.php?action=favorites&msg=saved');
        }

        $favorites = $this->patientModel->getFavorites((int)$patient['id']);
        require __DIR__ . '/../views/patient/favorites.php';
    }

    public function logMood(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mood = trim($_POST['mood'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            if ($mood !== '') {
                $this->patientModel->addMoodLog((int)$patient['id'], $mood, $notes);
            }
        }

        $this->redirect('/clinic/controllers/patient_run.php?action=dashboard');
    }

    public function emergency(int $userId): void
    {
        $this->requireRole('patient');
        $patient = $this->ensurePatient($userId);

        if (isset($_GET['trigger'])) {
            $this->patientModel->triggerCrisis((int)$patient['id']);
            $message = 'Emergency alert sent.';
        }

        require __DIR__ . '/../views/patient/emergency.php';
    }

    private function ensurePatient(int $userId): array
    {
        $patient = $this->patientModel->getPatientByUserId($userId);
        if (!$patient) {
            http_response_code(404);
            echo 'Patient profile not found.';
            exit;
        }
        return $patient;
    }
}
