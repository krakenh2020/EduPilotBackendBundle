<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use VC4SM\Bundle\Service\ExternalApi;
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
