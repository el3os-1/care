<?php

require_once __DIR__ . '/../core/BaseController.php';

class Patient extends BaseController
{
    private ?string $favoritesTable = null;

    private function resolveFavoritesTable(): ?string
    {
        if ($this->favoritesTable !== null) {
            return $this->favoritesTable;
        }

        foreach (['patient_favorites', 'Patient_Favorites', 'PatientFavorites'] as $name) {
            $safe = $this->db->real_escape_string($name);
            $res = $this->db->query("SHOW TABLES LIKE '{$safe}'");
            if ($res && $res->num_rows > 0) {
                $this->favoritesTable = $name;
                return $this->favoritesTable;
            }
        }

        $this->favoritesTable = null;
        return null;
    }

    public function getPatientByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT pp.UserId AS id, pp.UserId AS user_id, pp.Age, pp.MedicalHistory,
                   pp.IsAnonymous, pp.Status, u.FullName, u.Email, u.Phone
            FROM PatientProfile pp
            JOIN Users u ON pp.UserId = u.Id
            WHERE pp.UserId = ?
        ');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function completeIntakeForm(int $patientId, string $medicalHistory = ''): bool
    {
        $stmt = $this->db->prepare('
            UPDATE PatientProfile
            SET MedicalHistory = ?, Status = "active"
            WHERE UserId = ?
        ');
        $stmt->bind_param('si', $medicalHistory, $patientId);
        return $stmt->execute();
    }

    public function signAgreement(int $patientId, string $signature = ''): bool
    {
        $content = 'Signed electronically' . ($signature !== '' ? ' by ' . $signature : '');
        $stmt = $this->db->prepare('
            INSERT INTO Agreement (PatientId, Content)
            VALUES (?, ?)
        ');
        $stmt->bind_param('is', $patientId, $content);
        return $stmt->execute();
    }

    public function addFavorite(int $patientId, int $therapistId): bool
    {
        $ok = false;

        $favTable = $this->resolveFavoritesTable();
        if ($favTable) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO {$favTable} (patient_id, therapist_id) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param('ii', $patientId, $therapistId);
                $ok = $stmt->execute();
            }
        }

        $message = 'Patient requested therapist #' . $therapistId . ' as a favorite/preference.';
        $nStmt = $this->db->prepare('INSERT INTO Notification (UserId, Message, Type) VALUES (?, ?, "favorite")');
        if ($nStmt) {
            $nStmt->bind_param('is', $patientId, $message);
            $nStmt->execute();
        }

        return $ok || ($favTable === null);
    }

    public function getFavorites(int $patientId): array
    {
        $favTable = $this->resolveFavoritesTable();

        if ($favTable) {
            $stmt = $this->db->prepare("
                SELECT u.Id AS id, u.FullName AS name, tp.Specialization AS specialties,
                       (tp.LicenseStatus = 'active') AS license_verified, tp.IsSnoozed AS is_snoozed
             FROM {$favTable} pf
            JOIN Users u ON pf.therapist_id = u.Id
            JOIN TherapistProfile tp ON u.Id = tp.UserId
            WHERE pf.patient_id = ?
                ORDER BY u.FullName
            ");
            if ($stmt) {
                $stmt->bind_param('i', $patientId);
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                if (!empty($rows)) {
                    return $rows;
                }
            }
        }

        $result = $this->db->query('
            SELECT u.Id AS id, u.FullName AS name, tp.Specialization AS specialties,
                   (tp.LicenseStatus = "active") AS license_verified, tp.IsSnoozed AS is_snoozed
            FROM Users u
            JOIN TherapistProfile tp ON u.Id = tp.UserId
            JOIN UserRoles ur ON u.Id = ur.UserId
            JOIN Roles r ON ur.RoleId = r.RoleId
            WHERE r.RoleName = "therapist" AND u.IsActive = 1
            ORDER BY u.FullName
        ');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getUpcomingSessions(int $patientId): array
    {
        $stmt = $this->db->prepare('
            SELECT s.SessionId AS id, t.FullName AS therapist_name,
                   a.ScheduledAt AS date, COALESCE(s.Status, a.Status) AS status,
                   "" AS notes,
                   pay.PaymentId AS payment_id,
                   COALESCE(pay.Status, "pending") AS payment_status,
                   COALESCE(pay.Amount, 0) AS payment_amount,
                   COALESCE(pay.RefundStatus, "none") AS refund_status
            FROM Appointment a
            LEFT JOIN Session s ON a.AppointmentId = s.AppointmentId
            LEFT JOIN Users t ON a.TherapistId = t.Id
            LEFT JOIN Payment pay ON pay.AppointmentId = a.AppointmentId
            WHERE a.PatientId = ? AND a.ScheduledAt >= NOW()
            ORDER BY a.ScheduledAt ASC
        ');
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function payForSession(int $patientId, int $paymentId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE Payment pay
            JOIN Appointment a ON a.AppointmentId = pay.AppointmentId
            SET pay.Status = "paid"
            WHERE pay.PaymentId = ?
              AND a.PatientId = ?
              AND pay.Status IN ("pending", "fine")
        ');
        $stmt->bind_param('ii', $paymentId, $patientId);
        return $stmt->execute();
    }

    public function getMoodLogs(int $patientId): array
    {
        $stmt = $this->db->prepare('
            SELECT LogId AS id, MoodScore AS mood, Notes AS notes, LogDate AS date, SleepHours AS sleep_hours
            FROM DailyLog
            WHERE PatientId = ?
            ORDER BY LogDate DESC
        ');
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAssignedTherapist(int $patientId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.Id, u.FullName, u.Email, u.Phone, tp.Specialization, tp.LicenseStatus
            FROM Appointment a
            JOIN Users u ON a.TherapistId = u.Id
            JOIN UserRoles ur ON ur.UserId = u.Id
            JOIN Roles r ON r.RoleId = ur.RoleId AND r.RoleName = "therapist"
            LEFT JOIN TherapistProfile tp ON tp.UserId = u.Id
            WHERE a.PatientId = ?
              AND a.TherapistId <> a.PatientId
            ORDER BY a.ScheduledAt DESC
            LIMIT 1
        ');
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function getRecentTherapistNotes(int $patientId): array
    {
        $table = 'ClinicalNote';
        foreach (['ClinicalNote', 'clinicnote', 'ClinicNote'] as $candidate) {
            $safe = $this->db->real_escape_string($candidate);
            $res = $this->db->query("SHOW TABLES LIKE '{$safe}'");
            if ($res && $res->num_rows > 0) {
                $table = $candidate;
                break;
            }
        }

        $stmt = $this->db->prepare("
            SELECT cn.Content, cn.CreatedAt, t.FullName AS therapist_name
            FROM {$table} cn
            JOIN Session s ON s.SessionId = cn.SessionId
            JOIN Appointment a ON a.AppointmentId = s.AppointmentId
            JOIN Users t ON t.Id = cn.TherapistId
            WHERE a.PatientId = ?
            ORDER BY cn.CreatedAt DESC
            LIMIT 5
        ");
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addMoodLog(int $patientId, string $mood, string $notes = ''): bool
    {
        $score = $this->moodToScore($mood);
        $stmt = $this->db->prepare('
            INSERT INTO DailyLog (PatientId, MoodScore, Notes)
            VALUES (?, ?, ?)
        ');
        $stmt->bind_param('iis', $patientId, $score, $notes);
        return $stmt->execute();
    }

    public function triggerCrisis(int $patientId, string $severity = 'high'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO CrisisAlert (PatientId, Severity, Status) VALUES (?, ?, "open")');
        $stmt->bind_param('is', $patientId, $severity);
        return $stmt->execute();
    }

    private function moodToScore(string $mood): int
    {
        return match (strtolower($mood)) {
            'happy' => 8,
            'calm' => 7,
            'anxious' => 4,
            'sad' => 3,
            default => max(1, min(10, (int) $mood)),
        };
    }
}
