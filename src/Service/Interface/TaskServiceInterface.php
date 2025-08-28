<?php
namespace App\Service\Interface;

use App\Entity\Task;
use App\DTO\TaskDto;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface TaskServiceInterface
{
    /**
     * @return array{task: ?Task, errors: ?ConstraintViolationListInterface}
     */
    public function createFromDto(TaskDto $dto): array;
    
    /**
     * @param array $rawData Raw request data to distinguish absent fields from explicit nulls when needed.
     * @return array{task: ?Task, errors: ?ConstraintViolationListInterface}
     */
    public function updateFromDto(Task $task, TaskDto $dto, array $rawData = []): array;
    
    public function delete(Task $task): void;
}