<?php
namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use App\Service\ErrorFormatterService;

trait JsonRequestParserTrait
{
    private ErrorFormatterService $errorFormatter;

    public function setErrorFormatter(ErrorFormatterService $errorFormatter): void
    {
        $this->errorFormatter = $errorFormatter;
    }

    private function parseJsonBody(Request $request): array|string
    {
        $content = (string) $request->getContent();
        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON: ' . json_last_error_msg();
        }

        if (!is_array($data)) {
            return 'Expected JSON object/array';
        }

        return $data;
    }

    private function formatErrors(string|array|ConstraintViolationListInterface $errors): array
    {
        return $this->errorFormatter->format($errors);
    }
}
