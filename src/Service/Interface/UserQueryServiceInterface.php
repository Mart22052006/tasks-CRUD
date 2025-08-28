<?php
namespace App\Service\Interface;

use App\DTO\UserDto;

interface UserQueryServiceInterface
{
    /**
     * Возвращает список DTO пользователей
     */
    public function getAllUsers(): array; // array<UserDto>
    /**
     * Возвращает DTO пользователя или null
     */
    public function getUserById(int $id): ?UserDto;
}