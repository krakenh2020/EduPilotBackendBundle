<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Controller;

use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Service\DidConnectionProviderInterface;

class ExportCred
{
    private $api;

    public function __construct(DidConnectionProviderInterface $api)
    {
        $this->api = $api;
    }

    public function __invoke(Credential $data): ?Credential
    {
        return $this->api->provideCredenitalToBatchExporter($data);
    }
}
