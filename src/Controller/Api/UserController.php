<?php
namespace App\Controller\Api;

use App\DTO\UserDto;
use App\Service\Interface\UserQueryServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ErrorFormatterService;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private UserQueryServiceInterface $userQueryService,
        private LoggerInterface $logger,
        private ErrorFormatterService $errorFormatter
    ) {}

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(): JsonResponse
    {
        try {
            $users = $this->userQueryService->getAllUsers();
            return $this->json(['items' => $users], 200, [], ['groups' => ['user:read']]);
        } catch (\Throwable $e) {
            $this->logger->error('List users failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json(['error' => 'Internal Server Error'], 500);
        }
    }

    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }
        $userData = $this->userQueryService->getUserById($user->getId());
        return $this->json($userData, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'api_user_get', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getById(int $id): JsonResponse
    {
        $userData = $this->userQueryService->getUserById($id);
        if (!$userData) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        return $this->json($userData, 200, [], ['groups' => ['user:read']]);
    }
}
