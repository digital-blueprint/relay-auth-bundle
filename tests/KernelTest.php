<?php

declare(strict_types=1);

namespace DBP\API\KeycloakBundle\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class KernelTest extends ApiTestCase
{
    public function testBasics()
    {
        $client = static::createClient();
        $this->assertNotNull($client);
    }
}
