<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Service\DidExternalApi;
use VC4SM\Bundle\Service\ExternalApi;

class DidExternalApiTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        //self::bootKernel();

        //$this->api = new DidExternalApi();

        DidExternalApi::$classLogger = new MockLogger();
    }

    public function testLogger()
    {
        DidExternalApi::$classLogger->info("Init logger");

        $this->assertTrue(true);
    }

    public function testCurl()
    {

        $url = "https://krakenh2020.eu";
        $res = DidExternalApi::requestInsecure($url, "GET");
        //print_r($res);
        $this->assertEquals(200, $res['status_code']);
    }

    public function testAgentConnection()
    {

        $ret1 = DidExternalApi::checkConnection("https://kraken.iaik.tugraz.at");
        $this->assertTrue($ret1);

        $ret2 = DidExternalApi::checkConnection("https://krakenh2020.eu");
        $this->assertFalse($ret2);
    }

    public function testBuildOfferRequestDiploma()
    {
        $mydid = "did:myDID";
        $theirdid = "did:theirDID";
        $api = new ExternalApi();

        $type = "diplomas";
        $cred_id = "bsc1";
        $offer = DidExternalApi::buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
        //print_r($offer);
        $cred_json = json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        DidExternalApi::$classLogger->info($cred_json);

        $this->assertEquals($mydid, $offer['my_did']);
        $this->assertEquals($theirdid, $offer['their_did']);
    }

    public function testBuildOfferRequestGrade()
    {
        $mydid = "did:myDID";
        $theirdid = "did:theirDID";
        $api = new ExternalApi();

        $type = "course-grades";
        $cred_id = "os";
        $offer = DidExternalApi::buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
        //print_r($offer);
        $cred_json = json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        DidExternalApi::$classLogger->info($cred_json);

        $this->assertEquals($mydid, $offer['my_did']);
        $this->assertEquals($theirdid, $offer['their_did']);
    }


}





class MockLogger
{

    public function warning($text)
    {
        echo "Warning: " . $text . "\n";
    }

    public function info($text)
    {
        echo "Info: " . $text . "\n";
    }
}