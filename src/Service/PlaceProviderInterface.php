<?php

declare(strict_types=1);

namespace DBP\API\StarterBundle\Service;

use DBP\API\StarterBundle\Entity\Place;

interface PlaceProviderInterface
{
    public function getPlaceById(string $identifier): ?Place;

    public function getPlaces(): array;
}
