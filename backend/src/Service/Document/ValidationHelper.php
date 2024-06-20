<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\DocumentInterface;
use App\Service\Constant\ExceptionCodes;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use \DomainException as DomainException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function count;

/**
 * The class ValidationHelper provides helper methods to validate
 * documents implementing the DocumentInterface with symfony validator
 */
final class ValidationHelper
{
    private const DEFAULT_CREATE_VALIDATION_GROUPS = ['create'];
    private const DEFAULT_READ_VALIDATION_GROUPS = ['read'];
    private const DEFAULT_UPDATE_VALIDATION_GROUPS = ['update'];


    public function __construct(protected ValidatorInterface $validator)
    {
    }

    /**  
     * @param  array  $validationGroups: no validation is performed if the given array is empty
     * @throws DomainException if the document is not valid
     */
    public function validate(DocumentInterface $document, array $validationGroups): void
    {
        if (count($validationGroups) === 0) {
            return;
        }

        $constraintViolations = $this->validator->validate($document, null, $validationGroups);
        if (count($constraintViolations) > 0) {
            /*
             * Uses a __toString method on the $constraintViolations variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            throw new DomainException(
                (string) $constraintViolations,
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }
    }


    /**
     * Validate a document and automatically use the default creation validation groups if $validationGroups is null.
     *
     * @param  array|null $validationGroups: no validation is performed if the given array is empty
     * 
     * @throws BadRequestHttpException it the document does not pass validation
     */
    public function validateOnCreate(DocumentInterface $document, ?array $validationGroups = null): void
    {
        $validationGroups = isset($validationGroups) ? $validationGroups : static::DEFAULT_CREATE_VALIDATION_GROUPS;
        try {
            $this->validate($document, $validationGroups);
        } catch (DomainException $exception) {
            throw new BadRequestHttpException(
                $exception->getMessage(),
                $exception,
                ExceptionCodes::INVALID_INPUT_DOCUMENT_EXCEPTION
            );
        }
    }


    /**
     * Validate a document and automatically use the default read validation groups if $validationGroups is null.
     *
     * @param  array|null $validationGroups: no validation is performed if the given array is empty
     * 
     * @throws DomainException it the document does not pass validation
     */
    public function validateOnRead(DocumentInterface $document, ?array $validationGroups = null): void
    {
        $validationGroups = isset($validationGroups) ? $validationGroups : static::DEFAULT_READ_VALIDATION_GROUPS;
        try {
            $this->validate($document, $validationGroups);
        } catch (DomainException $exception) {
            throw new DomainException(
                $exception->getMessage(),
                ExceptionCodes::INVALID_OUTPUT_DOCUMENT_EXCEPTION,
                $exception
            );
        }
    }

    /**
     * Validate the document and automatically use the default update validation groups if $validationGroups is null.
     *
     * @param  array|null $validationGroups: no validation is performed if the given array is empty
     * 
     * @throws BadRequestHttpException it the document does not pass validation
     */
    public function validateOnUpdate(DocumentInterface $document, ?array $validationGroups = null): void
    {
        $validationGroups = isset($validationGroups) ? $validationGroups : static::DEFAULT_UPDATE_VALIDATION_GROUPS;
        try {
            $this->validate($document, $validationGroups);
        } catch (DomainException $exception) {
            throw new BadRequestHttpException(
                $exception->getMessage(),
                $exception,
                ExceptionCodes::INVALID_INPUT_DOCUMENT_EXCEPTION
            );
        }
    }

    /**
     * Validates each given document
     *
     * @param  array $validationGroups: no validation is performed if the given array is empty
     */
    public function validateAll(Collection $documents, array $validationGroups): void
    {
        $documents = $documents->toArray();
        foreach ($documents as $document) {
            $this->validate($document, $validationGroups);
        }
    }


    /**
     * Validates each document in the $documents array.
     * and automatically uses the default read validation groups if $validationGroups is null.
     *
     * @param  array|null $validationGroups: no validation is performed if the given array is empty
     */
    public function validateAllOnRead(Collection $documents, ?array $validationGroups = null): void
    {
        $validationGroups = isset($validationGroups) ? $validationGroups : static::DEFAULT_READ_VALIDATION_GROUPS;
        $this->validateAll($documents, $validationGroups);
    }
}
