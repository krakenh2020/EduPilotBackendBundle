<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Service\AriesAgentClient;

class AriesAgentClientTest extends TestCase
{
    private $agent1;

    protected function setUp(): void
    {
        $logger = new MockLogger();

        $agent1Url = 'https://kraken.iaik.tugraz.at';
        $agent1DID = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr';
        $this->agent1 = new AriesAgentClient($logger, $agent1Url, $agent1DID);
    }

    public function testConnection()
    {
        $res = $this->agent1->checkConnection();

        $this->assertTrue($res);
    }

    public function testGenInvite()
    {
        $invite = $this->agent1->createInvitation('TU Graz KRAKEN Student');

        $this->assertNotNull($invite);
        $this->assertNotEmpty($invite);

        $j = json_decode($invite);
        $this->assertEquals('TU Graz KRAKEN Student', $j->alias);
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
