<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Contract\Document\DocumentInterface;
use App\Service\Constant\ExceptionCodes;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use GraphQL\Error\UserError;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The class ValidationHelper provides helper methods to validate
 * documents and present the error messages to the client.
 */
final class ValidationHelper
{

    public function __construct(
        protected ValidatorInterface $validator,
        private TranslatorInterface $translator
    ) {
    }


    /**
     * Validate a graphql object / type and throws a UserError
     * if the object is not valid
     */
    public function validateType(DocumentInterface $document, ?array $validationGroups = null): void
    {
        $constraintViolations = $this->validator->validate($document, null, $validationGroups);
        if ($constraintViolations->count() === 0) {
            return;
        }

        $validationErrorMessages = array();
        for ($i = 0; $i < $constraintViolations->count(); $i++) {
            $constraintViolation = $constraintViolations->get($i);
            $validationErrorMessages[] = array(
                $constraintViolation->getPropertyPath() => $constraintViolation->getMessage(),
                'code' => $constraintViolation->getCode(),
                'propertyPath' => $constraintViolation->getPropertyPath(),
                'path' => $this->propertyPathToPath($constraintViolation->getPropertyPath()),
                'localizedPath' => $this->propertyPathToLocalizedPath($constraintViolation->getPropertyPath(), $document),
                'message' => $constraintViolation->getMessage()
            );
        }

        throw new UserError(json_encode($validationErrorMessages), ExceptionCodes::INVALID_INPUT_DOCUMENT_EXCEPTION);
    }

    /**
     * Validate an input of any kind and throws a UserError
     * exception if the input is not valid.
     */
    public function validateInput(object $input, ?array $validationGroups = null): void
    {
        $constraintViolations = $this->validator->validate($input, null, $validationGroups);
        if ($constraintViolations->count() === 0) {
            return;
        }

        $validationErrorMessages = array();
        for ($i = 0; $i < $constraintViolations->count(); $i++) {
            $constraintViolation = $constraintViolations->get($i);
            $validationErrorMessages[] = array(
                $constraintViolation->getPropertyPath() => $constraintViolation->getMessage(),
                'code' => $constraintViolation->getCode(),
                'propertyPath' => $constraintViolation->getPropertyPath(),
                'path' => $this->propertyPathToPath($constraintViolation->getPropertyPath()),
                'localizedPath' => $this->propertyPathToLocalizedPath($constraintViolation->getPropertyPath(), $input),
                'message' => $constraintViolation->getMessage()
            );
        }

        throw new UserError(json_encode($validationErrorMessages), ExceptionCodes::INVALID_INPUT_ARGUMENTS_EXCEPTION);
    }

    private function propertyPathToPath(string $propertyPath): array
    {
        $propertyPath = str_replace('[', '.', $propertyPath);
        $propertyPath =  str_replace(']', '', $propertyPath);
        return array_map(
            fn (string $slug): int|string => is_numeric($slug) ? (int)$slug : $slug,
            explode('.', $propertyPath)
        );
    }

    private function propertyPathToLocalizedPath(string $propertyPath, object $inputOrDocument): array
    {
        $domain = $inputOrDocument instanceof DocumentInterface ? 'document' : 'graphql';
        $name = $inputOrDocument::class;
        $propertyPath = str_replace('[', '.', $propertyPath);
        $propertyPath =  str_replace(']', '', $propertyPath);

        return array_map(
            fn (string $slug): int|string => is_numeric($slug) ? (int)$slug + 1 : $this->translator->trans($name . '.' . $slug, [], $domain),
            explode('.', $propertyPath)
        );
    }
}
