<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TaskDto
{
    #[Assert\NotBlank(message: 'Title must not be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Title cannot be longer than {{ limit }} characters.'
    )]
    public ?string $title = null;

    public ?string $description = null;

    #[Assert\Choice(
        choices: ['todo', 'in_progress', 'done'],
        message: 'Status must be one of: todo, in_progress, done.'
    )]
    public string $status = 'todo';
}