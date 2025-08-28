<?php

namespace App\Tests\Service;

use App\DTO\TaskDto;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TaskServiceTest extends TestCase
{
    public function testCreateSuccess(): void
    {
        $repo = $this->createMock(TaskRepository::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $dto = new TaskDto();
        $dto->title = 'Title';
        $dto->description = 'Desc';
        $dto->status = Task::STATUS_TODO;

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $repo->expects(self::once())->method('save')->with(self::isInstanceOf(Task::class), true);

        $service = new TaskService($repo, $validator);

        [$task, $errors] = $service->createFromDto($dto);

        self::assertNull($errors);
        self::assertInstanceOf(Task::class, $task);
        self::assertSame('Title', $task->getTitle());
    }

    public function testCreateValidationFail(): void
    {
        $repo = $this->createMock(TaskRepository::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $dto = new TaskDto();
        $dto->title = '';

        $violation = new ConstraintViolation('Title blank', null, [], null, 'title', '');
        $list = new ConstraintViolationList([$violation]);
        $validator->method('validate')->willReturn($list);

        $service = new TaskService($repo, $validator);

        [$task, $errors] = $service->createFromDto($dto);

        self::assertNull($task);
        self::assertSame($list, $errors);
    }

    public function testUpdateSuccess(): void
    {
        $repo = $this->createMock(TaskRepository::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $task = new Task();
        $task->setTitle('Old');

        $dto = new TaskDto();
        $dto->title = 'New';

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $repo->expects(self::once())->method('save')->with($task, true);

        $service = new TaskService($repo, $validator);

        [$updated, $errors] = $service->updateFromDto($task, $dto, ['title' => 'New']);

        self::assertNull($errors);
        self::assertSame('New', $updated->getTitle());
    }

    public function testDelete(): void
    {
        $repo = $this->createMock(TaskRepository::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $task = new Task();

        $repo->expects(self::once())->method('remove')->with($task, true);

        $service = new TaskService($repo, $validator);
        $service->delete($task);

        self::assertTrue(true);
    }
}
