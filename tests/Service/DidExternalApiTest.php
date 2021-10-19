<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Service\DidExternalApi;

class DidExternalApiTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        //self::bootKernel();

        //$this->api = new DidExternalApi();
    }

    public function testEcho()
    {
        echo "test echo!";
        $this->assertTrue(true);
    }

    public function testCurl()
    {

        $url = "https://krakenh2020.eu";
        $res = DidExternalApi::requestInsecure($url, "GET");
        //print_r($res);
        $this->assertEquals(200, $res['status_code']);
    }
}
