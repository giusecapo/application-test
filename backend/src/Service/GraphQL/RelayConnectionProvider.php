<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Contract\Document\DocumentInterface;
use App\Service\GraphQL\PageInfos;
use App\Service\GraphQL\RelayConnection;
use App\Service\QueryHelper\SubsetQueryResult;
use App\Service\PaginationCursor\PaginationCursorProvider;
use Doctrine\Common\Collections\ArrayCollection;


final class RelayConnectionProvider
{

    public function __construct(private PaginationCursorProvider $paginationCursorProvider)
    {
    }

    /**
     * Given a SubsetQueryResult object, builds a Connection object from its data
     */
    public function toConnection(SubsetQueryResult $subsetQueryResult): RelayConnection
    {
        $sortBy = $subsetQueryResult
            ->getQueryCriteria()
            ->getSortingDescriptor()
            ->getSortBy();

        $edges = $this->toEdges($subsetQueryResult->getDocuments(), $sortBy);
        $pageInfos = $this->toPageInfos($subsetQueryResult);

        $relayConnection = new RelayConnection();
        $relayConnection
            ->setEdges($edges)
            ->setPageInfos($pageInfos)
            ->setTotalCount($subsetQueryResult->getDocumentsTotalCount())
            ->setQueryCriteria($subsetQueryResult->getQueryCriteria());

        return $relayConnection;
    }

    private function toPageInfos(SubsetQueryResult $subsetQueryResult): PageInfos
    {
        $sortBy = $subsetQueryResult
            ->getQueryCriteria()
            ->getSortingDescriptor()
            ->getSortBy();

        $firstDocument = $subsetQueryResult->getDocuments()->first();
        $startCursor = isset($firstDocument) && $firstDocument !== false
            ? $this->paginationCursorProvider->documentToPaginationCursor($firstDocument, $sortBy)
            : null;

        $lastDocument = $subsetQueryResult->getDocuments()->last();
        $endCursor = isset($lastDocument)  && $lastDocument !== false
            ? $this->paginationCursorProvider->documentToPaginationCursor($lastDocument, $sortBy)
            : null;

        $pageInfos = new PageInfos();
        $pageInfos
            ->setStartCursor($startCursor)
            ->setEndCursor($endCursor)
            ->setHasPreviousPage($subsetQueryResult->getHasPreviousPage())
            ->setHasNextPage($subsetQueryResult->getHasNextPage())
            ->setIsPaginatingForward($subsetQueryResult->getQueryCriteria()->getCursorSubsetDescriptor()->getFirst() !== null);

        return $pageInfos;
    }

    private function toEdges(ArrayCollection $documents, string $sortBy): ArrayCollection
    {
        $edges = new ArrayCollection();
        $documents = $documents->toArray();
        foreach ($documents as $document) {
            $edges->add($this->toEdge($document, $sortBy));
        }
        return $edges;
    }

    private function toEdge(DocumentInterface $document, string $sortBy): Edge
    {
        $cursor = $this->paginationCursorProvider->documentToPaginationCursor($document, $sortBy);
        $edge = new Edge();
        $edge
            ->setNode($document)
            ->setCursor($cursor);

        return $edge;
    }
}
