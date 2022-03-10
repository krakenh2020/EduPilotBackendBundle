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
        $agent1logger = new AgentMockLogger('Agent 1');

        $agent1Url = 'https://kraken.iaik.tugraz.at';
        $agent1DID = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr'; // needs to match keymaterial imported by `aries_kms_init.py`
        $this->agent1 = new AriesAgentClient($agent1logger, $agent1Url, $agent1DID);
    }

    public function testConnection()
    {
        $agentReachable = $this->agent1->checkConnection();

        $this->assertTrue($agentReachable);
    }

    public function testCreateInvite()
    {
        $invite = $this->agent1->createInvitation('TU Graz KRAKEN Student');

        $this->assertNotNull($invite);
        $this->assertNotEmpty($invite);

        $j = json_decode($invite);
        $this->assertEquals('TU Graz KRAKEN Student', $j->alias);
    }

    // TODO create test for all other aries actions (at least for those needed by uni agent)
    

}

class AgentMockLogger
{
    private $agentName;

    public function __construct(string $agentName)
    {
        $this->agentName = $agentName;
    }

    public function warning($text)
    {
        echo "[$this->agentName] Warning: " . $text . "\n";
    }

    public function info($text)
    {
        echo "[$this->agentName] Info: " . $text . "\n";
    }
}
