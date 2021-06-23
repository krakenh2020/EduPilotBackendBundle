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
    private static $classLogger = null;

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

    private static function requestInsecure(string $url, string $method = 'GET', array $data = []): array
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
            DidExternalApi::$classLogger->error("$e");
            $body = FALSE;
        }  

        if($body === FALSE) {
            DidExternalApi::$classLogger->error("Error while connecting to $url");
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

    private static function checkConnection(string $baseUrl): bool
    {
        $PATH_CONNECTIONS = '/connections';
        $url = $baseUrl.$PATH_CONNECTIONS;
        // todo: unsecure
        $res = DidExternalApi::requestInsecure($url);

        if ($res['status_code'] !== 200) {
            DidExternalApi::$classLogger->error("Check connection to $baseUrl, status code: ".$res['status_code']);
            return false;
        }

        return true;
    }

    private static function createInvitation(string $baseUrl): string
    {
        $PATH_CREATE_INVITATION = '/connections/create-invitation';
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
        $didConnection1->setIdentifier('graz');
        // todo: change
        $didConnection1->setName('Graz');

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
        $didConnection->setName('Graz');

        $oneAccepted = false;
        $connectionId = '';
        if (!DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            throw new Exception('No Connection');

            return null;
        }
        $inviteContents = DidExternalApi::listConnections(DidExternalApi::$UNI_AGENT_URL);
        $invites = json_decode($inviteContents);
        
        // check if request actually returned something:
        if(!property_exists($invites, 'results')) { 
            $this->logger->error('Agent did not return any connections?');
            //throw new Exception("Agent did not return any connections.");
            return null; 
            // in this case, the user's browser might use cookies which were generated before the agent was restarted the last time
            // FIX: if this happens, the frontend needs to reset the corresponding cookie 
        }

        foreach ($invites->results as $invite) {
            // todo: skip accept and return good result if State === responded or completed.
            if ($invite->InvitationID === $identifier && $invite->State === 'requested') {
                $connectionId = $invite->ConnectionID;
                $acceptRes = DidExternalApi::acceptInviteRequest(DidExternalApi::$UNI_AGENT_URL, $connectionId);
                if ($acceptRes === '') {
                    throw new Exception('Accept failed');

                    return null;
                }
                $oneAccepted = true;
                break;
            }
        }
        if (!$oneAccepted) {
            throw new Exception('Non accepted');

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

    private static function sendOfferRequest(string $baseUrl, string $myDid, string $theirDid): string
    {
        $PATH_CREATE_INVITATION = '/issuecredential/send-offer';
        $url = $baseUrl.$PATH_CREATE_INVITATION;
        try {
            $credoffer = [
                'my_did' => $myDid,
                'their_did' => $theirDid,
                'offer_credential' => json_decode('{}'), // todo: clean up weird hack
            ];
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
        $data->setIdentifier('some id');
        $data->setStatus('try offer...');

        if (!DidExternalApi::checkConnection(DidExternalApi::$UNI_AGENT_URL)) {
            throw new Exception('No Connection');

            return null;
        }
        $response = DidExternalApi::sendOfferRequest(DidExternalApi::$UNI_AGENT_URL, $data->getMyDid(), $data->getTheirDid());

        // todo: remove this temp thing.
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

    public function acceptRequest(Credential $data): ?Credential
    {
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
        if ($type === 'diplomas') {
            $diploma = $this->diplomaApi->getDiplomaById($id);

            $cred = [
                'issue_credential' => [
                    'credentials~attach' => [
                        [
                            'lastmod_time' => '0001-01-01T00:00:00Z',
                            'data' => [
                                'json' => [
                                    '@context' => [
                                        'https://www.w3.org/2018/credentials/v1',
                                        'https://www.w3.org/2018/credentials/examples/v1',
                                    ],
                                    'type' => [
                                        'VerifiableCredential',
                                        'UniversityDegreeCredential',
                                    ],
                                    'id' => $diploma->getIdentifier(),
                                    'credentialSubject' => [
                                        'id' => 'sample-student-id2',
                                        'name' => $diploma->getName(),
                                        // todo: fix typo
                                        'achievenmentDate' => $diploma->getAchievenmentDate(),
                                        'academicDegree' => $diploma->getAcademicDegree(),
                                    ],
                                    'issuanceDate' => '2021-01-01T19:23:24Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        } elseif ($type === 'course_grades') {
            $courseGrade = $this->courseApi->getCourseGradeById($id);

            $cred = [
                'issue_credential' => [
                    'credentials~attach' => [
                        [
                            'lastmod_time' => '0001-01-01T00:00:00Z',
                            'data' => [
                                'json' => [
                                    '@context' => [
                                        'https://www.w3.org/2018/credentials/v1',
                                        'https://www.w3.org/2018/credentials/examples/v1',
                                    ],
                                    'type' => [
                                        'VerifiableCredential',
                                        'UniversityDegreeCredential',
                                    ],
                                    'id' => $courseGrade->getIdentifier(),
                                    'credentialSubject' => [
                                        'id' => 'sample-student-id2',
                                        'name' => $courseGrade->getName(),
                                        // todo: fix typo
                                        'achievenmentDate' => $courseGrade->getAchievenmentDate(),
                                        'grade' => $courseGrade->getGrade(),
                                        'credits' => $courseGrade->getCredits(),
                                    ],
                                    'issuanceDate' => '2021-01-01T19:23:24Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $response = DidExternalApi::acceptRequestRequest(DidExternalApi::$UNI_AGENT_URL, $credoffer_piid, $cred);

        // todo: remove this temp thing.
        $data->setMyDid($response);
        $data->setStatus('accept request!');

        return $data;
    }
}
