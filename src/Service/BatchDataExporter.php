<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use ItsDangerous\Signer\TimedSerializer;
use ItsDangerous\Support\ClockProvider;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BatchDataExporter
{
    private $logger;
    private $exporterUrl;
    private $authHeader;

    public function __construct($logger, $exporterUrl, $secretKey = "fooKRAKENbar")
    {
        $logger->info("Initializing BatchDataExporter for exporter at $exporterUrl ...");
        $this->logger = $logger;
        $this->exporterUrl = $exporterUrl;

        $this->authHeader = $this->initAuthHeader($secretKey);

        $this->checkConnection();
        $this->logger->info('BatchDataExporter initialized!');
    }

    public function checkConnection(): bool
    {
        $url = $this->exporterUrl;

        try {
            $res = SimpleHttpClient::request($url . "/upload", "GET", [], $this->authHeader);
        } catch (Exception $exception) {
            $this->logger->error($exception);
            return false;
        }

        if ($res['status_code'] !== 200) {
            $this->logger->warning("Checked connection to $url, status code: " . $res['status_code']);
            if ($res['contents']) {
                $this->logger->warning($res['contents']);
            }
            return false;
        }

        return true;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function exportData(string $signedCredential, string $type, string $id): bool
    {
        $this->logger->info("Credential type: " . gettype($signedCredential));
        $data = ['credential' => new DataPart($signedCredential, "credential.json")];
        $url = $this->exporterUrl . "/upload?type=" . $type . "&id=" . $id;

        // - POST credential it to $exporterUrl/upload (as file or POST parameter?)
        // SimpleHttpClient::request(); → add parameter for data and for HTTP header?
        $res = SimpleHttpClient::request($url, "POST", $data, $this->authHeader, false);

        if ($res['contents']) {
            $this->logger->info($res['contents']);
        }
        if ($res['status_code'] !== 200) {
            $this->logger->warning("Failed exporting credential to $url, status code: " . $res['status_code']);
            return false;
        }

        $this->logger->info("Exporting done!");
        return true;
    }

    private function initAuthHeader(string $secretKey): array
    {
        ClockProvider::$EPOCH = 0; // per default this is set to a weird magic number ...

        $ser = new TimedSerializer($secretKey);
        $token = $ser->dumps("KRAKEN");

        //$this->logger->info("Auth Token: " . $token);

        return ['auth_bearer' => $token];
    }
}
