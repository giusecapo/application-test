<?php

declare(strict_types=1);

namespace App\DocumentModel\Mapper;

use App\Contract\Document\DocumentInterface;
use App\Contract\Document\EmbeddedDocumentInterface;
use App\Document\User;
use \InvalidArgumentException as InvalidArgumentException;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Service\Document\MappingHelper;
use App\Service\Constant\ExceptionCodes;

final class UserMapper implements MapperInterface
{

    public const DOCUMENT_NAME = User::class;

    public function __construct(private MappingHelper $mappingHelper)
    {
    }


    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }


    /**
     * @inheritDoc
     */
    public function map(DocumentInterface|EmbeddedDocumentInterface $user, array $input): void
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException(
                sprintf('$user must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $this->mappingHelper->mapSimple($user, 'id', $input);
        $this->mappingHelper->mapSimple($user, 'version', $input);
        $this->mappingHelper->mapSimple($user, 'username', $input);
        $this->mappingHelper->mapSimple($user, 'unhashedPassword', $input);
        $this->mappingHelper->mapSimple($user, 'currentUnhashedPassword', $input);
        $this->mappingHelper->mapSimple($user, 'roles', $input);
    }


    /**
     * @inheritDoc
     */
    public function unMap(DocumentInterface|EmbeddedDocumentInterface $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException(
                sprintf('$user must be an instance of %s', self::DOCUMENT_NAME),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        return [
            'id' => $user->getId(),
            'version' => $user->getVersion(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ];
    }
}
