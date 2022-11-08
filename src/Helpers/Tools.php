<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Helpers;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;

class Tools
{
    public static function createLoggerMiddleware(LoggerInterface $logger): callable
    {
        return Middleware::log(
            $logger,
            new MessageFormatter('[{method}] {uri}: CODE={code}, ERROR={error}, CACHE={res_header_X-Kevinrob-Cache}')
        );
    }

    /**
     * Extract scopes from a decoded JWT.
     */
    public static function extractScopes(array $jwt): array
    {
        return preg_split('/\s+/', $jwt['scope'] ?? '', -1, PREG_SPLIT_NO_EMPTY);
    }
}
