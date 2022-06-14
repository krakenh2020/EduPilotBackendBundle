<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;

class AriesAgentClient
{
    private $logger;
    private $agentUrl;
    private $agentDID;

    public function __construct($logger, $agentUrl, $agentDID)
    {
        $logger->info("Initializing AriesAgentClient for agent at $agentUrl ...");
        $this->logger = $logger;
        $this->agentUrl = $agentUrl;
        $this->agentDID = $agentDID;

        $this->checkConnection();
        $this->logger->info('AriesAgentClient initialized!');
    }

    public function checkConnection(): bool
    {
        $PATH_CONNECTIONS = '/connections';
        $url = $this->agentUrl.$PATH_CONNECTIONS;

        try {
            $res = SimpleHttpClient::request($url);
        } catch (Exception $exception) {
            return false;
        }

        if ($res['status_code'] !== 200) {
            $this->logger->warning("Checked connection to $url, status code: ".$res['status_code']);

            return false;
        }

        return true;
    }

    private function printError($methodname, $res)
    {
        $this->logger->warning($methodname.' status code: '.$res['status_code'].' -> '.$res['contents']);
    }

    private function request(string $methodname, string $method, string $url, array $data = [])
    {
        try {
            $res = SimpleHttpClient::request($url, $method, $data);
            if ($res['status_code'] !== 200) {
                $this->printError($methodname, $res);

                return null;
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->error("$methodname failed: $exception");
            throw new Exception("$methodname failed: $exception");
        }
    }

    public function createInvitation(string $alias = 'TU Graz KRAKEN Demo'): ?string
    {
        $PATH_CREATE_INVITATION = '/connections/create-invitation';
        $url = $this->agentUrl.$PATH_CREATE_INVITATION.'?alias='.urlencode($alias);

        return $this->request('createInvitation', 'POST', $url);
    }

    public function receiveConnectionInvite($invite): ?string
    {
        $PATH_RECEIVE_INVITATION = '/connections/receive-invitation';
        $url = $this->agentUrl.$PATH_RECEIVE_INVITATION;

        return $this->request('receiveConnectionInvite', 'POST', $url, $invite);
    }

    public function acceptConnectionInvite(string $connectionId): ?string
    {
        $PATH_ACCEPT_INVITATION = '/connections/'.$connectionId.'/accept-invitation';
        $url = $this->agentUrl.$PATH_ACCEPT_INVITATION;

        return $this->request('acceptConnectionInvite', 'POST', $url);
    }

    public function listConnections(): ?string
    {
        $PATH_GET_CONNECTIONS = '/connections';
        $url = $this->agentUrl.$PATH_GET_CONNECTIONS;

        return $this->request('listConnections', 'GET', $url);
    }

    public function getConnectionById(string $id): ?string
    {
        $PATH_GET_CONNECTION = '/connections/'.$id;
        $url = $this->agentUrl.$PATH_GET_CONNECTION;

        return $this->request('getConnectionById', 'GET', $url);
    }

    public function acceptInviteRequest(string $identifier): ?string
    {
        $PATH_ACCEPT_INVITATION = '/connections/'.$identifier.'/accept-request';
        $url = $this->agentUrl.$PATH_ACCEPT_INVITATION;

        return $this->request('acceptInviteRequest', 'POST', $url);
    }

    public function sendOfferRequest($credoffer): ?string
    {
        $PATH_SEND_OFFER = '/issuecredential/send-offer';
        $url = $this->agentUrl.$PATH_SEND_OFFER;

        return $this->request('sendOfferRequest', 'POST', $url, $credoffer);
    }

    public function acceptRequestRequest(string $credoffer_piid, array $cred): ?string
    {
        $PATH_ACCEPT_RREQUEST = '/issuecredential/'.$credoffer_piid.'/accept-request';
        $url = $this->agentUrl.$PATH_ACCEPT_RREQUEST;

        return $this->request('acceptRequestRequest', 'POST', $url, $cred);
    }

    public function getIssuercredentialActions(): ?array
    {
        $PATH_GET_CREDACTIONS = '/issuecredential/actions';
        $url = $this->agentUrl.$PATH_GET_CREDACTIONS;

        $r = $this->request('getIssuercredentialActions', 'GET', $url);

        return $r !== null ? json_decode($r)->actions : null;
    }

    public function acceptCredentialOffer(string $credoffer_piid): ?string
    {
        $PATH_ACCEPT_CREDOFFER = '/issuecredential/'.$credoffer_piid.'/accept-offer';
        $url = $this->agentUrl.$PATH_ACCEPT_CREDOFFER;

        return $this->request('acceptCredentialOffer', 'POST', $url);
    }

    public function signCredential(array $cred): ?string
    {
        $PATH_SIGN_CRED = '/verifiable/signcredential';
        $url = $this->agentUrl.$PATH_SIGN_CRED;

        $credJson = json_encode($cred);
        $this->logger->info("Signing a credential using $url: $credJson");

        return $this->request('signCredential', 'POST', $url, $cred);
    }

    public function acceptCredential(string $credoffer_piid, string $cred_name): ?string
    {
        $PATH_ACCEPT_CRED = '/issuecredential/'.$credoffer_piid.'/accept-credential';
        $url = $this->agentUrl.$PATH_ACCEPT_CRED;

        return $this->request('acceptCredential', 'POST', $url, ['names' => [$cred_name]]);
    }

    /**
     * @return mixed
     */
    public function getAgentUrl()
    {
        return $this->agentUrl;
    }
}
