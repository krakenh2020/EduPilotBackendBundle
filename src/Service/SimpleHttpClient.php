<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

class SimpleHttpClient
{
    // TODO: migrate to something more stable
    // like https://symfony.com/doc/current/http_client.html
    // → $client = HttpClient::create();

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
            $body = false;
        }

        if ($body === false) {
            DidExternalApi::$classLogger->warning("Error while connecting to $url");

            return [
                'contents' => '',
                'status_code' => -1,
            ];
        }

        return [
            'contents' => $body,
            'status_code' => SimpleHttpClient::getHttpCode($http_response_header),
        ];
    }
}
