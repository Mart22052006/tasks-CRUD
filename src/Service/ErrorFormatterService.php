<?php
namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ErrorFormatterService
{
    public function format(string|array|ConstraintViolationListInterface $errors): array
    {
        if ($errors instanceof ConstraintViolationListInterface) {
            return $this->formatValidationErrors($errors);
        }
        
        if (is_string($errors)) {
            return ['global' => [$errors]];
        }
        
        if (is_array($errors)) {
            return $this->normalizeArrayErrors($errors);
        }
        
        return ['global' => ['Unknown error format']];
    }
    

    private function formatValidationErrors(ConstraintViolationListInterface $errors): array
    {
        $result = [];
        foreach ($errors as $violation) {
            $path = $violation->getPropertyPath() ?: 'global';
            $result[$path][] = $violation->getMessage();
        }
        return $result;
    }

    private function normalizeArrayErrors(array $errors): array
    {
        $result = [];
        
        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $value;
            } else {
                $result[$key] = [$value];
            }
        }
        
        return $result;
    }
}