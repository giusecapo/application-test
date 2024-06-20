<?php

declare(strict_types=1);

namespace App\Utility;

final class MethodsBuilder
{

    /**
     * username -> getUsername
     */
    public static function toGetMethod(string $propName): string
    {
        return sprintf('get%s', $propName);
    }

    /**
     * username -> setUsername
     */
    public static function toSetMethod(string $propName): string
    {
        return sprintf('set%s', $propName);
    }


    /**
     * username -> setUsername
     */
    public static function toResolveMethod(string $propName): string
    {
        return sprintf('resolve%s', $propName);
    }


    /**
     * user -> getUserId
     */
    public static function toGetIdMethod(string $propName): string
    {
        return sprintf('get%sId', $propName);
    }

    /**
     * users -> getUsersIds
     */
    public static function toGetIdsMethod(string $propName): string
    {
        return sprintf('get%sIds', $propName);
    }
}
