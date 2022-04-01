<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Exception;
use Symfony\Component\HttpClient\HttpClient;

class SimpleHttpClient
{
    //public static $classLogger = null;

    private $client;

    public function __construct($certVerifyDisabled = false)
    {
        // in case of $certVerifyDisabled:
        //   disabled: https://symfony.com/doc/current/reference/configuration/framework.html#verify-peer
        //   still enabled: https://symfony.com/doc/current/reference/configuration/framework.html#verify-host
        $this->client = HttpClient::create($certVerifyDisabled ? ['verify_peer' => false] : []);
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

        $isLocalhost = parse_url($url, PHP_URL_HOST) === 'localhost';
        $certVerifyDisabled = $isLocalhost == true;

        $c = new SimpleHttpClient($certVerifyDisabled);
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
    
}
