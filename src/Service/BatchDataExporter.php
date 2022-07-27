<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use ItsDangerous\Signer\TimedSerializer;

class BatchDataExporter
{
    private $logger;
    private $exporterUrl;

    public function __construct($logger, $exporterUrl)
    {
        $logger->info("Initializing BatchDataExporter for exporter at $exporterUrl ...");
        $this->logger = $logger;
        $this->exporterUrl = $exporterUrl;

        $this->checkConnection();
        $this->logger->info('BatchDataExporter initialized!');
    }

    public function checkConnection(): bool
    {
        $url = $this->exporterUrl;

        try {
            $res = SimpleHttpClient::request($url);
        } catch (Exception $exception) {
            return false;
        }

        if ($res['status_code'] !== 200) {
            $this->logger->warning("Checked connection to $url, status code: " . $res['status_code']);

            return false;
        }

        return true;
    }

    public function exportData($signedCredential, $type, $id)
    {
        // - receive API TOKEN, via bundle config?
        $TOKEN_SECRET_KEY = "foobar";
        $TOKEN_HEADER_NAME = "X-KRAKEN-TOKEN";

        // - build auth token using itsdangerous
        $ser = new TimedSerializer($TOKEN_SECRET_KEY);
        $token = $ser->dumps("KRAKEN");
        echo $token;

        // - POST credential it to $exporterUrl/upload (as file or POST parameter?)
        // SimpleHttpClient::request(); → add parameter for data and for HTTP header?
    }
}
