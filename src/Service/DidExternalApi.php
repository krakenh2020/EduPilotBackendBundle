<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use VC4SM\Bundle\Entity\DidConnection;

class DidExternalApi implements DidConnectionProviderInterface
{
    // todo: make this configurable
    private static $UNI_AGENT_URL = 'https://localhost:8082';

    private $didConnections;

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

    private static function requestInsecure(string $url, string $method = 'GET'): array
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

        return [
            'contents' => file_get_contents($url, false, stream_context_create($options)),
            'status_code' => DidExternalApi::getHttpCode($http_response_header),
        ];
    }

    private static function checkConnection(string $baseUrl): bool
    {
        $PATH_CONNECTIONS = '/connections';
        $url = $baseUrl.$PATH_CONNECTIONS;
        // todo: unsecure
        $res = DidExternalApi::requestInsecure($url);

        if ($res['status_code'] === 200) {
            throw new Exception('Check connection, status code '. $res['status_code']);
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

    public function __construct()
    {
        // DidConnections
        $this->didConnections = [];
        $didConnection1 = new DidConnection();
        $didConnection1->setIdentifier('graz');
        $didConnection1->setName('Graz');

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
    }

    public function getDidConnectionById(string $identifier): ?DidConnection
    {
        foreach ($this->didConnections as $DidConnection) {
            if ($DidConnection->getIdentifier() === $identifier) {
                return $DidConnection;
            }
        }

        return null;
    }

    public function getDidConnections(): array
    {
        return $this->didConnections;
    }
}
