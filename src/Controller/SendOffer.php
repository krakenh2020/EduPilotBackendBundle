<?php

namespace VC4SM\Bundle\Controller;

use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Service\DidConnectionProviderInterface;

class SendOffer
{
    private $api;

    public function __construct(DidConnectionProviderInterface $api)
    {
        $this->api = $api;
    }

    public function __invoke(): DidConnection
    {
        return $this->api->sendOffer();
    }
}
