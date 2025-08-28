<?php
namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    #[Groups(['user:read'])]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'Invalid email.')]
    #[Groups(['user:read', 'user:write'])]
    public ?string $email = null;

    #[Groups(['user:read'])]
    public array $roles = [];
}
