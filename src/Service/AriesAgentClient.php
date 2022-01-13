<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

class AriesAgentClient
{
    private $logger;
    private $agentUrl;
    private $agentDID;

    public function __construct($logger, $agentUrl, $agentDID)
    {
        $logger->info('Initializing AriesAgentClient ...');
        $this->logger = $logger;
        $this->agentUrl = $agentUrl;
        $this->agentDID = $agentDID;

        $this->checkConnection();
    }

    public function checkConnection(): bool
    {
        $PATH_CONNECTIONS = '/connections';
        $url = $this->agentUrl . $PATH_CONNECTIONS;
        $res = SimpleHttpClient::requestInsecure($url);

        if ($res['status_code'] !== 200) {
            $this->logger->warning("Check connection to $url, status code: " . $res['status_code']);
            return false;
        }

        return true;
    }

    public function createInvitation(string $alias = 'TU Graz KRAKEN Demo'): string
    {

        $PATH_CREATE_INVITATION = '/connections/create-invitation';
        $url = $this->agentUrl . $PATH_CREATE_INVITATION . '?alias=' . urlencode($alias);

        try {
            $res = SimpleHttpClient::requestInsecure($url, 'POST');
            if ($res['status_code'] !== 200) {
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            return '';
        }
    }
}
