<?php

class Schedule {

    private $db; 

    public function __construct($db) {
        $this->db = $db;
    }

    
    public function getAllAppointments(): array {
        $result = $this->db->query("
            SELECT
                a.AppointmentId, a.ScheduledAt, a.Status, a.CancelReason,
                p.FullName AS PatientName,
                p.Email    AS PatientEmail,
                t.FullName AS TherapistName,
                pay.Amount, pay.Status AS PaymentStatus
            FROM Appointment a
            JOIN  Users   p   ON a.PatientId   = p.Id
            JOIN  Users   t   ON a.TherapistId = t.Id
            LEFT JOIN Payment pay ON a.AppointmentId = pay.AppointmentId
            ORDER BY a.ScheduledAt DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getUpcomingAppointments(): array {
        $result = $this->db->query("
            SELECT
                a.AppointmentId, a.ScheduledAt, a.Status,
                p.FullName AS PatientName,
                t.FullName AS TherapistName
            FROM Appointment a
            JOIN Users p ON a.PatientId   = p.Id
            JOIN Users t ON a.TherapistId = t.Id
            WHERE a.ScheduledAt >= NOW() AND a.Status = 'scheduled'
            ORDER BY a.ScheduledAt ASC
            LIMIT 20
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }


    public function isDoubleBooked(int $therapistId, string $scheduledAt, ?int $excludeId = null): bool {
        $tid = (int)$therapistId;
        $sat = $this->db->real_escape_string($scheduledAt);
        $exc = $excludeId ? "AND AppointmentId != " . (int)$excludeId : "";

        $result = $this->db->query("
            SELECT COUNT(*) AS cnt
            FROM Appointment
            WHERE TherapistId = $tid
              AND Status NOT IN ('cancelled')
              AND ABS(TIMESTAMPDIFF(MINUTE, ScheduledAt, '$sat')) < 60
              $exc
        ");
        $row = $result ? $result->fetch_assoc() : ['cnt' => 0];
        return (int)$row['cnt'] > 0;
    }

    public function createAppointment(int $patientId, int $therapistId, string $scheduledAt, float $amount = 200.00): int {
        if ($this->isDoubleBooked($therapistId, $scheduledAt)) {
            throw new Exception('Double booking: therapist already has an appointment within 60 minutes of that time.');
        }

        $pid = (int)$patientId;
        $tid = (int)$therapistId;
        $sat = $this->db->real_escape_string($scheduledAt);
        $amt = (float)$amount;

      
        $this->db->query("
            INSERT INTO Appointment (PatientId, TherapistId, ScheduledAt, Status)
            VALUES ($pid, $tid, '$sat', 'scheduled')
        ");
        if ($this->db->error) throw new Exception($this->db->error);
        $appointmentId = (int)$this->db->insert_id;

        $this->db->query("
            INSERT INTO Session (AppointmentId, Status)
            VALUES ($appointmentId, 'pending')
        ");


        $this->db->query("
            INSERT INTO Payment (AppointmentId, Amount, Status, Method)
            VALUES ($appointmentId, $amt, 'pending', 'online')
        ");

        return $appointmentId;
    }

    
    public function cancelAppointment(int $appointmentId, string $reason, bool &$lateFine = false): void {
        $id  = (int)$appointmentId;

        $result = $this->db->query("SELECT ScheduledAt FROM Appointment WHERE AppointmentId = $id");
        if (!$result || $result->num_rows === 0) throw new Exception('Appointment not found.');

        $appt       = $result->fetch_assoc();
        $hoursUntil = (strtotime($appt['ScheduledAt']) - time()) / 3600;
        $lateFine   = ($hoursUntil > 0 && $hoursUntil < 24);

        $esc = $this->db->real_escape_string($reason);

        $this->db->query("
            UPDATE Appointment SET Status = 'cancelled', CancelReason = '$esc'
            WHERE AppointmentId = $id
        ");
        $this->db->query("
            UPDATE Session SET Status = 'cancelled' WHERE AppointmentId = $id
        ");

        if ($lateFine) {
            $this->db->query("
                UPDATE Payment
                SET Amount = Amount * 0.5, Status = 'fine', RefundStatus = 'partial'
                WHERE AppointmentId = $id
            ");
        } else {
            $this->db->query("
                UPDATE Payment SET RefundStatus = 'pending_refund'
                WHERE AppointmentId = $id
            ");
        }
    }

    public function getAllTherapists(): array {
        $result = $this->db->query("
            SELECT u.Id, u.FullName, tp.Specialization, tp.IsSnoozed
            FROM Users u
            JOIN TherapistProfile tp ON u.Id = tp.UserId
            JOIN UserRoles        ur ON u.Id = ur.UserId
            JOIN Roles             r ON ur.RoleId = r.RoleId
            WHERE r.RoleName = 'therapist' AND u.IsActive = 1
            ORDER BY u.FullName
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }


    public function getAllPatients(): array {
        $result = $this->db->query("
            SELECT u.Id, u.FullName, u.Email, pp.Status
            FROM Users u
            JOIN PatientProfile pp ON u.Id = pp.UserId
            JOIN UserRoles      ur ON u.Id = ur.UserId
            JOIN Roles           r ON ur.RoleId = r.RoleId
            WHERE r.RoleName = 'patient' AND u.IsActive = 1
            ORDER BY u.FullName
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
