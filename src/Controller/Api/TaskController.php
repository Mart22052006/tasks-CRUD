<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\DTO\TaskDto;
use App\Service\TaskService;
use App\Service\ErrorFormatterService;
use App\Service\Interface\TaskServiceInterface;
use App\Repository\TaskRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use App\Controller\Api\JsonRequestParserTrait;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    use JsonRequestParserTrait;

    public function __construct(
        private TaskServiceInterface $taskService,
        private TaskRepository $repo,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
        ErrorFormatterService $errorFormatter
    ) {
        $this->setErrorFormatter($errorFormatter);
    }

    #[Route('', name: 'api_tasks_list', methods: ['GET'])]
    /**
     * @OA\Get(
     *   path="/api/tasks",
     *   tags={"Task"},
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="List of tasks"),
     *   @OA\Response(response=400, description="Invalid params"),
     *   @OA\Response(response=500, description="Server error")
     * )
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = max(1, min(100, (int)$request->query->get('limit', 10)));
            $status = $request->query->get('status');

            if ($status !== null && $status !== '') {
                $valid = [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE];
                if (!in_array($status, $valid, true)) {
                    return $this->json([
                        'errors' => $this->formatErrors('Invalid status value'),
                        'allowed' => $valid
                    ], 400);
                }
            }

            $result = $this->repo->findByStatusWithPagination($status, $page, $limit);

            $items = array_map(fn(Task $t) => json_decode(
                $this->serializer->serialize($t, 'json', ['groups' => ['task:read']]), true
            ), $result['items']);

            $total = $result['total'];
            $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 1;

            return $this->json([
                'items' => $items,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => $totalPages,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('List tasks failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json([
                'errors' => $this->formatErrors('Internal Server Error')
            ], 500);
        }
    }

    #[Route('', name: 'api_tasks_create', methods: ['POST'])]
    /**
     * @OA\Post(
     *   path="/api/tasks",
     *   tags={"Task"},
     *   @OA\RequestBody(@Model(type=App\DTO\TaskDto::class)),
     *   @OA\Response(response=201, description="Task created", @Model(type=App\DTO\TaskDto::class)),
     *   @OA\Response(response=400, description="Validation error"),
     *   @OA\Response(response=500, description="Server error")
     * )
     */
    public function create(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        try {
            $data = $this->parseJsonBody($request);
            if (!is_array($data)) {
                return $this->json([
                    'errors' => $this->formatErrors($data)
                ], 400);
            }

            /** @var TaskDto $dto */
            $dto = $this->serializer->denormalize($data, TaskDto::class);
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => $this->formatErrors($errors)], 400);
            }

            [$task, $errors] = $this->taskService->createFromDto($dto);
            if ($errors !== null) {
                return $this->json([
                    'errors' => $this->formatErrors($errors)
                ], 400);
            }

            $location = $urlGenerator->generate('api_tasks_get', ['id' => $task->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->json($task, 201, ['Location' => $location], ['groups' => ['task:read']]);
        } catch (\Throwable $e) {
            $this->logger->error('Create task failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json([
                'errors' => $this->formatErrors('Internal Server Error')
            ], 500);
        }
    }

    #[Route('/{id}', name: 'api_tasks_get', methods: ['GET'])]
    public function getTask(int $id): JsonResponse
    {
        $task = $this->repo->find($id);
        if (!$task) {
            return $this->json([
                'errors' => $this->formatErrors('Task not found')
            ], 404);
        }

        return $this->json($task, 200, [], ['groups' => ['task:read']]);
    }

    #[Route('/{id}', name: 'api_tasks_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $task = $this->repo->find($id);
            if (!$task) {
                return $this->json([
                    'errors' => $this->formatErrors('Task not found')
                ], 404);
            }

            $data = $this->parseJsonBody($request);
            if (!is_array($data)) {
                return $this->json([
                    'errors' => $this->formatErrors($data)
                ], 400);
            }

            /** @var TaskDto $dto */
            $dto = $this->serializer->denormalize($data, TaskDto::class);
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => $this->formatErrors($errors)], 400);
            }

            [$task, $errors] = $this->taskService->updateFromDto($task, $dto, $data);
            if ($errors !== null) {
                return $this->json([
                    'errors' => $this->formatErrors($errors)
                ], 400);
            }

            return $this->json($task, 200, [], ['groups' => ['task:read']]);
        } catch (\Throwable $e) {
            $this->logger->error('Update task failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json([
                'errors' => $this->formatErrors('Internal Server Error')
            ], 500);
        }
    }

    #[Route('/{id}', name: 'api_tasks_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $task = $this->repo->find($id);
            if (!$task) {
                return $this->json([
                    'errors' => $this->formatErrors('Task not found')
                ], 404);
            }

            $this->taskService->delete($task);
            return new JsonResponse(null, 204);
        } catch (\Throwable $e) {
            $this->logger->error('Delete task failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json([
                'errors' => $this->formatErrors('Internal Server Error')
            ], 500);
        }
    }
}