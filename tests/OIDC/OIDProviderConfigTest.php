<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Tests\OIDC;

use Dbp\Relay\AuthBundle\OIDC\OIDProviderConfig;
use PHPUnit\Framework\TestCase;

class OIDProviderConfigTest extends TestCase
{
    public function testConfig()
    {
        $data = [
            'issuer' => 'issuer',
            'jwks_uri' => 'jwks_uri',
            'introspection_endpoint_auth_signing_alg_values_supported' => ['RS256'],
            'introspection_endpoint' => 'introspection_endpoint',
        ];
        $oid = OIDProviderConfig::fromString(json_encode($data));
        $oid->getIntrospectionEndpointSigningAlgorithms();
        $oid->getIntrospectionEndpoint();
        $oid->getJwksUri();
        $this->assertSame('issuer', $oid->getIssuer());
        $this->assertSame(['RS256'], $oid->getIntrospectionEndpointSigningAlgorithms());
        $this->assertSame('introspection_endpoint', $oid->getIntrospectionEndpoint());
        $this->assertSame('jwks_uri', $oid->getJwksUri());
    }
}
