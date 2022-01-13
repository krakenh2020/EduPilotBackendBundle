<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Service\DidExternalApi;
use VC4SM\Bundle\Service\ExternalApi;
use VC4SM\Bundle\Service\SimpleHttpClient;
use VC4SM\Bundle\Tests\Kernel;

class DidExternalApiTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $kernel = new Kernel([
            'aries_agent_university' => 'https://kraken.iaik.tugraz.at',
            'aries_agent_university2' => 'https://krakenh2020.eu',
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->api = $container->get('VC4SM\Bundle\Service\DidExternalApi');

        DidExternalApi::$classLogger = new MockLogger();
    }

    public function testLogger()
    {
        DidExternalApi::$classLogger->info('Init logger');

        $this->assertTrue(true);
    }

    public function testHttpClient()
    {
        $url = 'https://krakenh2020.eu';
        $res = SimpleHttpClient::requestInsecure($url, 'GET');
        //print_r($res);
        $this->assertEquals(200, $res['status_code']);
    }

    public function testAgentConnection()
    {
        // KRAKEN public (student) agent
        $ret1 = DidExternalApi::checkConnection('https://kraken.iaik.tugraz.at');
        $this->assertTrue($ret1);

        // No agent at this URL
        $ret2 = DidExternalApi::checkConnection('https://krakenh2020.eu');
        $this->assertFalse($ret2);
    }

    public function testCreateInvite()
    {
        /*
           http POST "https://kraken.iaik.tugraz.at/connections/create-invitation?alias=STEFAN"
        */

        $invite = DidExternalApi::createInvitation('https://kraken.iaik.tugraz.at'); // (public) remote agent

        //print_r($invite);

        $j = json_decode($invite);
        $this->assertEquals('TU Graz KRAKEN Demo', $j->alias);
    }

    public function testBuildOfferRequestDiploma()
    {
        $mydid = 'did:myDID';
        $theirdid = 'did:theirDID';
        $api = new ExternalApi();

        $type = 'diplomas';
        $cred_id = 'bsc1';
        $offer = DidExternalApi::buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
        //print_r($offer);
        $cred_json = json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        //DidExternalApi::$classLogger->info($cred_json);

        $this->assertEquals($mydid, $offer['my_did']);
        $this->assertEquals($theirdid, $offer['their_did']);
    }

    public function testBuildOfferRequestGrade()
    {
        $mydid = 'did:myDID';
        $theirdid = 'did:theirDID';
        $api = new ExternalApi();

        $type = 'course-grades';
        $cred_id = 'os';
        $offer = DidExternalApi::buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
        //print_r($offer);
        $cred_json = json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        //DidExternalApi::$classLogger->info($cred_json);

        $this->assertEquals($mydid, $offer['my_did']);
        $this->assertEquals($theirdid, $offer['their_did']);
    }
}

class MockLogger
{
    public function warning($text)
    {
        echo 'Warning: '.$text."\n";
    }

    public function info($text)
    {
        echo 'Info: '.$text."\n";
    }
}
