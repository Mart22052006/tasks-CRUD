<?php
namespace App\Service;

use App\Entity\Task;
use App\DTO\TaskDto;
use App\Repository\TaskRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use App\Service\Interface\TaskServiceInterface;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private TaskRepository $repository,
        private ValidatorInterface $validator,
        private \App\Service\Mapper\TaskMapper $mapper
    ) {}

    /**
     * Создает задачу из DTO.
     *
     * @return array{task: ?Task, errors: ?ConstraintViolationListInterface}
     */
    public function createFromDto(TaskDto $dto): array
    {
        // Map DTO to entity
        $task = $this->mapper->fromDto($dto);

         $errors = $this->validator->validate($task, null, ['persist']);
         if (count($errors) > 0) {
             return [null, $errors];
         }

         $this->repository->save($task, true);
         return [$task, null];
     }

     /**
      * Обновляет задачу из DTO.
      *
      * @return array{task: ?Task, errors: ?ConstraintViolationListInterface}
      */
     public function updateFromDto(Task $task, TaskDto $dto, array $rawData = []): array
     {
        // Use mapper to update entity
+        $task = $this->mapper->updateFromDto($task, $dto, $rawData);

         $errors = $this->validator->validate($task, null, ['persist']);
         if (count($errors) > 0) {
             return [null, $errors];
         }

         $this->repository->save($task, true);
         return [$task, null];
     }

     /**
      * Удаляет задачу.
      */
     public function delete(Task $task): void
     {
         $this->repository->remove($task, true);
     }
 }