<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Service\AriesAgentClient;
use VC4SM\Bundle\Service\ExternalApi;
use VC4SM\Bundle\Service\SimpleHttpClient;
use VC4SM\Bundle\Tests\Kernel;

class DidExternalApiTest extends TestCase
{
    // https://github.com/krakenh2020/EduPilotDeploymentDocker#exposed-services
    private const localhost_uni_agent = 'http://localhost:8082';
    private const localhost_student_agent = 'http://localhost:8092';
    public const remote_student_agent = 'https://kraken.iaik.tugraz.at';

    public const testOffline = true; // only use localhost agents
    private $disableCItests = true;  // disable tests that won't work in CI at the moment

    private $api;

    protected function setUp(): void
    {
        $kernel = new Kernel([
            'aries_agent_university' => 'https://krakenh2020.eu/fail', // this is not a valid agent on purpose
            'aries_agent_university2' => self::getUniAgentUrl(),
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();

        $this->api = $container->get('VC4SM\Bundle\Service\DidExternalApi');
    }

    public static function getStudentAgentUrl()
    {
        return self::testOffline ? self::localhost_student_agent : self::remote_student_agent;
    }

    public static function getUniAgentUrl()
    {
        // for some testing uni and student can both use same agent (as remote uni agent is not exposed)
        return self::testOffline ? self::localhost_uni_agent : self::remote_student_agent;
    }

    public static function log(string $text)
    {
        fwrite(STDERR, '[Test] ' . $text . "\n");
    }

    public function testLogger()
    {
        $this->api->getLogger()->info('Init logger');

        $this->assertTrue(true);
    }

    public function testStudentAgentReachable()
    {
        $url = self::getStudentAgentUrl() . '/connections';
        $res = SimpleHttpClient::request($url);
        $this->assertEquals(200, $res['status_code'], "Cannot reach $url: " . $res['status_code']);
    }

    public function testUniAgentReachable()
    {
        $url = self::getUniAgentUrl() . '/connections';
        $res = SimpleHttpClient::request($url);
        $this->assertEquals(200, $res['status_code'], "Cannot reach $url: " . $res['status_code']);
    }

    /*public function testHttpClientInsecure()
    {
        $url = 'https://krakenh2020.eu';
        $res = SimpleHttpClient::requestInsecure($url, 'GET');
        //print_r($res);
        $this->assertEquals(200, $res['status_code']);
    }*/

    public function testRemoteAgentConnection()
    {
        // KRAKEN public (student) agent
        $ret1 = $this->api->checkConnection('https://kraken.iaik.tugraz.at', 'could not reach remote agent');
        $this->assertTrue($ret1);

        // No agent at this URL
        $ret2 = $this->api->checkConnection('https://krakenh2020.eu', 'remote agent reached at URL where no remote agent is hosted');
        $this->assertFalse($ret2);
    }

    public function testBuildOfferRequestDiploma()
    {
        $mydid = 'did:myDID';
        $theirdid = 'did:theirDID';
        $api = new ExternalApi();

        $type = 'diplomas';
        $cred_id = 'bsc1';
        $offer = $this->api->buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
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
        $offer = $this->api->buildOfferRequest($mydid, $theirdid, $api, $type, $cred_id);
        //print_r($offer);
        $cred_json = json_encode($offer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        //DidExternalApi::$classLogger->info($cred_json);

        $this->assertEquals($mydid, $offer['my_did']);
        $this->assertEquals($theirdid, $offer['their_did']);
    }

    public function testInviteFlow()
    {
        $studentAgentUrl = self::getStudentAgentUrl();

        $studentAgent = new AriesAgentClient(new AgentMockLogger2('StudentAgent'), $studentAgentUrl, 'did:student');
        $this->assertTrue($studentAgent->checkConnection(), 'Could not connect to student agent ...');

        // University: Create Invite

        $connections = $this->api->getDidConnections();
        $this->assertEquals(1, count($connections));

        $connection = $connections[0];
        $this->assertInstanceOf(DidConnection::class, $connection);

        $invite = $connection->getInvitation();
        $this->assertNotNull($invite);
        $this->assertNotEmpty($invite);

        print_r($invite);
        
        $invite = json_decode($invite);

        // University: Frontend polls if invite accepted (not yet)

        $this->assertTrue(isset($invite->invitation));
        $this->assertTrue(isset($invite->invitation->{'@id'}));

        $connection_id = $invite->invitation->{'@id'};

        //$connection = $this->api->getDidConnectionById($connection_id);
        //$this->assertNull($connection, "Found accepted invite, but student did not accept yet.");

        // Student: Receive Invite
        //   POST /connections/receive-invitation → connection with $invite->invitation
        //   connectionId = connection['connection_id']

        // via https://stackoverflow.com/a/18576902/1518225
        $inviteAsArray = json_decode(json_encode($invite->invitation), true);
        $studentConnection = $studentAgent->receiveConnectionInvite($inviteAsArray);

        $this->assertNotEmpty($studentConnection);
        $studentConnection = json_decode($studentConnection);

        $this->assertTrue(isset($studentConnection->connection_id));
        $studentConnectionId = $studentConnection->connection_id;

        // Student: Accept invite
        // POST '/connections/' + connectionId + '/accept-invitation'

        $inviteAcceptDetails = $studentAgent->acceptConnectionInvite($studentConnectionId);
        $this->assertNotEmpty($inviteAcceptDetails);

        // University: Poll if invite accepted (yes)

        //  not sure if the problem is that the test is faster than the agent:
        for ($i = 0; $i < 10; ++$i) {
            $uniConnection = $this->api->getDidConnectionById($connection_id);
            if ($uniConnection !== null) {
                break;
            }
            sleep(1);
        }

        $this->assertNotNull($uniConnection, 'Could not find accepted invite.');
        $this->assertNotEmpty($uniConnection);
        //print_r($uniConnection->getInvitation());

        $uniAcceptedInvite = json_decode($uniConnection->getInvitation());
        //echo "MyDID= " . $uniAcceptedInvite->MyDID;
        //echo "TheirDID= " . $uniAcceptedInvite->TheirDID;

        ///////////////////////////////////////////////////////////////////////
        // done, connection established!
        $this->assertTrue(true);
    }

    public function testExportCredential()
    {
        $signedCred = $this->api->buildCred('course-grades', 'os');
        self::assertNotNull($signedCred);

        //print_r($signedCred);

        $c = json_encode($signedCred, JSON_UNESCAPED_SLASHES);
        print($c);

        return $c;
    }

    public function exportSignedCredential($uri)
    {
        $type = explode('/', $uri)[1];
        $id = explode('/', $uri)[2];

        $signedCred = $this->api->buildAndSignCred($type, $id);
        self::assertNotNull($signedCred);

        //print_r($signedCred);

        $c = json_encode($signedCred, JSON_UNESCAPED_SLASHES);
        print($c);

        return $c;
    }

    public function testExportSignedDiplomaCredential()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $this->exportSignedCredential('/diplomas/bsc1');
    }

    public function testExportSignedGradeCredential()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $this->exportSignedCredential('/course-grades/os');
    }

    public function testExportGradeToBatchexporter()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $cred = new Credential("", "", "/course-grades/os");
        $res = $this->api->provideCredenitalToBatchExporter($cred);

        self::assertNotNull($res);
        self::assertEquals("OK", $res->getMyDid());
    }

    public function testExportDiplomaToBatchexporter()
    {
        if ($this->disableCItests) {
            $this->markTestSkipped("currently not supported in CI");
        }

        $cred = new Credential("", "", "/diplomas/bsc1");
        $res = $this->api->provideCredenitalToBatchExporter($cred);

        self::assertEquals("OK", $res->getMyDid());
    }

    public function testFullFlowDiploma()
    {
        //$this->markTestSkipped('something not working with DID connection on github actions ...');

        $this->credFlow('/diplomas/bsc1');
    }

    public function testFullFlowGrade()
    {
        //$this->markTestSkipped('something not working with DID connection on github actions ...');

        $this->credFlow('/course-grades/os');
    }

    public function credFlow($credId)
    {
        $studentAgentUrl = self::getStudentAgentUrl();

        $studentAgent = new AriesAgentClient(new AgentMockLogger2('StudentAgent'), $studentAgentUrl, 'did:student');
        $this->assertTrue($studentAgent->checkConnection(), 'Could not connect to student agent ...');

        // University: Create Invite

        $connections = $this->api->getDidConnections();
        $this->assertEquals(1, count($connections));

        $connection = $connections[0];
        $this->assertInstanceOf(DidConnection::class, $connection);

        $invite = $connection->getInvitation();
        $this->assertNotNull($invite);
        $this->assertNotEmpty($invite);

        $invite = json_decode($invite);

        // University: Frontend polls if invite accepted (not yet)

        $this->assertTrue(isset($invite->invitation));
        $this->assertTrue(isset($invite->invitation->{'@id'}));

        $connection_id = $invite->invitation->{'@id'};

        //$connection = $this->api->getDidConnectionById($connection_id);
        //$this->assertNull($connection, "Found accepted invite, but student did not accept yet.");

        // Student: Receive Invite
        //   POST /connections/receive-invitation → connection with $invite->invitation
        //   connectionId = connection['connection_id']

        // via https://stackoverflow.com/a/18576902/1518225
        $inviteAsArray = json_decode(json_encode($invite->invitation), true);
        $studentConnection = $studentAgent->receiveConnectionInvite($inviteAsArray);

        $this->assertNotEmpty($studentConnection);
        $studentConnection = json_decode($studentConnection);

        $this->assertTrue(isset($studentConnection->connection_id));
        $studentConnectionId = $studentConnection->connection_id;

        // Student: Accept invite
        // POST '/connections/' + connectionId + '/accept-invitation'
        for ($i = 0; $i < 10; ++$i) {
            $inviteAcceptDetails = $studentAgent->acceptConnectionInvite($studentConnectionId);
            if ($inviteAcceptDetails !== null) {
                break;
            }
            sleep(1);
        }

        $this->assertNotNull($inviteAcceptDetails);
        $this->assertNotEmpty($inviteAcceptDetails);

        // University: Poll if invite accepted (yes)
        $uniConnection = null;
        for ($i = 0; $i < 20; ++$i) {
            $uniConnectionTmp = $this->api->getDidConnectionById($connection_id);
            if ($uniConnectionTmp !== null && json_decode($uniConnectionTmp->getInvitation())->State === 'completed') {
                $uniConnection = $uniConnectionTmp;
                //$s = json_decode($uniConnection->getInvitation())->State;
                //self::log($s);
                break;
            }
            sleep(1);
        }

        $this->assertNotNull($uniConnection, 'Could not find accepted invite.');
        $this->assertNotEmpty($uniConnection);
        //print_r($uniConnection->getInvitation());

        $uniAcceptedInvite = json_decode($uniConnection->getInvitation());
        //echo "MyDID= " . $uniAcceptedInvite->MyDID;
        //echo "TheirDID= " . $uniAcceptedInvite->TheirDID;

        /*
         * sleep(5); // wait for uni agent to receive info that student accepted the invite
        $uniConnection = $this->api->getDidConnectionById($connection_id);
        $uniAcceptedInvite = json_decode($uniConnection->getInvitation());
        echo "uniAcceptedInvite: $uniAcceptedInvite->State\n";  // responded ...
        print_r($uniAcceptedInvite);
        */

        ///////////////////////////////////////////////////////////////////////
        // done, connection established!
        $this->assertNotNull($uniAcceptedInvite, 'Uni did not yet accept DID invite.');

        // University: Send credential offer

        //$credId = "/diplomas/bsc1";

        $cred = new Credential($uniAcceptedInvite->MyDID, $uniAcceptedInvite->TheirDID, $credId);
        $credofferResp = $this->api->sendOffer($cred);

        $this->assertNotNull($credofferResp, 'Failed to send credential offer.');
        //print_r($credofferResp); // contains PIID in myDID field → send back via myDID field to acceptRequest

        // University: Frontend polls if credential accepted (not yet)

        $credoffer_piid = json_decode($credofferResp->getMyDid())->piid;
        $cred2 = new Credential($credoffer_piid, '', $credId);
        $credAcceptResp = $this->api->acceptRequest($cred2);

        $this->assertNull($credAcceptResp);

        // Student: Accept cred offer

        $studentActions = $studentAgent->getIssuercredentialActions();
        self::log('Handling ' . count($studentActions) . ' actions ...');
        foreach ($studentActions as $action) {
            self::log('offer-credential? ' . $action->Msg->{'@type'} . "\n");
            if (str_contains($action->Msg->{'@type'}, 'offer-credential')) {
                $action_piid = $action->PIID;
                self::log("student: accepting offer $action_piid ... \n");
                $acceptCredOffer = $studentAgent->acceptCredentialOffer($action_piid);
                $this->assertNotNull($acceptCredOffer, 'student: accepting credential offer failed.');
                $this->assertEquals([], json_decode($acceptCredOffer, true));
            }
        }

        // University: Frontend polls if credential accepted (yes!)
        // → issue credential
        sleep(5);

        $cred3 = new Credential($credoffer_piid, '', $credId);
        $credAcceptResp2 = $this->api->acceptRequest($cred3);

        $this->assertNotNull($credAcceptResp2, 'Tried to issue credential, but offer not yet accepted by student.');
        $this->assertEquals([], json_decode($credAcceptResp2->getMyDid(), true));

        // Student: Accept credential
        // (not sure why some only show up later, so do this twice)

        for ($i = 0; $i < 2; ++$i) {
            $studentActions2 = $studentAgent->getIssuercredentialActions();
            foreach ($studentActions2 as $action) {
                self::log('issue-credential? ' . $action->Msg->{'@type'} . "\n");
                if (!str_contains($action->Msg->{'@type'}, 'issue-credential')) {
                    continue;
                }
                $action_piid = $action->PIID;
                self::log("student: accepting credential $action_piid ... \n");
                $credname = 'tug_cred_' . $action_piid;
                $acceptCred = $studentAgent->acceptCredential($action_piid, $credname);
                $this->assertNotNull($acceptCred);
            }

            sleep(2);
        }

        // done, credential issued!
        self::log("done, credential $credId issued!\n");
        $this->assertTrue(true);
    }
}

class AgentMockLogger2
{
    private $agentName;

    public function __construct(string $agentName)
    {
        $this->agentName = $agentName;
    }

    public function warning($text)
    {
        $text = 'Warning: ' . $text . "\n";
        $this->writeItOut($text);
    }

    public function info($text)
    {
        $text = 'Info: ' . $text . "\n";
        $this->writeItOut($text);
    }

    private function writeItOut($text)
    {
        fwrite(STDERR, "[$this->agentName] " . $text . "\n");
    }
}
