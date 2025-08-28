<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\DTO\RegisterDto;
use App\Service\UserService;
use App\Service\ErrorFormatterService;
use App\Service\Interface\UserServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

#[Route('/api')]
class RegisterController extends AbstractController
{
    use JsonRequestParserTrait;

    public function __construct(
        private UserServiceInterface $userService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
        ErrorFormatterService $errorFormatter // Добавляем зависимость
    ) {
        $this->setErrorFormatter($errorFormatter); // Устанавливаем форматтер в трейт
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    /**
     * @OA\Post(
     *   path="/api/register",
     *   tags={"User"},
     *   @OA\RequestBody(@Model(type=App\DTO\RegisterDto::class)),
     *   @OA\Response(response=201, description="User created", @Model(type=App\DTO\UserDto::class)),
     *   @OA\Response(response=400, description="Validation error"),
     *   @OA\Response(response=500, description="Server error")
     * )
     */
    public function register(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        try {
            $data = $this->parseJsonBody($request);
            if (!is_array($data)) {
                return $this->json([
                    'errors' => $this->formatErrors($data)
                ], 400);
            }

            /** @var RegisterDto $dto */
            $dto = $this->serializer->denormalize($data, RegisterDto::class);
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json(['errors' => $this->formatErrors($errors)], 400);
            }

            [$user, $errors] = $this->userService->createFromDto($dto);

            if ($errors !== null) {
                return $this->json([
                    'errors' => $this->formatErrors($errors)
                ], 400);
            }

            $location = $urlGenerator->generate('api_user_get', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->json($user, 201, ['Location' => $location], ['groups' => ['user:read']]);
        } catch (\Throwable $e) {
            $this->logger->error('User register failed: '.$e->getMessage(), ['exception' => $e]);
            return $this->json([
                'errors' => $this->formatErrors('Internal Server Error')
            ], 500);
        }
    }
}