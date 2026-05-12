<?php
require_once __DIR__ . "/../models/User.php";
class UserController
{
    public static function GetAllUsers(): array
    {
        return User::getAll();
    }
    public static function GetUserById(int $userId): ?array
    {
        return User::findById($userId);
    }
    public static function GetUserRole(int $userId): string|false
    {
        return User::getRole($userId);
    }
    public static function CreateUser(string $username, string $email, string $password, string $fullname, string $roleId, string $phone = ''): int
    {
        return User::create($username, $email, $password, $fullname, $roleId, $phone);
    }
}
