<?php
require_once __DIR__ . "/User.php";
require_once __DIR__ . "/../config/db.php";
class Admin extends User
{
    public static function GetAllRoles(): array
    {
        $db = Database::getConnection();
        $result = $db->query('SELECT * FROM Roles');
        return $result->fetch_all(MYSQLI_ASSOC);
    }
public static function DeleteUser(int $userId): bool
{
    $db = Database::getConnection();

    $cleanup = [

      
        'DELETE FROM Appointment WHERE PatientId = ?',
        'DELETE FROM CrisisAlert WHERE PatientId = ?',
        'DELETE FROM DailyLog WHERE PatientId = ?',
        'DELETE FROM Journal WHERE PatientId = ?',
        'DELETE FROM Notification WHERE UserId = ?',

 
        'DELETE FROM PatientProfile WHERE UserId = ?',
        'DELETE FROM TherapistProfile WHERE UserId = ?',
        'DELETE FROM AdminProfile WHERE UserId = ?',

        'DELETE FROM UserRoles WHERE UserId = ?',
    ];

    foreach ($cleanup as $sql) {

        $stmt = $db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    $stmt = $db->prepare('DELETE FROM Users WHERE Id = ?');

    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    $stmt->bind_param('i', $userId);

    return $stmt->execute();
}
    public static function UpdateUser(int $userId, string $username, string $email, string $fullname, string $phone, int $roleId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('
            UPDATE Users 
            SET Username = ?, Email = ?, FullName = ?, Phone = ? 
            WHERE Id = ?
        ');
        $stmt->bind_param('ssssi', $username, $email, $fullname, $phone, $userId);
        $rolestmt = $db->prepare('
            UPDATE UserRoles 
            SET RoleId = ? 
            WHERE UserId = ?
            ');
        $rolestmt->bind_param('ii', $roleId, $userId);
        $rolestmt->execute();
        return $stmt->execute();
    }
    public static function GetAllViolationReports(): array
    {
        $db = Database::getConnection();
        $res = $db->query("SELECT * FROM violationreport");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    public static function ChangeViolationReportStatus(int $reportid, string $status): bool
    {
        $db = Database::getConnection();
        $res = $db->prepare("UPDATE violationreport set Status=? where reportid=?");
        $res->bind_param("si", $status, $reportid);
        return $res->execute();
    }
    public static function GiveWarning(int $userId, string $mess)
    {
        $db = Database::getConnection();
        $message = "Official Admin Warning: " . $mess;
        $query = "INSERT INTO notification (UserId, Message) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $db->error);
        }

        $stmt->bind_param("is", $userId, $message);
        return $stmt->execute();
    }
    public static function GiveBan(int $userId): bool
    {
        $db = Database::getConnection();
        $query = "UPDATE users SET IsActive = 0 WHERE Id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    public static function ActivateUser(int $userId): bool
    {
        $db = Database::getConnection();
        $query = "UPDATE users SET IsActive = 1 WHERE Id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    public static function GetAllIntakeForms(): array
    {
        $db = Database::getConnection();
        $res = $db->query("
            SELECT
                pp.UserId AS FormId,
                pp.UserId AS PatientId,
                COALESCE(pp.MedicalHistory, '') AS Responses,
                NULL AS SubmittedAt,
                (pp.Status = 'verified') AS isVerified
            FROM PatientProfile pp
            ORDER BY pp.UserId DESC
        ");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public static function GetWeeklyMoodReports(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT dl.PatientId,
                   u.FullName AS patient_name,
                   AVG(dl.MoodScore) AS avg_mood,
                   COUNT(*) AS logs_count
            FROM DailyLog dl
            JOIN Users u ON dl.PatientId = u.Id
            WHERE dl.LogDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY dl.PatientId, u.FullName
            ORDER BY avg_mood ASC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function GetOpenCrisisAlertsCount(): int
    {
        $db = Database::getConnection();
        $res = $db->query("SELECT COUNT(*) AS total FROM CrisisAlert WHERE Status = 'open'");
        if (!$res) return 0;
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }
    public static function VerifyForm(int $formid, bool $IsAccepted): bool
    {
        $db = Database::getConnection();
        $status = $IsAccepted ? 'verified' : 'active';
        $res = $db->prepare("UPDATE PatientProfile SET Status = ? WHERE UserId = ?");
        $res->bind_param("si", $status, $formid);
        return $res->execute();
    }
    public static function GetPatientName(int $patientID): string
    {
        $db = Database::getConnection();
        $res = $db->prepare("SELECT username from users u where id=?");
        $res->bind_param("i", $patientID);
        $res->execute();
        $result = $res->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['username'] : 'Unknown';
    }
}
