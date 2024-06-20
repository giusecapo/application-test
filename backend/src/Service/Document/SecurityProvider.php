<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\DocumentInterface;
use App\Contract\Document\UserInterface;
use \InvalidArgumentException as InvalidArgumentException;
use App\Service\Constant\ExceptionCodes;
use App\Service\QueryHelper\QueryCriteria;
use App\Utility\CollectionValidator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Exposes methods to check users authorization 
 * for CRUD operations on documents
 */
final class SecurityProvider
{

    public function __construct(private Security $security)
    {
    }


    /**
     * Denies access if the user is not logged in or has not the required role
     */
    public function denyAccessUnlessRoleGranted(string $role): void
    {
        if (!$this->security->isGranted($role)) {
            $this->denyAccess();
        }
    }

    /**
     * Denies access if the user is not logged in or has not the required privilege on the given document
     */
    public function denyDocumentAccessUnlessGranted(string $attribute, DocumentInterface $document): void
    {
        if (!$this->security->isGranted($attribute, $document)) {
            $this->denyAccess();
        }
    }


    /**
     * Denies access if the user is not logged in or has not the required privilege on the given documents
     */
    public function denyDocumentsAccessUnlessGranted(string $attribute, Collection $documents): void
    {
        if (!CollectionValidator::hasOnlyInstancesOfClass($documents, DocumentInterface::class)) {
            throw new InvalidArgumentException(
                '$documents must contain only documents implementing App\Contract\Document\DocumentInterface',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        foreach ($documents->toArray() as $document) {
            $this->denyDocumentAccessUnlessGranted($attribute, $document);
        }
    }


    /**
     * Denies access if the user is not logged in or has not the required privilege
     */
    public function denyAccessUnlessGranted(string $attribute): void
    {
        if (!$this->security->isGranted($attribute)) {
            $this->denyAccess();
        }
    }

    public function getUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    public function getToken(): ?TokenInterface
    {
        return $this->security->getToken();
    }

    public function isGrantedRole(string $role): bool
    {
        return $this->security->getToken() !== null
            && $this->security->isGranted($role);
    }

    public function denyAccess(): void
    {
        //the user is anonymous (not logged in)
        //and cannot perform the action
        $currentUser = $this->security->getUser();
        if (!isset($currentUser)) {
            throw new UnauthorizedHttpException('', 'You are not authenticated.', null, ExceptionCodes::UNAUTHORIZED_EXCEPTION);
        }

        //the user is logged in but does not have 
        //the required privileges to perform the action
        throw new AccessDeniedHttpException(
            'Access denied. You do not have the required privileges to perform this action.',
            null,
            ExceptionCodes::ACCESS_DENIED_EXCEPTION
        );
    }
}
