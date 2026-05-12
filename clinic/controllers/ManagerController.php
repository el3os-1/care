<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Admin.php';

class ManagerController extends BaseController
{
    private Schedule $scheduleModel;
    private Session $sessionModel;

    public function __construct()
    {
        parent::__construct();

        $this->scheduleModel = new Schedule($this->db);
        $this->sessionModel  = new Session($this->db);
    }


    public function dashboard(): void
    {
        $this->requireRole('manager');

        $stats      = $this->sessionModel->getStats();
        $upcoming   = $this->scheduleModel->getUpcomingAppointments();
        $cancelled  = $this->sessionModel->getCancelledSessions();
        $unverified = $this->countUnverifiedLicenses();

        $this->view('manager/dashboard', [
            'stats'      => $stats,
            'upcoming'   => $upcoming,
            'cancelled'  => $cancelled,
            'unverified' => $unverified
        ]);
    }


    public function assignTherapist(): void
    {
        $this->requireRole('manager');

        $therapists = $this->scheduleModel->getAllTherapists();
        $patients   = $this->scheduleModel->getAllPatients();

        $message = '';
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $patientId   = intval($_POST['patient_id'] ?? 0);
            $therapistId = intval($_POST['therapist_id'] ?? 0);
            $scheduledAt = trim($_POST['scheduled_at'] ?? '');
            $amount      = floatval($_POST['amount'] ?? 200);

            if (
                empty($patientId) ||
                empty($therapistId) ||
                empty($scheduledAt)
            ) {

                $error = 'All fields are required.';
            } else {

                try {

                    $appointmentId = $this->scheduleModel->createAppointment(
                        $patientId,
                        $therapistId,
                        $scheduledAt,
                        $amount
                    );

                    $message = "Appointment #{$appointmentId} created successfully.";
                } catch (Exception $e) {

                    $error = $e->getMessage();
                }
            }
        }

        $this->view('manager/assign_therapist', [
            'therapists' => $therapists,
            'patients'   => $patients,
            'message'    => $message,
            'error'      => $error
        ]);
    }


    public function verifyTherapists(): void
    {
        $this->requireRole('manager');

        $message = '';
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $action    = $_POST['action'] ?? '';
            $licenseId = intval($_POST['license_id'] ?? 0);

            if (!$licenseId) {

                $error = 'Invalid license ID.';
            } else {

                switch ($action) {

                    case 'verify':

                        $this->setLicenseStatus($licenseId, 'valid');
                        $message = 'License verified successfully.';
                        break;

                    case 'renew':

                        $newExpiry = trim($_POST['new_expiry'] ?? '');

                        if (empty($newExpiry)) {

                            $error = 'New expiry date is required.';
                        } else {

                            $this->renewLicense($licenseId, $newExpiry);
                            $message = 'License renewed successfully.';
                        }

                        break;

                    case 'revoke':

                        $this->setLicenseStatus($licenseId, 'revoked');
                        $message = 'License revoked successfully.';
                        break;

                    default:

                        $error = 'Unknown action.';
                }
            }
        }

        $therapists = $this->getAllTherapistsWithLicenses();

        $this->view('manager/verify_therapists', [
            'therapists' => $therapists,
            'message'    => $message,
            'error'      => $error
        ]);
    }

    public function verifyIntakeForms(): void
    {
        $this->requireRole('manager');
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify') {
            $formId = (int)($_POST['id'] ?? 0);
            if ($formId > 0) {
                Admin::VerifyForm($formId, true);
                $message = 'Form verified successfully.';
            }
        }

        $forms = Admin::GetAllIntakeForms();
        foreach ($forms as &$form) {
            $form['PatientName'] = Admin::GetPatientName((int)$form['PatientId']);
            $form['TherapistName'] = $this->getAssignedTherapistName((int)$form['PatientId']);
        }

        $this->view('manager/verify_intake_forms', [
            'forms' => $forms,
            'message' => $message
        ]);
    }

    public function reports(): void
    {
        $this->requireRole('manager');
        $weeklyMoodReports = Admin::GetWeeklyMoodReports();
        $openCrisisAlerts = Admin::GetOpenCrisisAlertsCount();
        $recentCrisisAlerts = array_slice($this->getOpenCrisisAlerts(), 0, 5);

        $this->view('manager/reports', [
            'weeklyMoodReports' => $weeklyMoodReports,
            'openCrisisAlerts' => $openCrisisAlerts,
            'recentCrisisAlerts' => $recentCrisisAlerts
        ]);
    }

    public function crisisAlerts(): void
    {
        $this->requireRole('manager');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resolve') {
            $alertId = (int)($_POST['alert_id'] ?? 0);
            if ($alertId > 0) {
                $stmt = $this->db->prepare("UPDATE CrisisAlert SET Status = 'resolved' WHERE AlertId = ?");
                $stmt->bind_param('i', $alertId);
                $stmt->execute();
            }
        }
        $alerts = $this->getOpenCrisisAlerts();
        $this->view('manager/crisis_alerts', ['alerts' => $alerts]);
    }

    public function notes(): void
    {
        $this->requireRole('manager');
        $notes = $this->getManagerNotes();
        $this->view('manager/notes', ['notes' => $notes]);
    }

    private function setLicenseStatus(int $licenseId, string $status): void
    {
        $profileStatus = ($status === 'valid')
            ? 'active'
            : $status;

        $stmt = $this->db->prepare("
            UPDATE TherapistLicense
            SET Status = ?
            WHERE LicenseId = ?
        ");

        $stmt->bind_param("si", $status, $licenseId);
        $stmt->execute();

        $stmt2 = $this->db->prepare("
            UPDATE TherapistProfile tp
            JOIN TherapistLicense tl
            ON tp.UserId = tl.UserId
            SET tp.LicenseStatus = ?
            WHERE tl.LicenseId = ?
        ");

        $stmt2->bind_param("si", $profileStatus, $licenseId);
        $stmt2->execute();
    }

    private function renewLicense(int $licenseId, string $newExpiry): void
    {
        $stmt = $this->db->prepare("
            UPDATE TherapistLicense
            SET Status = 'valid',
                ExpiryDate = ?
            WHERE LicenseId = ?
        ");

        $stmt->bind_param("si", $newExpiry, $licenseId);
        $stmt->execute();

        $stmt2 = $this->db->prepare("
            UPDATE TherapistProfile tp
            JOIN TherapistLicense tl
            ON tp.UserId = tl.UserId
            SET tp.LicenseStatus = 'active',
                tp.LicenseExpiry = ?
            WHERE tl.LicenseId = ?
        ");

        $stmt2->bind_param("si", $newExpiry, $licenseId);
        $stmt2->execute();
    }

    private function getAllTherapistsWithLicenses(): array
    {
        $sql = "
            SELECT
                u.Id,
                u.FullName,
                u.Email,
                u.Phone,

                tp.Specialization,
                tp.LicenseStatus,
                tp.LicenseExpiry,
                tp.Rating,
                tp.IsSnoozed,

                tl.LicenseId,
                tl.LicenseNumber,
                tl.Issuer,
                tl.ExpiryDate,
                tl.Status AS LicStatus

            FROM Users u

            JOIN TherapistProfile tp
                ON u.Id = tp.UserId

            JOIN UserRoles ur
                ON u.Id = ur.UserId

            JOIN Roles r
                ON ur.RoleId = r.RoleId

            LEFT JOIN TherapistLicense tl
                ON u.Id = tl.UserId

            WHERE r.RoleName = 'therapist'

            ORDER BY tp.LicenseStatus, u.FullName
        ";

        $result = $this->db->query($sql);

        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function countUnverifiedLicenses(): int
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM TherapistLicense
            WHERE Status IN ('pending', 'expired', 'revoked')
        ";

        $result = $this->db->query($sql);

        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();

        return intval($row['total']);
    }

    private function getAssignedTherapistName(int $patientId): string
    {
        $stmt = $this->db->prepare("
            SELECT t.FullName
            FROM Appointment a
            JOIN Users t ON a.TherapistId = t.Id
            JOIN UserRoles tur ON tur.UserId = t.Id
            JOIN Roles tr ON tr.RoleId = tur.RoleId AND tr.RoleName = 'therapist'
            WHERE a.PatientId = ?
            ORDER BY a.ScheduledAt DESC
            LIMIT 1
        ");
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['FullName'] ?? 'Not assigned';
    }

    private function getOpenCrisisAlerts(): array
    {
        $sql = "
            SELECT ca.AlertId, ca.PatientId, ca.Severity, ca.Status, ca.CreatedAt,
                   p.FullName AS PatientName,
                   CASE WHEN tr.RoleId IS NOT NULL THEN t.FullName ELSE 'Not assigned' END AS TherapistName
            FROM CrisisAlert ca
            JOIN Users p ON ca.PatientId = p.Id
            LEFT JOIN (
                SELECT a1.PatientId, a1.TherapistId
                FROM Appointment a1
                JOIN (
                    SELECT PatientId, MAX(ScheduledAt) AS latest
                    FROM Appointment
                    GROUP BY PatientId
                ) latest_a ON latest_a.PatientId = a1.PatientId AND latest_a.latest = a1.ScheduledAt
            ) last_appt ON last_appt.PatientId = ca.PatientId
            LEFT JOIN Users t ON t.Id = last_appt.TherapistId
            LEFT JOIN UserRoles tur ON tur.UserId = t.Id
            LEFT JOIN Roles tr ON tr.RoleId = tur.RoleId AND tr.RoleName = 'therapist'
            WHERE ca.Status = 'open'
            ORDER BY ca.CreatedAt DESC
        ";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getManagerNotes(): array
    {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $stmt = $this->db->prepare("
            SELECT NotificationId, Message, Type, CreatedAt
            FROM Notification
            WHERE UserId = ?
              AND Type IN ('admin_manager_note', 'therapist_manager_note')
            ORDER BY CreatedAt DESC
        ");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
