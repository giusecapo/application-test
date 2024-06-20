<?php

declare(strict_types=1);

namespace App\Service\QueryHelper;

use \InvalidArgumentException as InvalidArgumentException;
use App\Utility\ArrayValidator;
use App\Contract\Document\DocumentInterface;
use App\Service\Constant\ExceptionCodes;
use App\Utility\ArrayManipulator;
use DateTimeInterface;
use MongoDB\BSON\Regex as MongoRegex;
use function count;
use function is_array;

final class FiltersDescriptor
{

	/**
	 * This array will collect all the applied filters.
	 * The associative array is formatted as follows:
	 * [ 
	 *  'field' => 'fieldName' //the name of the object's field. e.g. username
	 *  'operator' => 'operatorName', // e.g greater than, lower than, equal
	 *  'value' => 'value' //mixed (e.g. an array, a string, an int, float ...)
	 * ]
	 */
	private array $filters;


	public function __construct()
	{
		$this->filters = array();
	}

	/**
	 * @param  (string|int|float)[] $values
	 * @throws InvalidArgumentException when the $values array has invalid entries, or is empty
	 */
	public function in(string $field, array $values, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!ArrayValidator::deepHasOnlyScalarsAndNullAsChildren($values)) {
				throw new InvalidArgumentException(
					'The $values array must contain only scalars.',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'in',
				'value' => $values
			];
		}
		return $this;
	}

	/**
	 * @param  (string|int|float)[] $values
	 * @throws InvalidArgumentException when the $values array has invalid entries, or is empty
	 */
	public function notIn(string $field, array $values, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!ArrayValidator::deepHasOnlyScalarsAndNullAsChildren($values)) {
				throw new InvalidArgumentException(
					'The $values array must contain only scalars.',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'notIn',
				'value' => $values
			];
		}
		return $this;
	}


	/**
	 * IMPORTANT: Equality matches on the whole embedded/nested document require 
	 * an exact match of the specified document, including the field order.
	 */
	public function equals(string $field, $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => $value
			];
		}
		return $this;
	}

	public function equalsCaseInsensitive(string $field, string $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => new MongoRegex("^$value$", 'i')
			];
		}
		return $this;
	}

	/**
	 * @param  string $value: the regex pattern
	 */
	public function equalsRegex(string $field, string $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => new MongoRegex($value, 'i')
			];
		}
		return $this;
	}

	public function contains(string $field, string $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => new MongoRegex($value, 'i')
			];
		}
		return $this;
	}

	public function startsWith(string $field, string $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => new MongoRegex("^$value", 'i')
			];
		}
		return $this;
	}


	/**
	 * IMPORTANT: Equality matches on the whole embedded/nested document require
	 * an exact match of the specified document, including the field order.
	 */
	public function notEqual(string $field, int|float|string|bool|null $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'notEqual',
				'value' => $value
			];
		}
		return $this;
	}


	/**
	 * Performs a text search on the content of the field 
	 */
	public function text(string $field, string $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'text',
				'value' => $value
			];
		}
		return $this;
	}

	public function gt(string $field, int|float|string|DateTimeInterface|null $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'gt',
				'value' => $value
			];
		}
		return $this;
	}

	public function gte(string $field, int|float|string|DateTimeInterface|null $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'gte',
				'value' => $value
			];
		}
		return $this;
	}

	public function lt(string $field, int|float|string|DateTimeInterface|null $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'lt',
				'value' => $value
			];
		}
		return $this;
	}

	public function lte(string $field, int|float|string|DateTimeInterface|null $value, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'lte',
				'value' => $value
			];
		}
		return $this;
	}

	public function size(string $field, int $size, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if ($size < 0) {
				throw new InvalidArgumentException(
					'The argument size must be an integer greater than or equal to 0',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'size',
				'value' => $size
			];
		}
		return $this;
	}

	public function exists(string $field, bool $bool, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'exists',
				'value' => $bool
			];
		}
		return $this;
	}

	/**
	 * @param  (int|float|string|bool)[] $values
	 */
	public function all(string $field, array $values, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!ArrayValidator::hasOnlyScalarsAsChildren($values)) {
				throw new InvalidArgumentException(
					'The $values array must contain only scalars',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'all',
				'value' => $values
			];
		}
		return $this;
	}

	public function references(string $field, DocumentInterface $document, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => $document->getId()
			];
		}
		return $this;
	}

	public function includesReferenceTo(string $field, DocumentInterface $document, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			$this->filters[] = [
				'field' => $field,
				'operator' => 'equals',
				'value' => $document->getId()
			];
		}
		return $this;
	}

	/**
	 * @param float[][] $polygon An array of arrays, each containing exactly two floats (longitude and latitude)
	 */
	public function geoWithinPolygon(string $field, array $polygon, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!self::isValidPolygon($polygon)) {
				throw new InvalidArgumentException(
					'The $polygon is not valid',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'geoWithinPolygon',
				//We add the first point of the polygon also as last point to close the polygon
				'value' => [...$polygon, $polygon[0]]
			];
		}
		return $this;
	}

	private static function isValidPolygon(array $polygon): bool
	{
		foreach ($polygon as $point) {
			if (
				!is_array($point)
				|| count($point) !== 2
				|| !ArrayValidator::hasOnlyNumbers($point)
				|| $point[0] < -180
				|| $point[0] > 180
				|| $point[1] < -90
				|| $point[1] > 90
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param float[] $circle An array containing exactly three floats (longitude, latitude and radius)
	 */
	public function geoWithinCircle(string $field, array $circle, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!self::isValidCircle($circle)) {
				throw new InvalidArgumentException(
					'The $circle is not valid',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}

			$this->filters[] = [
				'field' => $field,
				'operator' => 'geoWithinCenterSphere',
				//The radius must be expressed in radians.
				//To convert the radius to radian, the radius of the circle is divided by the radius of the earth
				'value' => [$circle[0], $circle[1], $circle[2] / 6378100]
			];
		}
		return $this;
	}

	private static function isValidCircle(array $circle): bool
	{
		return $circle[0] >= -180 && $circle[0] <= 180
			&& $circle[1] >= -90 && $circle[1] <= 90
			&& $circle[2] > 0 && $circle[2] <= 10000000;
	}

	public function and(FiltersDescriptor $filter, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {

			$this->filters[] = [
				'operator' => 'and',
				'field' => null,
				'value' => $filter
			];
		}
		return $this;
	}

	public function or(array $filters, bool $condition = true): FiltersDescriptor
	{
		if ($condition) {
			if (!ArrayValidator::hasOnlyInstancesOfClass($filters, FiltersDescriptor::class)) {
				throw new InvalidArgumentException(
					'The $filters array must contain only instances of FiltersDescriptor',
					ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
				);
			}
			$this->filters[] = [
				'operator' => 'or',
				'field' => null,
				'value' => $filters
			];
		}
		return $this;
	}

	/**
	 * Returns the filters as an array
	 * Each entry of the array is an associative array formatted as follows:
	 * [ 
	 *  'field' => 'fieldName' //the name of the object's field. e.g. username
	 *  'operator' => 'operatorName', // e.g greater than, lower than, equal
	 *  'value' => 'value' //mixed (e.g. an array, a string, an int, float ...)
	 * ]
	 */
	public function getFiltersAsArray(): array
	{
		return $this->filters;
	}

	/**
	 * Returns the filters applied to the given field as an array.
	 * Each entry of the array is an associative array formatted as follows:
	 * [ 
	 *  'field' => 'fieldName' //the name of the object's field. e.g. username
	 *  'operator' => 'operatorName', // e.g greater than, lower than, equal
	 *  'value' => 'value' //mixed (e.g. an array, a string, an int, float ...)
	 * ]
	 */
	public function getFiltersByFieldAsArray(string $field): array
	{
		$arrayManipulator = new ArrayManipulator($this->filters);
		return $arrayManipulator
			->filter(fn (array $filter): bool => $filter['field'] === $field)
			->get();
	}
}
