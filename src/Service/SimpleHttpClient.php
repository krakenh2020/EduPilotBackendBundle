<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use Symfony\Component\HttpClient\HttpClient;

class SimpleHttpClient
{
    //public static $classLogger = null;

    private $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public static function request(string $url, string $method = 'GET', array $data = []): array
    {
        //return self::requestInsecure($url, $method, $data);

        $c = new SimpleHttpClient();
        return $c->requestSymfony($url, $method, $data);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function requestSymfony(string $url, string $method = 'GET', array $data = []): array
    {
        // uses https://symfony.com/doc/current/http_client.html

        if ($data != null) {
            $response = $this->client->request($method, $url, ['json' => $data]);
        } else {
            $response = $this->client->request($method, $url);
        }

        $status = $response->getStatusCode();
        return [
            'status_code' => $status,
            'contents' => $response->getContent(false)
        ];
    }

    public
    static function requestInsecure(string $url, string $method = 'GET', array $data = []): array
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
            return [
                'contents' => "Error while connecting to $url: $e",
                'status_code' => -1,
            ];
        }

        if ($body === false) {
            //self::$classLogger->warning("Error while connecting to $url");

            return [
                'contents' => "Error while connecting to $url",
                'status_code' => -2,
            ];
        }

        return [
            'contents' => $body,
            'status_code' => self::getHttpCode($http_response_header),
        ];
    }

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
}
