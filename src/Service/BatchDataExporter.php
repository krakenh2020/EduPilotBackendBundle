<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use ItsDangerous\Signer\TimedSerializer;
use ItsDangerous\Support\ClockProvider;

class BatchDataExporter
{
    private $logger;
    private $exporterUrl;
    private $authHeader;

    public function __construct($logger, $exporterUrl)
    {
        $logger->info("Initializing BatchDataExporter for exporter at $exporterUrl ...");
        $this->logger = $logger;
        $this->exporterUrl = $exporterUrl;

        $secretKey = "fooKRAKENbar"; // TODO: load from config or env
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

    public function exportData($signedCredential, $type, $id)
    {

        // - POST credential it to $exporterUrl/upload (as file or POST parameter?)
        // SimpleHttpClient::request(); → add parameter for data and for HTTP header?
    }

    private function initAuthHeader(string $secretKey)
    {
        ClockProvider::$EPOCH = 0; // per default this is set to a weird magic number ...

        $ser = new TimedSerializer($secretKey);
        $token = $ser->dumps("KRAKEN");

        $this->logger->info("Authz Token: " . $token);

        return ['auth_bearer' => $token];
    }
}
