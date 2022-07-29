<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class SimpleHttpClient
{
    //public static $classLogger = null;

    private $client;

    public function __construct($certVerifyDisabled = false, array $httpParams = [])
    {

        $tlsParam = $certVerifyDisabled ? ['verify_peer' => false] : [];
        $params = array_merge($tlsParam, $httpParams);

        // in case of $certVerifyDisabled:
        //   disabled: https://symfony.com/doc/current/reference/configuration/framework.html#verify-peer
        //   still enabled: https://symfony.com/doc/current/reference/configuration/framework.html#verify-host
        $this->client = HttpClient::create($params);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public static function request(string $url, string $method = 'GET', array $data = [], array $httpParams = [], bool $dataAsJson = true): array
    {
        //return self::requestInsecure($url, $method, $data);

        $isLocalhost = parse_url($url, PHP_URL_HOST) === 'localhost';
        $certVerifyDisabled = $isLocalhost === true;

        $c = new SimpleHttpClient($certVerifyDisabled, $httpParams);

        return $c->requestSymfony($url, $method, $data, $dataAsJson);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     */
    public function requestSymfony(string $url, string $method = 'GET', array $data = [], bool $dataAsJson = true): array
    {
        // uses https://symfony.com/doc/current/http_client.html

        if ($data !== null && count($data) > 0) {
            if ($dataAsJson) {
                $dataToSend = ['json' => $data];
            } else {
                // to get content-type multipart/form-data (= file upload)
                $formData = new FormDataPart($data);
                $dataToSend = [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ];
            }
            //echo $method . "ing data: ";
            //print_r($dataToSend);
            $response = $this->client->request($method, $url, $dataToSend);
        } else {
            $response = $this->client->request($method, $url);
        }

        $status = $response->getStatusCode();

        return [
            'status_code' => $status,
            'contents' => $response->getContent(false),
        ];
    }
}
