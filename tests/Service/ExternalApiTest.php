<?php

declare(strict_types=1);

namespace DBP\API\StarterBundle\Tests\Service;

use DBP\API\StarterBundle\Service\ExternalApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExternalApiTest extends WebTestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new ExternalApi();
    }

    public function test()
    {
        $this->assertTrue(true);
    }
}
