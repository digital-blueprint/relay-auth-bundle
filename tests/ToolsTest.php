<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests;

use Dbp\Relay\AuthBundle\Helpers\Tools;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;

class ToolsTest extends TestCase
{
    public function testEscapeCacheKey()
    {
        $this->assertSame('.', CacheItem::validateKey(Tools::escapeCacheKey('')));
        $this->assertSame('%7B%7D%28%29%2F%5C%40%3A.', CacheItem::validateKey(Tools::escapeCacheKey(ItemInterface::RESERVED_CHARACTERS)));
        $this->assertSame('%2Ffoo%2Fbar.', CacheItem::validateKey(Tools::escapeCacheKey('/foo/bar')));
    }
}
