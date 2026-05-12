<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController extends BaseController
{
    private Schedule $scheduleModel;

    public function __construct()
    {
        parent::__construct();

        $this->scheduleModel = new Schedule($this->db);
    }

    public function viewSchedule(): void
    {
        $this->requireRole('manager');

        $appointments = $this->scheduleModel->getAllAppointments();
        $therapists   = $this->scheduleModel->getAllTherapists();
        $patients     = $this->scheduleModel->getAllPatients();

        $message = $_GET['msg'] ?? '';
        $error   = $_GET['err'] ?? '';

        $this->view('manager/schedule', [
            'appointments' => $appointments,
            'therapists'   => $therapists,
            'patients'     => $patients,
            'message'      => $message,
            'error'        => $error
        ]);
    }

    public function createAppointment(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('schedule_run.php?action=viewSchedule');
            return;
        }

        $patientId   = intval($_POST['patient_id'] ?? 0);
        $therapistId = intval($_POST['therapist_id'] ?? 0);
        $scheduledAt = trim($_POST['scheduled_at'] ?? '');
        $amount      = floatval($_POST['amount'] ?? 200);

        if (
            empty($patientId) ||
            empty($therapistId) ||
            empty($scheduledAt)
        ) {
            $this->redirect(
                'schedule_run.php?action=viewSchedule&err=' .
                    urlencode('All fields are required.')
            );
            return;
        }

        try {

            $this->scheduleModel->createAppointment(
                $patientId,
                $therapistId,
                $scheduledAt,
                $amount
            );

            $this->redirect(
                'schedule_run.php?action=viewSchedule&msg=' .
                    urlencode('Appointment created successfully.')
            );
        } catch (Exception $e) {

            $this->redirect(
                'schedule_run.php?action=viewSchedule&err=' .
                    urlencode($e->getMessage())
            );
        }
    }

    public function cancelAppointment(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('schedule_run.php?action=viewSchedule');
            return;
        }

        $appointmentId = intval($_POST['appointment_id'] ?? 0);

        $reason = trim(
            $_POST['cancel_reason']
                ?? 'Cancelled by manager'
        );

        if (empty($appointmentId)) {

            $this->redirect(
                'schedule_run.php?action=viewSchedule&err=' .
                    urlencode('Invalid appointment.')
            );

            return;
        }

        try {

            $lateFine = false;

            $this->scheduleModel->cancelAppointment(
                $appointmentId,
                $reason,
                $lateFine
            );

            $message = 'Appointment cancelled successfully.';

            if ($lateFine) {
                $message .= ' Late cancellation fine applied (50%).';
            }

            $this->redirect(
                'schedule_run.php?action=viewSchedule&msg=' .
                    urlencode($message)
            );
        } catch (Exception $e) {

            $this->redirect(
                'schedule_run.php?action=viewSchedule&err=' .
                    urlencode($e->getMessage())
            );
        }
    }
}
