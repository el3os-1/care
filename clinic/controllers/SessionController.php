<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Session.php';

class SessionController extends BaseController
{
    private Session $sessionModel;

    public function __construct()
    {
        parent::__construct();

        $this->sessionModel = new Session($this->db);
    }

    public function listSessions(): void
    {
        $this->requireRole('manager');

        $sessions = $this->sessionModel->getAllSessions();

        $this->view('manager/sessions', [
            'sessions' => $sessions,
            'filter'   => 'all'
        ]);
    }

    public function startSession(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(
                'session_run.php?action=listSessions'
            );
            return;
        }

        $sessionId = intval($_POST['session_id'] ?? 0);

        if (!empty($sessionId)) {
            $this->sessionModel->startSession($sessionId);
        }

        $this->redirect(
            'session_run.php?action=listSessions&msg=' .
                urlencode('Session started successfully.')
        );
    }

    public function endSession(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(
                'session_run.php?action=listSessions'
            );
            return;
        }

        $sessionId = intval($_POST['session_id'] ?? 0);

        if (!empty($sessionId)) {
            $this->sessionModel->endSession($sessionId);
        }

        $this->redirect(
            'session_run.php?action=listSessions&msg=' .
                urlencode('Session completed successfully.')
        );
    }

    public function cancelledSessions(): void
    {
        $this->requireRole('manager');

        $sessions = $this->sessionModel->getCancelledSessions();

        $this->view('manager/sessions', [
            'sessions' => $sessions,
            'filter'   => 'cancelled'
        ]);
    }

    public function processRefund(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(
                'session_run.php?action=cancelledSessions'
            );
            return;
        }

        $paymentId = intval($_POST['payment_id'] ?? 0);

        if (!empty($paymentId)) {
            $this->sessionModel->processRefund($paymentId);
        }

        $this->redirect(
            'session_run.php?action=cancelledSessions&msg=' .
                urlencode('Refund processed successfully.')
        );
    }

    public function applyFine(): void
    {
        $this->requireRole('manager');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(
                'session_run.php?action=cancelledSessions'
            );
            return;
        }

        $paymentId = intval($_POST['payment_id'] ?? 0);

        $fineAmount = floatval(
            $_POST['fine_amount'] ?? 100
        );

        if (!empty($paymentId)) {

            $this->sessionModel->applyLateFine(
                $paymentId,
                $fineAmount
            );
        }

        $this->redirect(
            'session_run.php?action=cancelledSessions&msg=' .
                urlencode('Late cancellation fine applied.')
        );
    }
}
