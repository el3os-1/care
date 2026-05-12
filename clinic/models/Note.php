<?php

require_once __DIR__ . '/../core/BaseController.php';

class Note extends BaseController
{
    private string $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->resolveTableName(['ClinicalNote', 'clinicnote', 'ClinicNote']);
    }

    public function create(int $patientId, int $therapistId, string $content, ?int $sessionId = null): array
    {
        if ($sessionId === null) {
            $sessionId = $this->findLatestSessionId($patientId, $therapistId);
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (SessionId, TherapistId, Content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $sessionId, $therapistId, $content);
        $stmt->execute();

        $noteId = (int)$this->db->insert_id;

        return [
            'id' => $noteId,
            'patient_id' => $patientId,
            'therapist_id' => $therapistId,
            'session_id' => $sessionId,
            'content' => $content,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function getTherapistNotes(int $therapistId): array
    {
        $stmt = $this->db->prepare("
            SELECT cn.NoteId AS id, cn.SessionId AS session_id, cn.TherapistId AS therapist_id,
                   cn.Content AS content, cn.CreatedAt AS timestamp
            FROM {$this->table} cn
            WHERE cn.TherapistId = ?
            ORDER BY cn.CreatedAt DESC
        ");
        $stmt->bind_param('i', $therapistId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByTherapist(int $therapistId): array
    {
        return $this->getTherapistNotes($therapistId);
    }

    public function getBySession(int $sessionId): array
    {
        $stmt = $this->db->prepare("
            SELECT NoteId AS id, SessionId AS session_id, TherapistId AS therapist_id,
                   Content AS content, CreatedAt AS timestamp, Version AS version
            FROM {$this->table}
            WHERE SessionId = ?
            ORDER BY CreatedAt DESC
        ");
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update(int $noteId, int $therapistId, string $content): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET Content = ?, Version = Version + 1
            WHERE NoteId = ? AND TherapistId = ?
        ");
        $stmt->bind_param('sii', $content, $noteId, $therapistId);
        return $stmt->execute();
    }

    private function resolveTableName(array $candidates): string
    {
        foreach ($candidates as $name) {
            $safe = $this->db->real_escape_string($name);
            $res = $this->db->query("SHOW TABLES LIKE '{$safe}'");
            if ($res && $res->num_rows > 0) {
                return $name;
            }
        }

        return $candidates[0] ?? 'clinicnote';
    }

    private function findLatestSessionId(int $patientId, int $therapistId): ?int
    {
        $stmt = $this->db->prepare('
            SELECT s.SessionId
            FROM Session s
            JOIN Appointment a ON s.AppointmentId = a.AppointmentId
            WHERE a.PatientId = ? AND a.TherapistId = ?
            ORDER BY a.ScheduledAt DESC
            LIMIT 1
        ');
        $stmt->bind_param('ii', $patientId, $therapistId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (int)$row['SessionId'] : null;
    }
}
