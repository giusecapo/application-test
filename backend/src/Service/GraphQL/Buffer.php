<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Contract\Document\DocumentInterface;
use \DomainException as DomainException;
use App\Utility\MethodsBuilder;
use function count;

/**
 * The buffer service implements a generic and multi-document buffer
 * to load documents from storage with a unique query instead
 * (solves the n+1 problem in a graphql server)
 */
final class Buffer
{
    private const DEFAULT_BUFFER_SCOPE = 'default';

    private array $documentsBuffers;


    public function __construct()
    {
        $this->documentsBuffers = array();
    }


    /**
     * Add the identifier of a document to the buffer
     * to retrieve the documents of the current buffer from storage 
     * 
     * @param  string $identifierFieldName: the identifier field class name (e.g. getUsername)
     * @param  string|int|float|null $identifier: an identifier used to identify and retrieve a document from storage 
     * (id, key, username or any other unique field)
     * @param  string|null $bufferScope: restricts the buffer to a particular context, 
     * enabling the retrieval of identical data using the same identifier but through distinct queries.
     * (e.g. fetch users by ids, once with authorization check, once without it)
     */
    public function add(
        string $documentName,
        string $identifierFieldName,
        string|int|float|null $identifier,
        ?string $bufferScope = null
    ): void {

        $bufferName = $this->getBufferName($documentName, $identifierFieldName, $bufferScope);
        if (!isset($this->documentsBuffers[$bufferName])) {
            $this->documentsBuffers[$bufferName] = array();
        }

        //store the identifier as array key and null as the value, the actual object 
        //will be loaded on the first execution of Buffer::get
        $this->documentsBuffers[$bufferName][$identifier] = null;
    }


    /**
     * Resolve the buffer and return the required document
     *
     * @param  string $identifierFieldName: the identifier field class name (e.g. getUsername)
     * @param  string|int|float|null $identifier: an identifier used to identify and retrieve a document from storage 
     * @param  callable $dataLoaderCallback: the callback to execute once to load all the required documents from buffer
     * @param  string|null $bufferScope: restricts the buffer to a particular context, 
     * enabling the retrieval of identical data using the same identifier but through distinct queries.
     * (e.g. fetch users by ids, once with authorization check, once without it)
     */
    public function get(
        string $documentName,
        string $identifierFieldName,
        string|int|float|null $identifier,
        callable $dataLoaderCallback,
        ?string $bufferScope = null
    ): ?DocumentInterface {
        $bufferName = $this->getBufferName($documentName, $identifierFieldName, $bufferScope);

        if (count($this->documentsBuffers[$bufferName]) === 0) {
            throw new DomainException(
                'The buffer is empty. 
                You need to call the method add() at least once for this buffer
                before trying to retrieve a document from it.'
            );
        }

        if (!isset($this->documentsBuffers[$bufferName][$identifier])) {
            $identifiers = array_map(fn ($key) => (string)$key, array_keys($this->documentsBuffers[$bufferName]));
            $documents = $dataLoaderCallback($identifiers);

            //store the documents in the documentsBuffers array
            foreach ($documents as $document) {
                $identifierGetterMethod = MethodsBuilder::toGetMethod($identifierFieldName);
                $documentIdentifier = $document->$identifierGetterMethod();
                $this->documentsBuffers[$bufferName][$documentIdentifier] = $document;
            }
        }

        return $this->documentsBuffers[$bufferName][$identifier];
    }

    private function getBufferName(
        string $documentName,
        string $identifierFieldName,
        ?string $bufferScope = null
    ): string {
        return sprintf(
            '%s@%s@%s',
            $documentName,
            $bufferScope ?? self::DEFAULT_BUFFER_SCOPE,
            $identifierFieldName
        );
    }
}
