<?php
require_once __DIR__ . "/../models/Admin.php";
require_once __DIR__ . "/UserController.php";
class AdminController extends UserController
{
    public static function GetAllRoles(): array
    {
        return Admin::GetAllRoles();
    }
    public static function DeleteUser(int $userId): bool
    {
        return Admin::DeleteUser($userId);
    }
    public static function UpdateUser(int $userId, string $username, string $email, string $fullname, string $phone, int $roleId): bool
    {
        return Admin::UpdateUser($userId, $username, $email, $fullname, $phone, $roleId);
    }
    public static function GetAllViolationReports()
    {
        return Admin::GetAllViolationReports();
    }
    public static function ChangeViolationReportStatus(int $reportid, string $status)
    {
        return Admin::ChangeViolationReportStatus($reportid, $status);
    }
    public static function GiveWarning(int $userid, string $reason)
    {
        return Admin::GiveWarning($userid, $reason);
    }
    public static function GiveBan(int $userId)
    {
        return Admin::GiveBan($userId);
    }
    public static function ActivateUser(int $userId)
    {
        return Admin::ActivateUser($userId);
    }
}
