<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\Constant\ExceptionCodes;
use App\Service\GlobalId\GlobalIdProvider;
use App\Service\QueryHelper\FiltersDescriptor;
use App\Service\QueryHelper\SortingDescriptor;
use App\Service\QueryHelper\CursorSubsetDescriptor;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use App\Service\PaginationCursor\PaginationCursorProvider;
use App\Service\QueryHelper\OffsetAndLimitDescriptor;
use \DateTime as DateTime;
use \Exception as Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function count;
use function in_array;

final class QueryArgumentsProvider
{
    public const TYPE_STRING = 'STRING';
    public const TYPE_BOOL = 'BOOL';
    public const TYPE_INT = 'INT';
    public const TYPE_FLOAT = 'FLOAT';
    public const TYPE_DATETIME = 'DATETIME';
    public const TYPE_REFERENCE = 'REFERENCE';
    public const TYPE_STRING_LIST = 'STRING_LIST';
    public const TYPE_INT_LIST = 'INT_LIST';
    public const TYPE_FLOAT_LIST = 'FLOAT_LIST';
    public const TYPE_BOOL_LIST = 'BOOL_LIST';
    public const TYPE_REFERENCE_LIST = 'REFERENCE_LIST';
    public const TYPE_DATETIME_LIST = 'DATETIME_LIST_NULLABLE';
    public const TYPE_STRING_LIST_NULLABLE = 'STRING_LIST_NULLABLE';
    public const TYPE_INT_LIST_NULLABLE = 'INT_LIST_NULLABLE';
    public const TYPE_FLOAT_LIST_NULLABLE = 'FLOAT_LIST_NULLABLE';
    public const TYPE_BOOL_LIST_NULLABLE = 'BOOL_LIST_NULLABLE';
    public const TYPE_DATETIME_LIST_NULLABLE = 'DATETIME_LIST_NULLABLE';
    public const TYPE_POLYGON = 'POLYGON';
    public const TYPE_CIRCLE = 'CIRCLE';

    public function __construct(
        private PaginationCursorProvider $paginationCursorProvider,
        private GlobalIdProvider $globalIdProvider
    ) {
    }


    /**
     * Extracts relevant information from a RelayConnection ArgumentInterface
     * and builds a SortingDescriptor
     */
    public function toSortingDescriptor(ArgumentInterface $args): SortingDescriptor
    {
        $sortBy = isset($args['sort']) && isset($args['sort']['sortBy'])
            ? $args['sort']['sortBy']
            : null;
        $sortDirection = isset($args['sort']) && isset($args['sort']['sortDirection'])
            ? $args['sort']['sortDirection']
            : null;

        $sortingDescriptor = new SortingDescriptor();
        $sortingDescriptor
            ->setSortBy($sortBy)
            ->setSortDirection($sortDirection);

        return $sortingDescriptor;
    }

    /**
     * Extracts relevant information from a RelayConnection ArgumentInterface
     * and builds a FiltersDescriptor
     */
    public function toFiltersDescriptor(ArgumentInterface $args): FiltersDescriptor
    {
        if (!isset($args['filters']) || count($args['filters']) === 0) {
            return new FiltersDescriptor();
        }

        $filtersDescriptor = new FiltersDescriptor();
        foreach ($args['filters'] as $filter) {
            $operator = $filter['operator'];
            $field = $filter['field'];
            $value = $filter['value'];
            $valueType = $filter['valueType'] ?? self::TYPE_STRING;

            $value = $this->parseFilterValue($valueType, $value);

            $filtersDescriptor->$operator($field, $value);
        }
        return $filtersDescriptor;
    }

    public function parseFilterValue(?string $expectedType, string $value): mixed
    {
        try {
            return match ($expectedType) {
                self::TYPE_STRING =>  $value,
                self::TYPE_DATETIME =>  new DateTime($value),
                self::TYPE_INT =>  (int) $value,
                self::TYPE_BOOL =>  in_array($value, ['true', true, '1', 1], true) ? true : false,
                self::TYPE_FLOAT =>  (float) $value,
                self::TYPE_REFERENCE =>  $this->globalIdProvider->fromGlobalId($value)->getId(),
                self::TYPE_STRING_LIST =>  explode(',', $value),
                self::TYPE_DATETIME_LIST => $this->parseDateTimeList($value),
                self::TYPE_INT_LIST => $this->parseIntList($value),
                self::TYPE_FLOAT_LIST => $this->parseFloatList($value),
                self::TYPE_BOOL_LIST => $this->parseBoolList($value),
                self::TYPE_REFERENCE_LIST => $this->parseReferenceList($value),
                self::TYPE_STRING_LIST_NULLABLE =>  $this->parseStringListNullable($value),
                self::TYPE_DATETIME_LIST_NULLABLE => $this->parseDateTimeListNullable($value),
                self::TYPE_INT_LIST_NULLABLE => $this->parseIntListNullable($value),
                self::TYPE_FLOAT_LIST_NULLABLE => $this->parseFloatListNullable($value),
                self::TYPE_BOOL_LIST_NULLABLE => $this->parseBoolListNullable($value),
                self::TYPE_POLYGON => $this->parsePolygon($value),
                self::TYPE_CIRCLE => $this->parseCircle($value)
            };
        } catch (Exception) {
            throw new BadRequestHttpException(
                'One or more filters values are malformed and cannot be parsed.',
                null,
                ExceptionCodes::BAD_REQUEST_EXCEPTION
            );
        }
    }

    private function parseDateTimeList(string $value): array
    {
        $values = explode(',', $value);
        return array_map(fn ($value) => new DateTime($value), $values);
    }

    private function parseIntList(string $value): array
    {
        $values = explode(',', $value);
        return array_map(fn ($value) => (int) $value, $values);
    }

    private function parseFloatList(string $value): array
    {
        $values = explode(',', $value);
        return array_map(fn ($value) => (float) $value, $values);
    }

    private function parseBoolList(string $value): array
    {
        $values = explode(',', $value);
        return array_map(fn ($value) => in_array($value, ['true', true, '1', 1]) ? true : false, $values);
    }

    private function parseReferenceList(string $value): array
    {
        $values = explode(',', $value);
        return array_map(fn ($value) => $this->globalIdProvider->fromGlobalId($value)->getId(), $values);
    }

    private function parseStringListNullable(string $value): array
    {
        $values = explode(',', $value);
        $values[] = null;
        return $values;
    }

    private function parseDateTimeListNullable(string $value): array
    {
        $values = explode(',', $value);
        $values = array_map(fn ($value) => new DateTime($value), $values);
        $values[] = null;
        return $values;
    }

    private function parseIntListNullable(string $value): array
    {
        $values = explode(',', $value);
        $values = array_map(fn ($value) => (int) $value, $values);
        $values[] = null;
        return $values;
    }

    private function parseFloatListNullable(string $value): array
    {
        $values = explode(',', $value);
        $values = array_map(fn ($value) => (float) $value, $values);
        $values[] = null;
        return $values;
    }

    private function parseBoolListNullable(string $value): array
    {
        $values = explode(',', $value);
        $values = array_map(fn ($value) => in_array($value, ['true', true, '1', 1]) ? true : false, $values);
        $values[] = null;
        return $values;
    }

    private function parsePolygon(string $value): array
    {
        return array_map(
            fn (string $longitudeAndLatitude) => array_map(fn (string $coordinate) => (float)$coordinate, explode(',', $longitudeAndLatitude)),
            explode('|', $value)
        );
    }

    private function parseCircle(string $value): array
    {
        $coordinatesAndRadius = explode('|', $value);
        $longitudeAndLatitude = explode(',', $coordinatesAndRadius[0]);
        $longitude = (float)$longitudeAndLatitude[0];
        $latitude = (float)$longitudeAndLatitude[1];
        $radius = (int)$coordinatesAndRadius[1];

        return [$longitude, $latitude, $radius];
    }

    /**
     * Extracts relevant information from a RelayConnection ArgumentInterface
     * and builds a CursorSubsetDescriptor
     */
    public function toCursorSubsetDescriptor(ArgumentInterface $args): CursorSubsetDescriptor
    {
        if ($args['first'] === null && $args['last'] === null) {
            throw new BadRequestHttpException("Neither 'first' nor 'last' were declared.", null, ExceptionCodes::INVALID_ARGUMENT_EXCEPTION);
        }

        $cursorSubsetDescriptor = new CursorSubsetDescriptor();
        $cursorSubsetDescriptor
            ->setFirst($args['first'])
            ->setAfter($this->paginationCursorProvider->fromPaginationCursor($args['after']))
            ->setLast($args['first'] !== null ? null : $args['last'])
            ->setBefore($this->paginationCursorProvider->fromPaginationCursor($args['first'] !== null ?  null : $args['before']));

        return $cursorSubsetDescriptor;
    }


    /**
     * Extract the limit and offset values from a RelayConnection ArgumentInterface
     * and builds a OffsetAndLimitDescriptor
     */
    public function toOffsetAndLimitDescriptor(ArgumentInterface $args): OffsetAndLimitDescriptor
    {
        $offsetAndLimitDescriptor = new OffsetAndLimitDescriptor();
        if (isset($args['offset'])) {
            $offsetAndLimitDescriptor->setOffset($args['offset']);
        }
        if (isset($args['limit'])) {
            $offsetAndLimitDescriptor->setLimit($args['limit']);
        }
        return $offsetAndLimitDescriptor;
    }
}
