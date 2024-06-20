<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\EquatableDocumentInterface;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use \InvalidArgumentException as InvalidArgumentException;
use App\Utility\CollectionManipulator;
use App\Utility\MethodsBuilder;
use App\Utility\ReflectionHelper;
use Doctrine\Common\Collections\ArrayCollection;
use \DateTime as DateTime;
use \Exception as Exception;
use \RuntimeException as RuntimeException;
use App\Service\Constant\ExceptionCodes;
use App\Service\QueryHelper\FiltersDescriptor;
use App\Service\QueryHelper\QueryCriteria;
use App\Service\QueryHelper\QueryHelper;
use App\Utility\CappedSet;
use \DateTimeZone as DateTimeZone;
use \Error as Error;
use function in_array;
use function array_key_exists;
use function is_string;
use function is_array;

final class MappingHelper
{
    //replace all entries of a collection with the given data
    public const MODE_PUT = 0;

    //update the entries and add the new data.
    public const MODE_PATCH = 1;

    private CappedSet $memoizedDateTimes;

    private CappedSet $memoizedReferences;

    private DateTimeZone $dateTimeZone;

    public function __construct(private QueryHelper $queryHelper)
    {
        $this->memoizedDateTimes = new CappedSet(10000);
        $this->memoizedReferences = new CappedSet(5000);
        $this->dateTimeZone = new DateTimeZone(date_default_timezone_get());
    }

    /**
     * Map int, float, string, bool, array and null 
     * Do not use with arrays of objects.
     */
    public function mapSimple(object $document, string $key, array $input): void
    {
        if (array_key_exists($key, $input)) {
            $value = $input[$key];
            $setterMethodForField = MethodsBuilder::toSetMethod($key);
            try {
                $document->$setterMethodForField($value);
            } catch (Error $error) {
                if (isset($value)) {
                    throw $error;
                }
            }
        }
    }

    /**
     * Map a json string (decodes json to an associative array)
     */
    public function mapJson(object $document, string $key, array $input): void
    {
        if (array_key_exists($key, $input)) {
            $value = json_decode($input[$key], true);
            $setterMethodForField = MethodsBuilder::toSetMethod($key);
            try {
                $document->$setterMethodForField($value);
            } catch (Error $error) {
                if (isset($value)) {
                    throw $error;
                }
            }
        }
    }


    /**
     * Map a valid date-time string to a DateTime object.
     * Works also with array of DateTime objects.
     */
    public function mapDateTime(object $document, string $key, array $input): void
    {
        if (!array_key_exists($key, $input)) {
            return;
        }

        $value = null;
        if (is_array($input[$key])) {
            $value = array();
            foreach ($input[$key] as $dateTime) {
                $value[] = $dateTime !== null ? $this->parseDateTime($dateTime) : null;
            }
        } else {
            $value = isset($input[$key]) && $input[$key] !== null ? $this->parseDateTime($input[$key]) : null;
        }

        $setterMethodForField = MethodsBuilder::toSetMethod($key);
        $document->$setterMethodForField($value);
    }


    private function parseDateTime(DateTime|string $dateTime): DateTime
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime;
        }

        if (is_string($dateTime)) {
            if (!$this->memoizedDateTimes->isset($dateTime) && DateTime::createFromFormat(DateTime::ATOM, $dateTime) !== false) {
                $this->memoizedDateTimes->add(
                    $dateTime,
                    (new DateTime($dateTime))->setTimezone($this->dateTimeZone)
                );
            }

            return clone $this->memoizedDateTimes->get($dateTime);
        }

        throw new InvalidArgumentException(
            'Cannot map input to DateTime: the input is not a valid iso8601 date time string or DateTime object',
            ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
        );
    }

    /**
     * Map a reference to a document by id
     */
    public function mapReference(
        object $document,
        string $key,
        array $input,
        string $referencedDocumentName
    ): void {
        if (array_key_exists($key, $input)) {
            $setterMethodForField = MethodsBuilder::toSetMethod($key);

            if (!isset($input[$key])) {
                $document->$setterMethodForField(null);
                return;
            }

            if (!isset($input[$key]['id'])) {
                throw new InvalidArgumentException(
                    "The \$input array has not the expected structure: the value '\$input[$key]' must be an array with the key 'id'",
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }

            $memoizedReferenceKey = md5($referencedDocumentName . '|' . $input[$key]['id']);

            if (!$this->memoizedReferences->isset($memoizedReferenceKey)) {
                $filtersDescriptor = new FiltersDescriptor();
                $filtersDescriptor->equals('id', $input[$key]['id']);

                $queryCriteria = new QueryCriteria();
                $queryCriteria->setFiltersDescriptor($filtersDescriptor);

                $referencedDocument = $this->queryHelper->getSingleResult($referencedDocumentName, $queryCriteria);

                $this->memoizedReferences->add($memoizedReferenceKey, $referencedDocument);
            }

            $document->$setterMethodForField($this->memoizedReferences->get($memoizedReferenceKey));
        }
    }

    /**
     * Map the references to the documents by id (1:n)
     * This method supports two update strategies: put and patch. Select the update strategy to use by setting
     * it in the $input array under the key <$key>UpdateMode (e.g.: if $key = 'names' and you want patch the current values,
     * set $input['namesUpdateMode'] = MappingHelper::MODE_PATCH). 
     */
    public function mapArrayCollectionOfReferences(
        object $document,
        string $key,
        array $input,
        string $referencedDocumentName
    ): void {
        if (array_key_exists($key, $input)) {
            $values = $input[$key] ?? [];
            $mode = $this->getUpdateMode($input, $key);
            switch ($mode) {
                case static::MODE_PUT:
                    $this->putArrayCollectionOfReferences($document, $key, $values, $referencedDocumentName);
                    break;
                case static::MODE_PATCH:
                    $this->patchArrayCollectionOfReferences($document, $key, $values, $referencedDocumentName);
                    break;
            }
        }
    }

    /**
     * Replace the current references with the new provided references
     */
    public function putArrayCollectionOfReferences(
        object $document,
        string $key,
        array $values,
        string $referencedDocumentName
    ): void {
        $referencedDocumentsIds = $this->valuesToIds($values);

        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->in('id', $referencedDocumentsIds);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        $referencedDocuments = $this->queryHelper->find($referencedDocumentName, $queryCriteria);

        $setterMethodForField = MethodsBuilder::toSetMethod($key);
        $document->$setterMethodForField($referencedDocuments);
    }

    /**
     * Merge the current references and the new references
     */
    public function patchArrayCollectionOfReferences(
        object $document,
        string $key,
        array $values,
        string $referencedDocumentName
    ): void {
        $referencedDocuments = ReflectionHelper::getPrivateProp($document, $key);

        $collectionManipulator = new CollectionManipulator($referencedDocuments);
        $referencedDocumentsIds = array_merge(
            $collectionManipulator->extractPropsFromObjs('getId')->get()->toArray(),
            $this->valuesToIds($values)
        );

        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->in('id', $referencedDocumentsIds);

        $queryCriteria = new QueryCriteria();
        $queryCriteria->setFiltersDescriptor($filtersDescriptor);

        $referencedDocuments = $this->queryHelper->find($referencedDocumentName, $queryCriteria);

        $setterMethodForField = MethodsBuilder::toSetMethod($key);
        $document->$setterMethodForField($referencedDocuments);
    }


    public function mapEmbeddedDocument(
        object $document,
        string $key,
        array $input,
        MapperInterface $documentMapper
    ): void {
        if (array_key_exists($key, $input) && is_array($input[$key])) {
            $documentName = $documentMapper->getDocumentName();
            $embeddedDocument = new $documentName();
            $documentMapper->map($embeddedDocument, $input[$key]);
            $setterMethodForField = MethodsBuilder::toSetMethod($key);
            $document->$setterMethodForField($embeddedDocument);
        } else {
            $this->mapSimple($document, $key, $input);
        }
    }


    /**
     * This method supports two update strategies: put and patch. Select the update strategy to use by setting
     * it in the $input array under the key UpdateMode (e.g.: if $key = 'names' and you want patch the current values,
     * set $input['namesUpdateMode'] = MappingHelper::MODE_PATCH). 
     */
    public function mapArrayCollectionOfEmbeddedDocuments(
        object $document,
        string $key,
        array $input,
        MapperInterface $documentMapper
    ): void {
        if (array_key_exists($key, $input)) {
            $values = $input[$key] ?? [];
            $mode = $this->getUpdateMode($input, $key);
            switch ($mode) {
                case static::MODE_PUT:
                    $this->putArrayCollectionOfEmbeddedDocuments($document, $key, $values, $documentMapper);
                    break;
                case static::MODE_PATCH:
                    $this->patchArrayCollectionOfEmbeddedDocuments($document, $key, $values, $documentMapper);
                    break;
            }
        }
    }


    /**
     * Replace the entire collection of embedded documents with the new data
     */
    private function putArrayCollectionOfEmbeddedDocuments(
        object $document,
        string $key,
        array $values,
        MapperInterface $documentMapper
    ): void {
        $embeddedDocuments =  new ArrayCollection();

        foreach ($values as $value) {
            $documentName = $documentMapper->getDocumentName();
            $embeddedDocument = new $documentName();
            $documentMapper->map($embeddedDocument, $value);
            $embeddedDocuments->add($embeddedDocument);
        }

        $setterMethodForField = MethodsBuilder::toSetMethod($key);
        $document->$setterMethodForField($embeddedDocuments);
    }

    /**
     * Merge the current embedded documents and the new data
     */
    private function patchArrayCollectionOfEmbeddedDocuments(
        object $document,
        string $key,
        array $values,
        MapperInterface $documentMapper
    ): void {
        $getterMethodForField = MethodsBuilder::toGetMethod($key);
        $embeddedDocuments = $document->$getterMethodForField();

        foreach ($values as $value) {
            $documentName = $documentMapper->getDocumentName();
            $embeddedDocument = new $documentName();
            $documentMapper->map($embeddedDocument, $value);
            $embeddedDocuments->add($embeddedDocument);
        }

        //remove duplicates if the document class implements EquatableDocumentInterface
        if ($embeddedDocuments->first() instanceof EquatableDocumentInterface) {
            $collectionManipulator = new CollectionManipulator($embeddedDocuments);
            $embeddedDocuments = $collectionManipulator
                ->removeEqualObjs()
                ->get();
        }

        $setterMethodForField = MethodsBuilder::toSetMethod($key);
        $document->$setterMethodForField($embeddedDocuments);
    }


    /**
     * This method supports only the "put" update strategy
     * @param  callable $discriminatorCallback: signature (mixed $value) : MapperInterface
     *          Returns the document model to use to perform the mapping
     */
    public function mapArrayCollectionOfMixedEmbeddedDocuments(
        object $document,
        string $key,
        array $input,
        callable $discriminatorCallback
    ): void {
        if (array_key_exists($key, $input)) {
            $values = $input[$key];

            $embeddedDocuments =  new ArrayCollection();

            try {
                foreach ($values as $value) {
                    $documentMapper = $discriminatorCallback($value);
                    $documentName = $documentMapper->getDocumentName();

                    $embeddedDocument = new $documentName();
                    $documentMapper->map($embeddedDocument, $value);
                    $embeddedDocuments->add($embeddedDocument);
                }

                $setterMethodForField = MethodsBuilder::toSetMethod($key);
                $document->$setterMethodForField($embeddedDocuments);
            } catch (Exception $exception) {
                throw new RuntimeException(
                    sprintf(
                        'Cannot map the value to the document\'s prop %s: original error message: %s',
                        $key,
                        $exception->getMessage()
                    ),
                    ExceptionCodes::RUNTIME_EXCEPTION,
                    $exception
                );
            }
        }
    }


    private function valuesToIds(array $values): array
    {
        return array_map(
            function ($value) {
                if (!isset($value['id'])) {
                    throw new InvalidArgumentException(
                        "The \$values array has not the expected structure: the key 'id' is missing",
                        ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                    );
                }
                return $value['id'];
            },
            $values
        );
    }


    private function getUpdateMode(array $input, string $key): int
    {
        $updateModeKey  = sprintf('%sUpdateMode', $key);
        $updateMode = $input[$updateModeKey] ?? static::MODE_PUT;

        if (!in_array($updateMode, [static::MODE_PUT, static::MODE_PATCH])) {
            throw new InvalidArgumentException(
                "The value of $updateModeKey must be one of 'static::MODE_PUT' and 'static::MODE_PATCH'",
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        return $updateMode;
    }
}
