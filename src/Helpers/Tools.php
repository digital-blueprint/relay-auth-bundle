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

    /**
     * Escape a cache key string to be valid as a Symfony cache key.
     */
    public static function escapeCacheKey(string $input): string
    {
        // Always append a '.' since empty strings are also not allowed.
        // For what isn't allowed see ItemInterface::RESERVED_CHARACTERS
        return rawurlencode($input.'.');
    }
}
