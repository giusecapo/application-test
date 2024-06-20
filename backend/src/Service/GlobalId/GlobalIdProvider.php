<?php

declare(strict_types=1);

namespace App\Service\GlobalId;

use App\Contract\Document\DocumentInterface;
use \DomainException as DomainException;
use \InvalidArgumentException as InvalidArgumentException;
use App\Service\Constant\ExceptionCodes;
use App\Service\GlobalId\DecodedGlobalId;
use App\Utility\ArrayValidator;
use App\Utility\CappedSet;
use function count;
use function strlen;
use function is_string;

/**
 * This class exposes the methods to synthesize and decode guids
 * which can be used to identify a document and its kind.
 * The generated guid carries the information of the document's id and its class name.
 */
final class GlobalIdProvider
{

    private CappedSet $memoizedGlobalIds;

    private CappedSet $memoizedDecodedGlobalIds;

    public function __construct()
    {
        $this->memoizedGlobalIds = new CappedSet(100000);
        $this->memoizedDecodedGlobalIds = new CappedSet(100000);
    }

    /**
     * Combine the document's id and the type (class name) together
     * and base64-encodes the resulting string
     *
     * @throws DomainException if the document's id is not set (the document has no identity)
     */
    public function toGlobalId(DocumentInterface $document): string
    {
        if ($document->getId() === null) {
            throw new DomainException(
                'Cannot synthesize global id because the document\'s id is not set',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }


        return $this->getGlobalId($document->getDocumentName(), $document->getId());
    }


    /**
     * Combine the document's id and the type (class name) together
     * and base64-encodes the resulting string
     */
    public function getGlobalId(string $documentName, string $id): string
    {
        $memoizedGlobalIdKey = md5($documentName . '|' . $id);
        if (!$this->memoizedGlobalIds->isset($memoizedGlobalIdKey)) {
            $this->memoizedGlobalIds->add($memoizedGlobalIdKey, base64_encode(sprintf('%s|%s', $documentName, $id)));
        }

        return $this->memoizedGlobalIds->get($memoizedGlobalIdKey);
    }

    /**
     * Decode the base64-encoded guid and return a DecodedGuid object which contains the class name and id
     * 
     * @throws InvalidArgumentException if the guid is not valid (has a wrong format)
     */
    public function fromGlobalId(string $globalId): DecodedGlobalId
    {
        if (!$this->isValidGlobalId($globalId)) {
            throw new InvalidArgumentException(
                'The format of the global id is not valid and cannot be decoded.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        if (!$this->memoizedDecodedGlobalIds->isset($globalId)) {
            $decodedGlobalId = base64_decode($globalId);
            $splittedGlobalId = explode('|', $decodedGlobalId, 2);

            $decodedGlobalId = new DecodedGlobalId();
            $decodedGlobalId
                ->setDocumentName($splittedGlobalId[0])
                ->setId($splittedGlobalId[1]);

            $this->memoizedDecodedGlobalIds->add($globalId, $decodedGlobalId);
        }

        return $this->memoizedDecodedGlobalIds->get($globalId);
    }


    /**
     * Decodes a global id and returns only the id
     */
    public function getIdFromGlobalId(string $globalId): string
    {
        $globalId = $this->fromGlobalId($globalId);
        return $globalId->getId();
    }


    public function getIdsFromGlobalIds(array $globalIds): array
    {
        if (count($globalIds) > 0 && !ArrayValidator::hasOnlyStrings($globalIds)) {
            throw new InvalidArgumentException(
                'The $globalIds array must contain only strings',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $ids = array();
        foreach ($globalIds as $globalId) {
            $ids[] = $this->getIdFromGlobalId($globalId);
        }

        return $ids;
    }

    public function isValidGlobalId($globalId): bool
    {
        if (!is_string($globalId)) {
            return false;
        }

        $decodedGlobalId = base64_decode($globalId);
        $splittedGlobalId = explode('|', $decodedGlobalId, 2);

        if (
            count($splittedGlobalId) !== 2
            || !str_starts_with($splittedGlobalId[0], 'App')
            || strlen($splittedGlobalId[0]) === 0
            || strlen($splittedGlobalId[1]) === 0
        ) {
            return false;
        }

        return true;
    }
}
