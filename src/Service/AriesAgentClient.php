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
                $this->logger->warning('createInvitation status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('createInvitation exception: ' . $exception);
            return '';
        }
    }

    public function getAgentUrl()
    {
        return $this->agentUrl;
    }

    public function listConnections(): string
    {
        $PATH_CREATE_INVITATION = '/connections';
        $url = $this->agentUrl . $PATH_CREATE_INVITATION;

        try {
            $res = SimpleHttpClient::request($url, 'GET');
            if ($res['status_code'] !== 200) {
                $this->logger->warning('listConnections status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('listConnections exception: ' . $exception);
        }

        return '';
    }

    public function getConnectionById(string $id): string
    {
        $PATH_CREATE_INVITATION = '/connections/' . $id;
        $url = $this->agentUrl . $PATH_CREATE_INVITATION;

        try {
            $res = SimpleHttpClient::request($url, 'GET');
            if ($res['status_code'] !== 200) {
                $this->logger->warning('getConnectionById status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('getConnectionById exception: ' . $exception);
        }

        return '';
    }

    public function acceptInviteRequest(string $identifier): string
    {
        $PATH_CREATE_INVITATION = '/connections/' . $identifier . '/accept-request';
        $url = $this->agentUrl . $PATH_CREATE_INVITATION;

        try {
            $res = SimpleHttpClient::request($url, 'POST');
            if ($res['status_code'] !== 200) {
                $this->logger->warning('acceptInviteRequest status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('acceptInviteRequest exception: ' . $exception);
        }

        return '';
    }

    public function sendOfferRequest(string $myDid, string $theirDid, $api, $type, $id): string
    {
        $PATH_CREATE_INVITATION = '/issuecredential/send-offer';
        $url = $this->agentUrl . $PATH_CREATE_INVITATION;

        try {
            $credoffer = DidExternalApi::buildOfferRequest($myDid, $theirDid, $api, $type, $id);

            $res = SimpleHttpClient::request($url, 'POST', $credoffer);
            if ($res['status_code'] !== 200) {
                $this->logger->warning('sendOfferRequest status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('sendOfferRequest exception: ' . $exception);
        }

        return '';
    }

    public function acceptRequestRequest(string $credoffer_piid, array $cred): string
    {
        // todo: accept diploma or coursegrade
        $PATH_CREATE_INVITATION = '/issuecredential/' . $credoffer_piid . '/accept-request';
        $url = $this->agentUrl . $PATH_CREATE_INVITATION;

        // todo: add diploma
        try {
            $res = SimpleHttpClient::request($url, 'POST', $cred);
            if ($res['status_code'] !== 200) {
                $this->logger->warning('acceptRequestRequest status code: ' . $res['status_code']);
                return '';
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->warning('acceptRequestRequest exception: ' . $exception);
        }

        return '';
    }

    public function signCredential(array $cred): string
    {
        $PATH_SIGN_CRED = '/verifiable/signcredential';
        $url = $this->agentUrl . $PATH_SIGN_CRED;

        $credJson = json_encode($cred);
        $this->logger->info("Signing a credential using $url: $credJson");

        try {
            $res = SimpleHttpClient::request($url, 'POST', $cred);
            if ($res['status_code'] !== 200) {
                $code = $res['status_code'];
                $this->logger->error("Credential signing failed: HTTP $code");
            }

            return $res['contents'];
        } catch (Exception $exception) {
            $this->logger->error("Credential signing failed: $exception");
            throw new Exception("Credential signing failed: $exception");
        }
    }
}
