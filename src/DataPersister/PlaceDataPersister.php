<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use VC4SM\Bundle\Entity\Place;
use VC4SM\Bundle\Service\PlaceProviderInterface;

class PlaceDataPersister implements DataPersisterInterface
{
    private $api;

    public function __construct(PlaceProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports($data): bool
    {
        return $data instanceof Place;
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
