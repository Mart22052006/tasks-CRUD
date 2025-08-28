<?php

namespace App\Tests\Service;

use App\DTO\RegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserServiceTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $dto = new RegisterDto();
        $dto->email = 'test@example.test';
        $dto->password = 'secret123';

        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(User::class), $dto->password)
            ->willReturn('hashed');

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $repo->expects(self::once())->method('add')->with(self::isInstanceOf(User::class), true);

        $service = new UserService($repo, $passwordHasher, $validator);

        [$user, $errors] = $service->createFromDto($dto);

        self::assertNull($errors);
        self::assertInstanceOf(User::class, $user);
        self::assertSame('test@example.test', $user->getEmail());
        self::assertSame('hashed', $user->getPassword());
    }

    public function testCreateValidationFailure(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $dto = new RegisterDto();
        $dto->email = 'bad email';
        $dto->password = 'pw';

        $violation = new ConstraintViolation('Invalid', null, [], null, 'email', 'bad');
        $list = new ConstraintViolationList([$violation]);
        $validator->method('validate')->willReturn($list);

        $service = new UserService($repo, $passwordHasher, $validator);

        [$user, $errors] = $service->createFromDto($dto);

        self::assertNull($user);
        self::assertSame($list, $errors);
    }

    public function testCreateDuplicateEmail(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $dto = new RegisterDto();
        $dto->email = 'dup@example.test';
        $dto->password = 'secret123';

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $repo->expects(self::once())->method('add')->willThrowException(new UniqueConstraintViolationException('dup'));

        $service = new UserService($repo, $passwordHasher, $validator);

        [$user, $errors] = $service->createFromDto($dto);

        self::assertNull($user);
        self::assertIsArray($errors);
        self::assertArrayHasKey('email', $errors);
    }
}
