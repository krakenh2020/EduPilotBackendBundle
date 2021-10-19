<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Entity\Diploma;

class DidExternalApi implements DidConnectionProviderInterface
{
    private static $UNI_AGENT_URL = ''; //'https://agent.university-agent.demo:8082';
    public static $classLogger = null;

    private $didConnections;
    private $courseApi;
    private $diplomaApi;

    /**
     * see: https://stackoverflow.com/a/49299689/782920 .
     */
    private static function getHttpCode($http_response_header): int
    {
        if (is_array($http_response_header)) {
            $parts = explode(' ', $http_response_header[0]);
            if (count($parts) > 1) { //HTTP/1.0 <code> <text>
                return intval($parts[1]);
            }
        }

        return 0;
    }

    public static function requestInsecure(string $url, string $method = 'GET', array $data = []): array
    {
        $options = [
            'http' => [
                'method' => $method,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        if ($data) {
            $options['http']['header'] = 'Content-type: application/json';
            $options['http']['content'] = json_encode($data);
        }

        try {
            $body = @file_get_contents($url, false, stream_context_create($options));
        } catch (Exception $e) {  
            DidExternalApi::$classLogger->warning("$e");
            $body = FALSE;
        }  

        if($body === FALSE) {
            DidExternalApi::$classLogger->warning("Error while connecting to $url");
            return [
                'contents' => '',
                'status_code' => -1,
             ];
        }

        return [
            'contents' => $body,
            'status_code' => DidExternalApi::getHttpCode($http_response_header),
        ];
    }

    public static function checkConnection(string $baseUrl): bool
    {
        $PATH_CONNECTIONS = '/connections';
        $url = $baseUrl.$PATH_CONNECTIONS;
        // todo: unsecure
        $res = DidExternalApi::requestInsecure($url);

        if ($res['status_code'] !== 200) {
            DidExternalApi::$classLogger->warning("Check connection to $baseUrl, status code: ".$res['status_code']);
            return false;
        }

        return true;
    }

    public static function createInvitation(string $baseUrl): string
    {
        $alias = "TU Graz KRAKEN Demo";
        $PATH_CREATE_INVITATION = '/connections/create-invitation';
        $url = $baseUrl.$PATH_CREATE_INVITATION."?alias=".urlencode($alias);

        try {
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'POST');
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    public function __construct(CourseGradeProviderInterface $courseApi, DiplomaProviderInterface $diplomaApi, ContainerInterface $container, LoggerInterface $logger)
    {
        $logger->info('I just got the logger');
        $this->logger = $logger;
        DidExternalApi::$classLogger = $logger;
        $this->container = $container;

        $agent1 = $container->getParameter('vc4sm.aries_agent_university');
        $agent2 = $container->getParameter('vc4sm.aries_agent_university2');

        $this->logger->info("agent1: $agent1");
        $this->logger->info("agent2: $agent2");
        if(DidExternalApi::checkConnection($agent1)) {
            $agent = $agent1;
        } elseif (DidExternalApi::checkConnection($agent2)) {
            $agent = $agent2;
        } else {
            throw new Exception('None of the two configured agents is reachable ...');
        }

        DidExternalApi::$UNI_AGENT_URL = $agent;
        $this->logger->info("Using Aries agent at $agent.");

        $this->courseApi = $courseApi;
        $this->diplomaApi = $diplomaApi;

        // DidConnections
        $this->didConnections = [];
        $didConnection1 = new DidConnection();
        // todo: change
        $didConnection1->setIdentifier('tug');
        // todo: change
        $didConnection1->setName('Graz University of Technology');

        // todo: remove invitation intermediate states..
        $didConnection1->setInvitation('try');
        if (DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            $didConnection1->setInvitation('conn');
            $invitation = DidExternalApi::createInvitation(DidExternalApi::$UNI_AGENT_URL);
            $didConnection1->setInvitation('inv?');
            if ($invitation) {
                $didConnection1->setInvitation($invitation);
            }
        }

        $this->didConnections[] = $didConnection1;

        // TODO: move to configuration, use variable from ansible
        $this->uniAgentDID = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr';

        $this->logger->info('DidExternalApi initialized!');
    }

    private static function listConnections(string $baseUrl): string
    {
        $PATH_CREATE_INVITATION = '/connections';
        $url = $baseUrl.$PATH_CREATE_INVITATION;
        try {
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'GET');
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    private static function getConnectionById(string $baseUrl, string $id): string
    {
        $PATH_CREATE_INVITATION = '/connections/'.$id;
        $url = $baseUrl.$PATH_CREATE_INVITATION;
        try {
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'GET');
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    private static function acceptInviteRequest(string $baseUrl, string $identifier): string
    {
        $PATH_CREATE_INVITATION = '/connections/'.$identifier.'/accept-request';
        $url = $baseUrl.$PATH_CREATE_INVITATION;
        try {
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'POST');
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    public function getDidConnectionById(string $identifier): ?DidConnection
    {
        $this->logger->info('getDidConnectionById ...');
        $didConnection = new DidConnection();
        $didConnection->setIdentifier($identifier);
        // todo: change
        $didConnection->setName('Graz University of Technology');

        $oneAccepted = false;
        $connectionId = '';
        if (!DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            throw new Exception('No Connection');

            return null;
        }
        $inviteContents = DidExternalApi::listConnections(DidExternalApi::$UNI_AGENT_URL);
        $invites = json_decode($inviteContents);
        
        $this->logger->info("Invites: $inviteContents");

        // check if request actually returned something:
        if(!property_exists($invites, 'results')) { 
            $this->logger->error('Agent did not return any connections?');
            throw new Exception("Agent did not return any connections.");
            return null; 
            // in this case, the user's browser might use cookies which were generated before the agent was restarted the last time
            // FIX: if this happens, the frontend needs to reset the corresponding cookie 
        }

        foreach ($invites->results as $invite) {
            // todo: skip accept and return good result if State === responded or completed.
            if ($invite->InvitationID === $identifier && $invite->State === 'requested') {
                $connectionId = $invite->ConnectionID;
                $acceptRes = DidExternalApi::acceptInviteRequest(DidExternalApi::$UNI_AGENT_URL, $connectionId);
                $this->logger->info("acceptRes: $acceptRes");
                if ($acceptRes === '') {
                    throw new Exception('Accept failed');

                    return null;
                }
                $oneAccepted = true;
                break;
            } elseif ($invite->InvitationID === $identifier && $invite->State === 'responded') {
                $this->logger->info("Invitation found but already accepted ... moving on.");
                $connectionId = $invite->ConnectionID;
                $oneAccepted = true;
                break;
            } else {
                $this->logger->info("InvitationID: $invite->InvitationID, \n requested: $identifier, \n state: $invite->State");
            }
        }

        if (!$oneAccepted) {
            $this->logger->error('No invite accepted');

            return null;
        }

        $connContents = DidExternalApi::getConnectionById(DidExternalApi::$UNI_AGENT_URL, $connectionId);
        $conn = json_decode($connContents);
        if ($conn->result->State === 'responded' || $conn->result->State === 'completed') {
            $didConnection->setInvitation(json_encode($conn->result, 0, 512));

            return $didConnection;
        }

        throw new Exception('Accepted connection not found');

        return null;
    }

    public function getDidConnections(): array
    {
        return $this->didConnections;
    }

    public function getCredentialById(string $identifier): ?Credential
    {
        $credential = new Credential();
        $credential->setIdentifier($identifier);
        $credential->setMyDid('asdf');
        $credential->setTheirDid('asdf');
        $credential->setStatus('asdf');

        return $credential;
    }

    public static function buildOfferRequest(string $myDid, string $theirDid, $api, $type, $id)
    {

        if ($type === 'diplomas') {
            $diploma = $api->getDiplomaById($id);
            $cred_type = "Academic Diploma";
            $cred_attributes = [
            [
                'name' => "Credential Type",
                'value' => $cred_type,
            ],
            [
                'name' => "Name",
                'value' => $diploma->getName(),
            ],
            [
                'name' => "Degree",
                'value' => $diploma->getAcademicDegree(),
            ]
        ];

        } elseif ($type === 'course-grades') {
            $courseGrade = $api->getCourseGradeById($id);   
            $cred_type = "Academic Course Grade";
            $cred_attributes = [
            [
                'name' => "Credential Type",
                'value' => $cred_type,
            ],
            [
                'name' => "Name",
                'value' => $courseGrade->getName(),
            ],
            [
                'name' => "Grade",
                'value' => $courseGrade->getGrade(),
            ],
            [
                'name' => "Credits",
                'value' =>  $courseGrade->getCredits(),
            ]
        ];
        }

        $cred_preview = [
            '@type' => "https://didcomm.org/issue-credential/1.0/credential-preview",
            'attributes' => $cred_attributes,
        ];

        $offer_credential = [
                '@type' => "https://didcomm.org/issue-credential/1.0/offer-credential",
                'comment' => $cred_type . " offer",
                'credential_preview' => $cred_preview,
        ];

        $credoffer = [
                'my_did' => $myDid,
                'their_did' => $theirDid,
                'offer_credential' => $offer_credential,
            ];

        return $credoffer;
    }

    private static function sendOfferRequest(string $baseUrl, string $myDid, string $theirDid, $api, $type, $id): string
    {
        $PATH_CREATE_INVITATION = '/issuecredential/send-offer';
        $url = $baseUrl.$PATH_CREATE_INVITATION;
        try {

            $credoffer = DidExternalApi::buildOfferRequest($myDid, $theirDid, $api, $type, $id);
            
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'POST', $credoffer);
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    public function sendOffer(Credential $data): ?Credential
    {
        //$data->setIdentifier('some id');
        //$data->setStatus('try offer...');

        if (!DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            throw new Exception('No Connection');

            return null;
        }

        DidExternalApi::$classLogger->info("Send offer for status:" . $data->getStatus());
        //DidExternalApi::$classLogger->info("Send offer for credential:" . $data->getIdentifier());
        $id = $data->getStatus();
        $type = explode('/', $id)[1];
        $id = explode('/', $id)[2];

        $api = $type === 'diplomas' ? $this->diplomaApi : $this->courseApi;

        $response = DidExternalApi::sendOfferRequest(DidExternalApi::$UNI_AGENT_URL, $data->getMyDid(), $data->getTheirDid(), $api, $type, $id);

        // todo: remove this temp thing.
        $data->setIdentifier($id);
        $data->setMyDid($response);
        $data->setStatus('offer!');

        return $data;
    }

    // todo: accept diploma or coursegrade
    private static function acceptRequestRequest(string $baseUrl, string $credoffer_piid, array $cred): string
    {
        $PATH_CREATE_INVITATION = '/issuecredential/'.$credoffer_piid.'/accept-request';
        $url = $baseUrl.$PATH_CREATE_INVITATION;

        // todo: add diploma
        try {
            // todo: unsecure
            $res = DidExternalApi::requestInsecure($url, 'POST', $cred);
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }

    private static function signCredential(string $baseUrl, array $cred): string
    {
        $PATH_SIGN_CRED = '/verifiable/signcredential';
        $url = $baseUrl.$PATH_SIGN_CRED;
        $credJson = json_encode($cred);
        DidExternalApi::$classLogger->info("Signing a credential using $url: $credJson");

        try {
            $res = DidExternalApi::requestInsecure($url, 'POST', $cred);
            if ($res['status_code'] !== 200) {
                $code = $res['status_code'];
                DidExternalApi::$classLogger->error("Credential signing failed: HTTP $code");
            }

            return $res['contents'];
        } catch (Exception $exception) {
            DidExternalApi::$classLogger->error("Credential signing failed: $exception");
        }
    }

    public function acceptRequest(Credential $data): ?Credential
    {
        $this->logger->info("acceptRequest: Issuing a credential ...");

        // todo: don't pass id via status..
        $id = $data->getStatus();
        $data->setIdentifier('some id');
        $data->setStatus('try accept request...');

        if (!DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            throw new Exception('No Connection');

            return null;
        }

        // todo: fix naming
        $credoffer_piid = $data->getMyDid();

        $type = explode('/', $id)[1];
        $id = explode('/', $id)[2];

        // STEP 1: Build credential (for signing) --> just the VC
        $this->logger->info("STEP 1: Build credential of type: $type");

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
                        'id' => 'https://kraken-edu.iaik.tugraz.at/diploma/'.$diploma->getIdentifier(),
                        'credentialSubject' => [
                            'id' => 'sample-student-id2',
                            'name' => $diploma->getName(),
                            // todo: fix typo
                            'achievenmentDate' => $diploma->getAchievenmentDate(),
                            'academicDegree' => $diploma->getAcademicDegree(),
                        ],
                        'issuanceDate' => '2021-01-01T19:23:24Z',  
                        'issuer' => $this->uniAgentDID     
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
                        'id' => 'https://kraken-edu.iaik.tugraz.at/coursegrade/'.$courseGrade->getIdentifier(),
                        'credentialSubject' => [
                            'id' => 'sample-student-id2',
                            'name' => $courseGrade->getName(),
                            // todo: fix typo
                            'achievenmentDate' => $courseGrade->getAchievenmentDate(),
                            'grade' => $courseGrade->getGrade(),
                            'credits' => $courseGrade->getCredits(),
                        ],
                        'issuanceDate' => '2021-01-01T19:23:24Z',
                        'issuer' => $this->uniAgentDID
                    ];
        } else {
            $this->logger->error("Unknown credential type: $type");
        }

        // STEP 2: Sign credential using /verifiable/signcredential
        $this->logger->info("STEP 2: Sign credential");

        $signrequest = [
            'created' => '2021-06-15T15:04:06Z',
            'did' => $this->uniAgentDID,
            'signatureType' => 'Ed25519Signature2018',
            'credential' => $cred
        ];

        $signedCred = DidExternalApi::signCredential(DidExternalApi::$UNI_AGENT_URL, $signrequest);
        $signedCred = json_decode($signedCred)->verifiableCredential;

        // STEP 3: Build credential datastructure
        $this->logger->info("Build credential datastructure");
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
        $this->logger->info("STEP 4: Issue credential ...");
        $response = DidExternalApi::acceptRequestRequest(DidExternalApi::$UNI_AGENT_URL, $credoffer_piid, $credAnswer);

        // todo: remove this temp thing.
        $data->setMyDid($response);
        $data->setStatus('accept request!');

        return $data;
    }
}
