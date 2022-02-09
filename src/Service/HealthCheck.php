<?php

declare(strict_types=1);

namespace Dbp\Relay\AuthBundle\Service;

use Dbp\Relay\AuthBundle\OIDC\OIDProvider;
use Dbp\Relay\CoreBundle\HealthCheck\CheckInterface;
use Dbp\Relay\CoreBundle\HealthCheck\CheckOptions;
use Dbp\Relay\CoreBundle\HealthCheck\CheckResult;

class HealthCheck implements CheckInterface
{
    private $provider;

    public function __construct(OIDProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getName(): string
    {
        return 'auth';
    }

    private function checkMethod(string $description, callable $func): CheckResult
    {
        $result = new CheckResult($description);
        try {
            $func();
        } catch (\Throwable $e) {
            $result->set(CheckResult::STATUS_FAILURE, $e->getMessage(), ['exception' => $e]);

            return $result;
        }
        $result->set(CheckResult::STATUS_SUCCESS);

        return $result;
    }

    public function checkConfig()
    {
        $this->provider->getProviderConfig();
    }

    public function checkPublicKey()
    {
        $this->provider->getJWKs();
    }

    public function check(CheckOptions $options): array
    {
        $results = [];
        $results[] = $this->checkMethod('Check if the OIDC config can be fetched', [$this, 'checkConfig']);
        $results[] = $this->checkMethod('Check if the OIDC public key can be fetched', [$this, 'checkPublicKey']);

        return $results;
    }
}
