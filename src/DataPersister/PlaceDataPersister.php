<?php

declare(strict_types=1);

namespace DBP\API\StarterBundle\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use DBP\API\StarterBundle\Entity\Place;
use DBP\API\StarterBundle\Service\PlaceProviderInterface;

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
