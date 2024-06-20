<?php

declare(strict_types=1);

namespace App\Service\Http;

use Symfony\Component\HttpFoundation\Request;
use function is_array;


/**
 * QueryParameterParser provides methods
 * to parse  http query parameters
 */
final class QueryParameterParser
{

    public function parseAsBool(Request $request, string $parameterName, ?bool $default = false): ?bool
    {
        $value = $request->query->get($parameterName) ?? $default;
        if (!isset($value)) {
            return null;
        }
        return (bool) $value;
    }

    /**
     * @return bool[]
     */
    public function parseAsArrayOfBools(
        Request $request,
        string $parameterName,
        array $default = array()
    ): array {
        $values = $request->query->get($parameterName);
        $values = isset($values) && is_array($values) ? $values : $default;

        $values = array_map(fn ($value) => (bool) $value, $values);

        return $values;
    }

    /**
     * @param  float $min: if the query parameter value is lower than min, min is returned
     * @param  float $max: if the query parameter value is greater than max, max is returned
     */
    public function parseAsFloat(
        Request $request,
        string $parameterName,
        ?float $default = 0.00,
        ?float $min = null,
        ?float $max = null
    ): ?float {
        $value = $request->query->get($parameterName) ?? $default;
        if (!isset($value)) {
            return null;
        }
        $value = (float) $value;
        if (isset($min) && $value < $min) {
            return $min;
        }
        if (isset($max) && $value > $max) {
            return $max;
        }
        return $value;
    }


    /**
     * @param  float $min: if the query parameter value is lower than min, min is returned
     * @param  float $max: if the query parameter value is greater than max, max is returned
     * @return float[]
     */
    public function parseAsArrayOfFloats(
        Request $request,
        string $parameterName,
        array $default = array(),
        ?float $min = null,
        ?float $max = null
    ): array {
        $values = $request->query->get($parameterName);
        $values = isset($values) && is_array($values) ? $values : $default;

        $values = array_map(fn ($value) => (float) $value, $values);

        if (isset($min)) {
            $values = array_map(fn ($value) => $value >= $min ? $value : $min, $values);
        }

        if (isset($max)) {
            $values = array_map(fn ($value) => $value <= $max ? $value : $max, $values);
        }

        return $values;
    }

    /**
     * @param  int $min: if the query parameter value is lower than min, min is returned
     * @param  int $max: if the query parameter value is greater than max, max is returned
     */
    public function parseAsInt(
        Request $request,
        string $parameterName,
        ?int $default = 0,
        ?int $min = null,
        ?int $max = null
    ): ?int {
        $value = $request->query->get($parameterName) ?? $default;
        if (!isset($value)) {
            return null;
        }
        $value = (int) $value;
        if (isset($min) && $value < $min) {
            return $min;
        }
        if (isset($max) && $value > $max) {
            return $max;
        }
        return $value;
    }

    /**
     * @param  int $min: if the query parameter value is lower than min, min is returned
     * @param  int $max: if the query parameter value is greater than max, max is returned
     * @return int[]
     */
    public function parseAsArrayOfIntegers(
        Request $request,
        string $parameterName,
        array $default = array(),
        ?int $min = null,
        ?int $max = null
    ): array {
        $values = $request->query->get($parameterName);
        $values = isset($values) && is_array($values) ? $values : $default;

        $values = array_map(fn ($value) => (int) $value, $values);

        if (isset($min)) {
            $values = array_map(fn ($value) => $value >= $min ? $value : $min, $values);
        }

        if (isset($max)) {
            $values = array_map(fn ($value) => $value <= $max ? $value : $max, $values);
        }
        return $values;
    }

    public function parseAsString(Request $request, string $parameterName, ?string $default = ''): ?string
    {
        return $request->query->get($parameterName) ?? $default;
    }

    /**
     * @return string[]
     */
    public function parseAsArrayOfStrings(Request $request, string $parameterName, array $default = array()): array
    {
        $values = $request->query->get($parameterName);
        $values = isset($values) && is_array($values) ? $values : $default;
        $values = array_map(fn ($value) => (string) $value, $values);

        return $values;
    }
}
