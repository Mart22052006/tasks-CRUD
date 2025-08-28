<?php
namespace App\Service;

use App\Entity\User;
use App\DTO\RegisterDto;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use App\Service\Interface\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepository $repository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private \App\Service\Mapper\UserMapper $mapper
    ) {}

    /**
     * Создает пользователя из DTO.
     *
     * @return array{user: ?User, errors: array|ConstraintViolationListInterface|null}
     */
    public function createFromDto(RegisterDto $dto): array
    {
        $email = $dto->email;
        $plainPassword = $dto->password;

        if (!$email || !$plainPassword) {
            $errors = [];
            if (!$email) $errors['email'][] = 'Email is required';
            if (!$plainPassword) $errors['password'][] = 'Password is required';
            return [null, $errors];
        }

        // Map DTO to entity using mapper
        $user = $this->mapper->fromRegisterDto($dto);
        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $validationErrors = $this->validator->validate($user);
        if (count($validationErrors) > 0) {
            return [null, $validationErrors];
        }

        try {
            $this->repository->add($user, true);
            return [$user, null];
        } catch (UniqueConstraintViolationException $e) {
            return [null, ['email' => ['This email is already used.']]];
        }
    }
}