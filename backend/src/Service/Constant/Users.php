<?php

declare(strict_types=1);

namespace App\Service\Constant;


final class Users
{
    public const USER_ANONYMOUS = 'anonymous';
    public const USER_SYSTEM = 'system';
    public const USER_FIXTURE = 'fixture';

    public const ALL_SYSTEM_USERS = [
        self::USER_ANONYMOUS,
        self::USER_SYSTEM,
        self::USER_FIXTURE
    ];
}
