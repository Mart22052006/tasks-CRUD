<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterDto
{
    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'Invalid email.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Password is required.')]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters.')]
    public ?string $password = null;
}
