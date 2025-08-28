<?php
namespace App\Service\Interface;

use App\Entity\User;
use App\DTO\RegisterDto;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface UserServiceInterface
{
    /**
     * @return array{user: ?User, errors: array|ConstraintViolationListInterface|null}
     */
    public function createFromDto(RegisterDto $dto): array;
}