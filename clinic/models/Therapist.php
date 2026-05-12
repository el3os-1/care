<?php

require_once __DIR__ . '/../core/BaseController.php';

class Therapist extends BaseController
{
    public function getProfile(int $therapistId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT tp.*, u.FullName, u.Email, u.Phone
            FROM TherapistProfile tp
            JOIN Users u ON tp.UserId = u.Id
            WHERE tp.UserId = ?
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function getUpcomingAppointments(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT a.AppointmentId AS id, a.ScheduledAt AS date, a.Status AS status,
                   p.FullName AS patient_name
            FROM Appointment a
            JOIN Users p ON a.PatientId = p.Id
            WHERE a.TherapistId = ? AND a.ScheduledAt >= NOW()
            ORDER BY a.ScheduledAt ASC
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTodaySessions(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT s.SessionId AS id, s.Status AS status, a.ScheduledAt AS date,
                   p.FullName AS patient_name
            FROM Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            JOIN Users p ON a.PatientId = p.Id
            WHERE a.TherapistId = ? AND DATE(a.ScheduledAt) = CURDATE()
            ORDER BY a.ScheduledAt ASC
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getWeeklyMoodReports(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT dl.PatientId, u.FullName AS patient_name, AVG(dl.MoodScore) AS avg_mood,
                   COUNT(*) AS logs_count
            FROM DailyLog dl
            JOIN Users u ON dl.PatientId = u.Id
            JOIN Appointment a ON a.PatientId = dl.PatientId
            WHERE a.TherapistId = ? AND dl.LogDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY dl.PatientId, u.FullName
            ORDER BY avg_mood ASC
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailability(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT AvailabilityId AS id, DayOfWeek AS day, StartTime AS start_time, EndTime AS end_time
            FROM Availability
            WHERE TherapistId = ?
            ORDER BY DayOfWeek
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function upsertAvailability(int $therapistId, int $day, string $start, string $end): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO Availability (TherapistId, DayOfWeek, StartTime, EndTime)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE StartTime = VALUES(StartTime), EndTime = VALUES(EndTime)
        ');
        $stmt->bind_param('iiss', $therapistId, $day, $start, $end);
        return $stmt->execute();
    }

    public function setSnooze(int $therapistId, int $snoozed): bool
    {
        $stmt = $this->db->prepare('UPDATE TherapistProfile SET IsSnoozed = ? WHERE UserId = ?');
        $stmt->bind_param('ii', $snoozed, $therapistId);
        return $stmt->execute();
    }

    public function notifyPatientsTherapistSnoozed(int $therapistId): bool
    {
        $message = 'Your therapist is temporarily unavailable.';
        $stmt = $this->db->prepare('
            INSERT INTO Notification (UserId, Message, Type)
            SELECT DISTINCT PatientId, ?, "therapist_snoozed"
            FROM Appointment
            WHERE TherapistId = ?
        ');
        $stmt->bind_param('si', $message, $therapistId);
        return $stmt->execute();
    }

    public function getSession(int $sessionId, int $therapistId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT s.SessionId AS id, s.*, a.ScheduledAt AS date, a.PatientId,
                   p.FullName AS patient_name
            FROM Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            JOIN Users p ON a.PatientId = p.Id
            WHERE s.SessionId = ? AND a.TherapistId = ?
        ');
        $stmt->bind_param('ii', $sessionId, $therapistId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function getPatientBySession(int $sessionId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.*
            FROM Users u
            JOIN Appointment a ON u.Id = a.PatientId
            JOIN Session s ON a.AppointmentId = s.AppointmentId
            WHERE s.SessionId = ?
        ');
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function hasLiveSession(int $therapistId): bool
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) AS total
            FROM Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            WHERE a.TherapistId = ? AND s.Status = "live"
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['total'] ?? 0) > 0;
    }

    public function startSession(int $sessionId, int $therapistId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            SET s.Status = "live", s.StartedAt = NOW(), a.Status = "live"
            WHERE s.SessionId = ? AND a.TherapistId = ?
        ');
        $stmt->bind_param('ii', $sessionId, $therapistId);
        return $stmt->execute();
    }

    public function endSession(int $sessionId, int $therapistId): bool
    {
        $stmt = $this->db->prepare('
            UPDATE Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            SET s.Status = "completed", s.EndedAt = NOW(), a.Status = "completed"
            WHERE s.SessionId = ? AND a.TherapistId = ?
        ');
        $stmt->bind_param('ii', $sessionId, $therapistId);
        return $stmt->execute();
    }

    public function triggerCrisisAlert(int $sessionId, int $therapistId, string $keyword): bool
    {
        $patient = $this->getPatientBySession($sessionId);
        if (!$patient) {
            return false;
        }
        $severity = 'keyword:' . $keyword;
        $stmt = $this->db->prepare('INSERT INTO CrisisAlert (PatientId, Severity, Status) VALUES (?, ?, "open")');
        $patientId = (int)$patient['Id'];
        $stmt->bind_param('is', $patientId, $severity);
        return $stmt->execute();
    }

    public function updateProfile(int $therapistId, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE TherapistProfile
            SET Specialization = ?, LicenseStatus = ?, LicenseExpiry = ?, IsSnoozed = ?
            WHERE UserId = ?
        ');
        $stmt->bind_param(
            'sssii',
            $data['Specialization'],
            $data['LicenseStatus'],
            $data['LicenseExpiry'],
            $data['IsSnoozed'],
            $therapistId
        );
        return $stmt->execute();
    }

    public function cancelAppointment(int $appointmentId, int $therapistId, string $reason): bool
    {
        $stmt = $this->db->prepare('
            UPDATE Appointment
            SET Status = "cancelled", CancelReason = ?
            WHERE AppointmentId = ? AND TherapistId = ?
        ');
        $stmt->bind_param('sii', $reason, $appointmentId, $therapistId);
        return $stmt->execute();
    }

    public function getPatients(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT u.Id, u.FullName, u.Email
            FROM Users u
            JOIN Appointment a ON u.Id = a.PatientId
            WHERE a.TherapistId = ?
            ORDER BY u.FullName
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSharedJournals(int $therapistId): array
    {
        $stmt = $this->db->prepare('
            SELECT j.*, u.FullName AS patient_name
            FROM Journal j
            JOIN Users u ON j.PatientId = u.Id
            JOIN Appointment a ON a.PatientId = j.PatientId
            WHERE a.TherapistId = ? AND j.IsPrivate = 0
            ORDER BY j.CreatedAt DESC
        ');
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPatientIfAssigned(int $therapistId, int $patientId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.*
            FROM Users u
            JOIN Appointment a ON u.Id = a.PatientId
            WHERE a.TherapistId = ? AND a.PatientId = ?
            LIMIT 1
        ');
        $stmt->bind_param('ii', $therapistId, $patientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function getWeeklyMoodLogs(int $patientId): array
    {
        $stmt = $this->db->prepare('
            SELECT *
            FROM DailyLog
            WHERE PatientId = ? AND LogDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY LogDate DESC
        ');
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}
