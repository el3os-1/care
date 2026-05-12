<?php

class Session
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function getAllSessions(): array
    {
        $result = $this->db->query("
            SELECT
                s.SessionId, s.Status, s.StartedAt, s.EndedAt,
                a.AppointmentId, a.ScheduledAt, a.Status AS AppointmentStatus,
                a.CancelReason,
                p.FullName  AS PatientName,
                p.Email     AS PatientEmail,
                t.FullName  AS TherapistName,
                pay.PaymentId, pay.Amount,
                pay.Status      AS PaymentStatus,
                pay.RefundStatus
            FROM Session s
            JOIN  Appointment a   ON s.AppointmentId = a.AppointmentId
            JOIN  Users       p   ON a.PatientId     = p.Id
            JOIN  Users       t   ON a.TherapistId   = t.Id
            LEFT JOIN Payment pay ON a.AppointmentId = pay.AppointmentId
            ORDER BY a.ScheduledAt DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }


    public function getCancelledSessions(): array
    {
        $result = $this->db->query("
            SELECT
                a.AppointmentId, a.ScheduledAt, a.CancelReason,
                a.Status        AS AppointmentStatus,
                p.FullName      AS PatientName,
                p.Email         AS PatientEmail,
                t.FullName      AS TherapistName,
                pay.PaymentId,  pay.Amount,
                pay.Status      AS PaymentStatus,
                pay.RefundStatus,
                s.SessionId
            FROM Appointment a
            JOIN  Users   p   ON a.PatientId   = p.Id
            JOIN  Users   t   ON a.TherapistId = t.Id
            LEFT JOIN Payment pay ON a.AppointmentId = pay.AppointmentId
            LEFT JOIN Session s   ON a.AppointmentId = s.AppointmentId
            WHERE a.Status = 'cancelled'
            ORDER BY a.ScheduledAt DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getStats(): array
    {
        $result = $this->db->query("
            SELECT
                COUNT(*)                              AS total,
                SUM(a.Status = 'scheduled')           AS scheduled,
                SUM(s.Status = 'live')                AS live,
                SUM(s.Status = 'completed')           AS completed,
                SUM(a.Status = 'cancelled')           AS cancelled
            FROM Appointment a
            LEFT JOIN Session s ON a.AppointmentId = s.AppointmentId
        ");
        return $result ? $result->fetch_assoc() : [];
    }


    public function startSession(int $sessionId): void
    {
        $id = (int)$sessionId;
        $this->db->query("
            UPDATE Session SET Status = 'live', StartedAt = NOW()
            WHERE SessionId = $id
        ");
        $this->db->query("
            UPDATE Appointment a
            JOIN   Session     s ON a.AppointmentId = s.AppointmentId
            SET    a.Status = 'live'
            WHERE  s.SessionId = $id
        ");
    }


    public function endSession(int $sessionId): void
    {
        $id = (int)$sessionId;
        $this->db->query("
            UPDATE Session SET Status = 'completed', EndedAt = NOW()
            WHERE SessionId = $id
        ");
        $this->db->query("
            UPDATE Appointment a
            JOIN   Session     s ON a.AppointmentId = s.AppointmentId
            SET    a.Status = 'completed'
            WHERE  s.SessionId = $id
        ");
        $this->db->query("
            UPDATE Payment pay
            JOIN   Session  s ON pay.AppointmentId = s.AppointmentId
            SET    pay.Status = 'paid'
            WHERE  s.SessionId = $id AND pay.Status = 'pending'
        ");
    }


    public function processRefund(int $paymentId): void
    {
        $id = (int)$paymentId;
        $this->db->query("
            UPDATE Payment SET Status = 'refunded', RefundStatus = 'refunded'
            WHERE PaymentId = $id
        ");
    }


    public function applyLateFine(int $paymentId, float $fineAmount): void
    {
        $id   = (int)$paymentId;
        $fine = (float)$fineAmount;
        $this->db->query("
            UPDATE Payment
            SET Amount = $fine, Status = 'fine', RefundStatus = 'none'
            WHERE PaymentId = $id
        ");
    }
}
