<?php
namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\Interface\UserQueryServiceInterface;
use App\DTO\UserDto;

class UserQueryService implements UserQueryServiceInterface
{
    public function __construct(
        private UserRepository $repository,
        private SerializerInterface $serializer
    ) {}

    /**
     * Получает всех пользователей и возвращает массив DTO объектов.
     */
    public function getAllUsers(): array
    {
        $users = $this->repository->findAll();
        return array_map(fn($u) => $this->mapToDto($u), $users);
    }

    /**
     * Получает пользователя по ID и возвращает DTO объект.
     */
    public function getUserById(int $id): ?UserDto
    {
        $user = $this->repository->find($id);
        if (!$user) {
            return null;
        }
        return $this->mapToDto($user);
    }

    private function mapToDto($user): UserDto
    {
        $dto = new UserDto();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->roles = $user->getRoles();

        return $dto;
    }
}