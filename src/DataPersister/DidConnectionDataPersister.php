<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Service\DidConnectionProviderInterface;

class DidConnectionDataPersister implements DataPersisterInterface
{
    private $api;

    public function __construct(DidConnectionProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports($data): bool
    {
        return $data instanceof DidConnection;
    }

    public function persist($data)
    {
        // TODO
    }

    public function remove($data)
    {
        // TODO
    }
}
