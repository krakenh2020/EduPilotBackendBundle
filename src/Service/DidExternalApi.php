<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\DidConnection;

class DidExternalApi implements DidConnectionProviderInterface
{
    //private static $UNI_AGENT_URL = ''; //'https://agent.university-agent.demo:8082';
    //public static $classLogger = null;

    private $didConnections;
    private $courseApi;
    private $diplomaApi;
    private $logger;
    private $uniAgentDID;
    private $agent;
    private $exporterURL;
    private $exporterSecretKey;

    public function __construct(CourseGradeProviderInterface $courseApi, DiplomaProviderInterface $diplomaApi, ContainerInterface $container, LoggerInterface $logger)
    {
        $logger->info('Initializing DidExternalApi ...');
        $this->logger = $logger;
        //DidExternalApi::$classLogger = $logger;

        // TODO: move to configuration (use variable from ansible)
        $this->uniAgentDID = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr';
        //$this->uniAgentDID = 'did:ebsi:zqZ5txUaSG7c5K7wZsFweWo';

        $agentUrl = $this->probeConfiguredAgents($container);
        $this->agent = new AriesAgentClient($this->logger, $agentUrl, $this->uniAgentDID);

        $this->exporterURL = "http://127.0.0.1:5000"; //  TODO: $container->getParameter('vc4sm.batch_exporter_url');
        $this->exporterSecretKey = "fooKRAKENbar"; // TODO: $container->getParameter('vc4sm.batch_exporter_secret');

        $this->courseApi = $courseApi;
        $this->diplomaApi = $diplomaApi;

        $this->logger->info('DidExternalApi initialized!');
    }

    private function probeConfiguredAgents(ContainerInterface $container)
    {
        $agent1 = $container->getParameter('vc4sm.aries_agent_university');
        $agent2 = $container->getParameter('vc4sm.aries_agent_university2');
        $this->logger->info("agent1: $agent1");
        $this->logger->info("agent2: $agent2");

        if ($this->checkConnection($agent1)) {
            $agentUrl = $agent1;
        } elseif ($this->checkConnection($agent2)) {
            $agentUrl = $agent2;
        } else {
            throw new Exception('None of the two configured agents is reachable ...');
        }

        //DidExternalApi::$UNI_AGENT_URL = $agentUrl;
        $this->logger->info("Using Aries agent at $agentUrl.");

        return $agentUrl;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function checkConnection(string $baseUrl): bool
    {
        $PATH_CONNECTIONS = '/connections'; // path known to exist for aries agents
        $url = $baseUrl . $PATH_CONNECTIONS;

        try {
            $res = SimpleHttpClient::request($url);
        } catch (Exception $e) {
            $this->logger->warning($e);

            return false;
        }

        if ($res['status_code'] !== 200) {
            return false;
        }

        return true;
    }

    public function checkAgentConnection(): void
    {
        if (!$this->agent->checkConnection()) {
            throw new Exception('No connection to agent at ' . $this->agent->getAgentUrl());
        }
    }

    public function getDidConnectionById(string $identifier): ?DidConnection
    {
        $this->logger->info('getDidConnectionById for ' . $identifier);
        $this->checkAgentConnection();

        $didConnection = new DidConnection();
        $didConnection->setIdentifier($identifier);
        $didConnection->setName('Graz University of Technology');

        $oneAccepted = false;
        $connectionId = '';
        $inviteContents = $this->agent->listConnections();

        $invites = json_decode($inviteContents);
        //$this->logger->info("Invites: $inviteContents");

        // check if request actually returned something:
        if (!property_exists($invites, 'results')) {
            $this->logger->info(print_r($invites, true));
            $this->logger->error('Agent did not return any connections?');
            throw new Exception('Agent did not return any connections.');
            //return null;
            // in this case, the user's browser might use cookies which were generated before the agent was restarted the last time
            // FIX: if this happens, the frontend needs to reset the corresponding cookie
        }

        foreach ($invites->results as $invite) {
            // todo: skip accept and return good result if State === responded or completed.
            if ($invite->InvitationID === $identifier && $invite->State === 'requested') {
                $this->logger->info('Invitation found and state is "requested"! Accepting it ...');
                $connectionId = $invite->ConnectionID;
                $acceptRes = $this->agent->acceptInviteRequest($connectionId);

                $this->logger->info("acceptRes: $acceptRes");
                if ($acceptRes === null || $acceptRes === '') {
                    $this->logger->warning('Failed to accept connection request.');

                    return null;
                }
                $oneAccepted = true;
                break;
            } elseif ($invite->InvitationID === $identifier && ($invite->State === 'responded' || $invite->State === 'completed')) {
                $this->logger->info('Invitation found and state already accepted ... moving on.');
                $connectionId = $invite->ConnectionID;
                $oneAccepted = true;
                break;
            } /*else {
                $this->logger->info("open invite, ID: $invite->InvitationID, \n requested: $identifier, \n state: $invite->State");
            }*/
        }

        if (!$oneAccepted) {
            $this->logger->warning('Invite not (yet) accepted by student.');

            return null;
        }

        // get details of connection which we accepted:
        $connContents = $this->agent->getConnectionById($connectionId);
        $conn = json_decode($connContents);

        // since we now accepted the connection, check if that was successful:
        if ($conn->result->State === 'responded' || $conn->result->State === 'completed') {
            $didConnection->setInvitation(json_encode($conn->result, 0, 512));

            // return the accepted connection:
            return $didConnection;
        }

        throw new Exception('Connection not found ...');
        //return null;
    }

    /**
     * Each student needs a fresh DID connection.
     * To ensure that there is always an unused connection available,
     * we simply create a new one every time the frontend requests one
     * (and only return that one).
     *
     * This is a "quickfix" since otherwise we would need to track state
     * of the connections, and also track which one is used by which student.
     */
    public function initNewDidConnection()
    {
        $this->logger->info('initNewDidConnection ...');

        // DidConnections
        $this->didConnections = [];
        $didConnection1 = new DidConnection();

        $didConnection1->setIdentifier('tug');
        $didConnection1->setName('DID Connection to KRAKEN Pilot at Graz University of Technology');

        $invitation = $this->agent->createInvitation();
        $invitation = json_decode($invitation, true);
        $invitation['invitation']['imageUrl'] = 'https://www.tugraz.at/typo3conf/ext/tugraztemplateinternal/Resources/Public/Img/OpenGraph/tu_graz_start.jpg';
        $invitation = json_encode($invitation, JSON_UNESCAPED_SLASHES);

        if ($invitation) {
            $didConnection1->setInvitation($invitation);
            $this->logger->info('initNewDidConnection: created new DID connection: ' . print_r($didConnection1, true));
        } else {
            $this->logger->warning('initNewDidConnection: could not create new connection.');
        }

        $this->didConnections[] = $didConnection1;
    }

    public function getDidConnections(): array
    {
        $this->initNewDidConnection();

        return $this->didConnections;
    }

    public function getCredentialById(string $identifier): ?Credential
    {
        // TODO: check if this is actually used by frontend

        $credential = new Credential();
        $credential->setIdentifier($identifier);
        $credential->setMyDid('asdf');
        $credential->setTheirDid('asdf');
        $credential->setStatus('asdf');

        return $credential;
    }

    public function buildOfferRequest(string $myDid, string $theirDid, $api, $type, $id)
    {
        if ($type === 'diplomas') {
            $diploma = $api->getDiplomaById($id);
            $cred_type = 'Academic Diploma (' . $diploma->getStudyName() . ')';
            $cred_attributes = [
                // [
                //     'name' => "Credential Type",
                //     'value' => $cred_type,
                // ],
                // [
                //     'name' => "Name",
                //     'value' => $diploma->getName(),
                // ],
                // [
                //     'name' => "Degree",
                //     'value' => $diploma->getAcademicDegree(),
                // ],
                // experimental:
                [
                    'name' => 'credentialSubject.name',
                    'value' => 'Degree Name',
                ],
                [
                    'name' => 'credentialSubject.achievenmentDate',
                    'value' => 'Degree Date',
                ],
                [
                    'name' => 'credentialSubject.academicDegree',
                    'value' => 'Degree',
                ],
            ];
        } elseif ($type === 'course-grades') {
            $courseGrade = $api->getCourseGradeById($id);
            $cred_type = 'Academic Course Grade (' . $courseGrade->getCourseTitle() . ')';
            $cred_attributes = [
                // [
                //     'name' => "Credential Type",
                //     'value' => $cred_type,
                // ],
                // [
                //     'name' => "Name",
                //     'value' => $courseGrade->getName(),
                // ],
                // [
                //     'name' => "Grade",
                //     'value' => $courseGrade->getGrade(),
                // ],
                // [
                //     'name' => "Credits",
                //     'value' =>  $courseGrade->getCredits(),
                // ]
                [
                    'name' => 'credentialSubject.name',
                    'value' => 'Course Name',
                ],
                [
                    'name' => 'credentialSubject.achievenmentDate',
                    'value' => 'Grade Date',
                ],
                [
                    'name' => 'credentialSubject.grade',
                    'value' => 'Grade',
                ],
                [
                    'name' => 'credentialSubject.credits',
                    'value' => 'Credits',
                ],
            ];
        } else {
            $this->logger->error('Unknown credential type: ' . $type);

            return null;
        }

        $cred_preview = [
            '@type' => 'https://didcomm.org/issue-credential/1.0/credential-preview',
            'attributes' => $cred_attributes,
        ];

        $offer_credential = [
            '@type' => 'https://didcomm.org/issue-credential/1.0/offer-credential',
            'comment' => $cred_type,
            'credential_preview' => $cred_preview,
        ];

        $credoffer = [
            'my_did' => $myDid,
            'their_did' => $theirDid,
            'offer_credential' => $offer_credential,
        ];

        return $credoffer;
    }

    public function sendOffer(Credential $data): ?Credential
    {
        //$data->setIdentifier('some id');
        //$data->setStatus('try offer...');

        $this->checkAgentConnection();

        $this->logger->info('Send offer for credential: ' . $data->getStatus());

        $id = $data->getStatus();
        $type = explode('/', $id)[1];
        $id = explode('/', $id)[2];

        $api = $type === 'diplomas' ? $this->diplomaApi : $this->courseApi;

        $credoffer = self::buildOfferRequest($data->getMyDid(), $data->getTheirDid(), $api, $type, $id);
        $response = $this->agent->sendOfferRequest($credoffer);

        if ($response === null) {
            return null;
        }

        // todo: remove this temp thing.
        $data->setIdentifier($id);
        $data->setMyDid($response);
        $data->setStatus('offer!');

        return $data;
    }


    public function acceptRequest(Credential $data): ?Credential
    {
        $this->logger->info('acceptRequest: Issuing a ' . $data->getStatus() . ' credential ...');

        $this->checkAgentConnection();

        // todo: don't pass id via status..
        $id = $data->getStatus();
        $data->setIdentifier('some id');
        $data->setStatus('try accept request...');

        // todo: fix naming
        $credoffer_piid = $data->getMyDid();

        // check if credoffer with $credoffer_piid already accepted by student
        //   via https://github.com/hyperledger/aries-framework-go/blob/main/docs/rest/openapi_demo.md#how-to-issue-credentials-through-the-issue-credential-protocol
        $actions = $this->agent->getIssuercredentialActions();
        $found = false;
        foreach ($actions as $action) {
            if ($action->PIID === $credoffer_piid) {
                $found = true;
            }
        }

        if (!$found) {
            $this->logger->warning("Credential offer $credoffer_piid not (yet) accepted by student.");

            return null;
        } else {
            $this->logger->info("Credential offer $credoffer_piid accepted by student! Issuing $id ...");
        }

        $type = explode('/', $id)[1];
        $id = explode('/', $id)[2];


        $signedCred = $this->buildAndSignCred($type, $id);
        if ($signedCred === null) return null;


        // STEP 3: Build credential datastructure
        $this->logger->info('Build credential datastructure');
        $credAnswer = [
            'issue_credential' => [
                'credentials~attach' => [
                    [
                        //'lastmod_time' => '0001-01-01T00:00:00Z',
                        'data' => [
                            'json' => $signedCred,
                        ],
                    ],
                ],
            ],
        ];

        // STEP 4: Issue credential
        $this->logger->info('STEP 4: Issue credential ...');

        // (this will fail if the student agent has not yet accepted the offer)
        $response = $this->agent->acceptRequestRequest($credoffer_piid, $credAnswer);

        // todo: remove this temp thing.
        $data->setMyDid($response);
        $data->setStatus('accept request!');

        return $data;
    }

    public function provideCredenitalToBatchExporter(Credential $data): ?Credential
    {
        $this->logger->info('provideCredenitalToBatchExporter: Issuing a ' . $data->getStatus() . ' credential to batch exporter ...');

        $this->checkAgentConnection();

        // todo: don't pass id via status..
        $id = $data->getStatus();

        $type = explode('/', $id)[1];
        $id = explode('/', $id)[2];

        $signedCred = $this->buildAndSignCred($type, $id, true);
        if ($signedCred === null) return null;

        $exporter = new BatchDataExporter($this->logger, $this->exporterURL, $this->exporterSecretKey);
        if (!$exporter->checkConnection()) {
            $this->logger->error("BatchExporter not reachable ...");
            throw new Exception('BatchExporter not reachable ...');
        }

        try {
            $status = $exporter->exportData($signedCred, $type, $id);
        } catch (ExceptionInterface $e) {
            $this->logger->error("BatchExporter error: $e");
            throw new Exception('BatchExporter error: $e');
        }

        if ($status != true) {
            return null;
        }

        // todo: remove this temp thing.
        $data->setMyDid('OK');
        $data->setStatus('accept request!');

        $this->logger->info('provideCredenitalToBatchExporter: DONE');

        return $data;
    }

    public function buildCred($type, $id)
    {
        if ($type === 'diplomas') {
            $diploma = $this->diplomaApi->getDiplomaById($id);

            $cred = [
                '@context' => [
                    'https://www.w3.org/2018/credentials/v1',
                    'https://www.w3.org/2018/credentials/examples/v1',
                ],
                'type' => [
                    'VerifiableCredential',
                    'UniversityDegreeCredential',
                ],
                'id' => 'https://kraken-edu.iaik.tugraz.at/diploma/' . $diploma->getIdentifier(),
                'credentialSubject' => [
                    'id' => 'sample-student-id2',
                    'name' => $diploma->getName(),
                    // todo: fix typo
                    'achievenmentDate' => $diploma->getAchievenmentDate(),
                    'academicDegree' => $diploma->getAcademicDegree(),
                ],
                'issuanceDate' => '2021-01-01T19:23:24Z',
                'issuer' => $this->uniAgentDID,
            ];
        } elseif ($type === 'course-grades') {
            $courseGrade = $this->courseApi->getCourseGradeById($id);

            $cred = [
                '@context' => [
                    'https://www.w3.org/2018/credentials/v1',
                    'https://www.w3.org/2018/credentials/examples/v1',
                ],
                'type' => [
                    'VerifiableCredential',
                    'UniversityDegreeCredential',
                ],
                'id' => 'https://kraken-edu.iaik.tugraz.at/coursegrade/' . $courseGrade->getIdentifier(),
                'credentialSubject' => [
                    'id' => 'sample-student-id2',
                    'name' => $courseGrade->getName(),
                    // todo: fix typo
                    'achievenmentDate' => $courseGrade->getAchievenmentDate(),
                    'grade' => $courseGrade->getGrade(),
                    'credits' => $courseGrade->getCredits(),
                ],
                'issuanceDate' => '2021-01-01T19:23:24Z',
                'issuer' => $this->uniAgentDID,
            ];
        } else {
            $this->logger->error("Unknown credential type: $type");

            return null;
        }

        return $cred;
    }

    /**
     * @throws Exception
     */
    public function buildAndSignCred($type, $id, $asJson = false)
    {
        // STEP 1: Build credential (for signing) --> just the VC
        $this->logger->info("STEP 1: Build credential of type: $type");
        $cred = $this->buildCred($type, $id);
        if ($cred == null) return null;

        // STEP 2: Sign credential using /verifiable/signcredential
        $this->logger->info('STEP 2: Sign credential');

        $signrequest = [
            'created' => '2021-06-15T15:04:06Z',
            'did' => $this->uniAgentDID,
            'signatureType' => 'Ed25519Signature2018',
            'credential' => $cred,
        ];

        $signedCred = $this->agent->signCredential($signrequest);
        if ($signedCred === null) {
            throw new Exception('ERROR: Could not sign credential (see log).');
        }

        $cred = json_decode($signedCred)->verifiableCredential;
        if ($asJson) return json_encode($cred, JSON_UNESCAPED_SLASHES);
        else return $cred;
    }
}
