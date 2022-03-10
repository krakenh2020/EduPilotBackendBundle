<?php

namespace VC4SM\Bundle\Tests\Service;

use VC4SM\Bundle\Service\SimpleHttpClient;
use PHPUnit\Framework\TestCase;

class SimpleHttpClientTest extends TestCase
{
    public function testRequestGoogle()
    {
        $r = SimpleHttpClient::request("https://google.com");
        $this->assertNotEmpty($r);
    }

    public function testRequest2Google()
    {
        $c = new SimpleHttpClient();
        $r = $c->requestSymfony("https://google.com");
        $this->assertNotEmpty($r);
    }

    public function testRequestGoogleInsecure()
    {
        $r = SimpleHttpClient::requestInsecure("https://google.com");
        $this->assertNotEmpty($r);
    }

    public function testRequestKrakenIAIK()
    {
        $r = SimpleHttpClient::request("https://kraken-edu.iaik.tugraz.at");
        $this->assertNotEmpty($r);
    }

    public function testRequestKrakenWeb()
    {
        $r = SimpleHttpClient::request("https://krakenh2020.eu/");
        $this->assertNotEmpty($r);
    }


}
