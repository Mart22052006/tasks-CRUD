<?php
namespace App\Service\Mapper;

use App\DTO\TaskDto;
use App\Entity\Task;

class TaskMapper
{
    public function fromDto(TaskDto $dto): Task
    {
        $task = new Task();
        if ($dto->title !== null) {
            $task->setTitle($dto->title);
        }
        $task->setDescription($dto->description ?? null);
        $task->setStatus($dto->status ?? Task::STATUS_TODO);
        return $task;
    }

    public function toDto(Task $task): TaskDto
    {
        $dto = new TaskDto();
        $dto->title = $task->getTitle();
        $dto->description = $task->getDescription();
        $dto->status = $task->getStatus();
        return $dto;
    }

    public function updateFromDto(Task $task, TaskDto $dto, array $rawData = []): Task
    {
        if (array_key_exists('title', $rawData) || $dto->title !== null) {
            $task->setTitle($dto->title ?? $task->getTitle());
        }
        if (array_key_exists('description', $rawData) || array_key_exists('description', $rawData)) {
            $task->setDescription($dto->description ?? $task->getDescription());
        }
        if (array_key_exists('status', $rawData) || $dto->status !== null) {
            $task->setStatus($dto->status ?? $task->getStatus());
        }
        return $task;
    }
}
