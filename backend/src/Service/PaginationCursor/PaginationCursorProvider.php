<?php

declare(strict_types=1);

namespace App\Service\PaginationCursor;

use App\Contract\Document\DocumentInterface;
use App\Service\Constant\ExceptionCodes;
use App\Utility\MethodsBuilder;
use \DateTime as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use \DomainException as DomainException;


/**
 * PaginationCursorProvider exposes the methods to synthesize and decode cursors.
 * The cursors can be used to implement cursor-based pagination of document's sub-sets.
 */
final class PaginationCursorProvider
{

    public function toDecodedPaginationCursor(DocumentInterface $document, string $sortBy): DecodedPaginationCursor
    {
        $cursorValueGetterMethod = MethodsBuilder::toGetMethod($sortBy);
        $cursorValue = $document->$cursorValueGetterMethod();
        $id = $document->getId();

        $decodedPaginationCursor = new DecodedPaginationCursor();
        $decodedPaginationCursor
            ->setCursorValue($cursorValue)
            ->setId($id);
        return $decodedPaginationCursor;
    }

    public function toDecodedPaginationCursors(ArrayCollection $documents, string $sortBy): Collection
    {
        $decodedPaginationCursors = new ArrayCollection();
        foreach ($documents as $document) {
            $decodedPaginationCursors->add($this->toDecodedPaginationCursor($document, $sortBy));
        }
        return $decodedPaginationCursors;
    }


    public function toPaginationCursor(DecodedPaginationCursor $decodedPaginationCursor): string
    {
        $id = $decodedPaginationCursor->getId();
        $cursorValue = $decodedPaginationCursor->getCursorValue();

        //DateTime and null are the only non-scalar value accepted as cursorValue

        //serialize the DateTime object as string.
        if ($cursorValue instanceof DateTime) {
            $cursorValue = sprintf('\DateTime:%s', $cursorValue->format('Y-m-d H:i:s.u'));
        }

        if (!isset($cursorValue)) {
            $cursorValue = '';
        }

        if (!is_scalar($cursorValue)) {
            throw new DomainException(
                "Only scalar types, DateTime and 'null' are allowed.",
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }

        $paginationCursor = sprintf('%s|%s', $id, $cursorValue);
        $opaquePaginationCursor =  base64_encode($paginationCursor);

        return $opaquePaginationCursor;
    }

    public function toPaginationCursors(ArrayCollection $decodedPaginationCursors): Collection
    {
        $paginationCursors = new ArrayCollection();
        $decodedPaginationCursors = $decodedPaginationCursors->toArray();
        foreach ($decodedPaginationCursors as $decodedPaginationCursor) {
            $paginationCursors->add($this->toPaginationCursor($decodedPaginationCursor));
        }
        return $paginationCursors;
    }

    public function documentToPaginationCursor(DocumentInterface $document, string $sortBy): string
    {
        $decodedPaginationCursor = $this->toDecodedPaginationCursor($document, $sortBy);
        $paginationCursor = $this->toPaginationCursor($decodedPaginationCursor);
        return $paginationCursor;
    }


    public function documentsToPaginationCursors(ArrayCollection $documents, string $sortBy): Collection
    {
        $paginationCursors = new ArrayCollection();
        foreach ($documents->toArray() as $document) {
            $paginationCursors->add($this->documentToPaginationCursor($document, $sortBy));
        }

        return $paginationCursors;
    }

    public function fromPaginationCursor(?string $paginationCursor): ?DecodedPaginationCursor
    {
        if (!isset($paginationCursor) || $paginationCursor === '') {
            return null;
        }

        $decodedPaginationCursorString = base64_decode($paginationCursor);
        $decodedPaginationCursorArray = explode('|', $decodedPaginationCursorString, 2);
        $id = $decodedPaginationCursorArray[0];
        $cursorValue = $decodedPaginationCursorArray[1];

        //DateTime objects are serialized in cursors with the prefix '\DateTime'
        //If the cursor value starts with \DateTime, we have to de-serialize it
        if (str_starts_with($cursorValue, '\DateTime:')) {
            $cursorValue = new DateTime(str_replace('\DateTime:', '', $cursorValue));
        }

        $decodedPaginationCursor = new DecodedPaginationCursor();
        $decodedPaginationCursor
            ->setId($id)
            ->setCursorValue($cursorValue);

        return $decodedPaginationCursor;
    }

    public function fromPaginationCursors(array $paginationCursors): Collection
    {
        $decodedPaginationCursors = new ArrayCollection();
        foreach ($paginationCursors as $paginationCursor) {
            $decodedPaginationCursors->add($this->fromPaginationCursor($paginationCursor));
        }
        return $decodedPaginationCursors;
    }
}
