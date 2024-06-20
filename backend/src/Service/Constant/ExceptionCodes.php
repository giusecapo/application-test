<?php

declare(strict_types=1);

namespace App\Service\Constant;

final class ExceptionCodes
{
    public const DOMAIN_EXCEPTION = 1010;
    public const INVALID_ARGUMENT_EXCEPTION = 1020;
    public const INVALID_OUTPUT_DOCUMENT_EXCEPTION = 1030;
    public const METHOD_NOT_IMPLEMENTED_EXCEPTION = 1040;
    public const RUNTIME_EXCEPTION = 1050;

    public const LOCK_EXCEPTION = 2010;
    public const DUPLICATE_EXCEPTION = 2020;
    public const BAD_REQUEST_EXCEPTION = 2110;
    public const UNPROCESSABLE_ENTITY_EXCEPTION = 2210;
    public const INVALID_INPUT_DOCUMENT_EXCEPTION = 2310;
    public const INVALID_INPUT_ARGUMENTS_EXCEPTION = 2311;
    public const NOT_ALLOWED_MIME_TYPE_EXCEPTION = 2320;
    public const REFERENTIAL_INTEGRITY_EXCEPTION = 2410;

    public const UNAUTHORIZED_EXCEPTION = 5110;
    public const ACCESS_DENIED_EXCEPTION = 5120;
    public const SPAM_EXCEPTION = 5210;
    public const TAMPERING_EXCEPTION = 5220;
}
