<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Place;

interface PlaceProviderInterface
{
    public function getPlaceById(string $identifier): ?Place;

    public function getPlaces(): array;
}
