<?php

namespace VC4SM\Bundle\Controller;

use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Service\DidConnectionProviderInterface;

class AcceptRequest
{
    private $api;

    public function __construct(DidConnectionProviderInterface $api)
    {
        $this->api = $api;
    }

    public function __invoke(Credential $data): ?Credential
    {
        return $this->api->acceptRequest($data);
    }
}
