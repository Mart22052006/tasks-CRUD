<?php
namespace App\Service\Mapper;

use App\DTO\RegisterDto;
use App\DTO\UserDto;
use App\Entity\User;

class UserMapper
{
    public function fromRegisterDto(RegisterDto $dto): User
    {
        $user = new User();
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        return $user;
    }

    public function toDto(User $user): UserDto
    {
        $dto = new UserDto();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->roles = $user->getRoles();
        return $dto;
    }

    public function updateFromDto(User $user, UserDto $dto): User
    {
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }
        if (!empty($dto->roles)) {
            $user->setRoles($dto->roles);
        }
        return $user;
    }
}
