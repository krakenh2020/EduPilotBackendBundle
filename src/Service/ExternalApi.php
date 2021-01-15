<?php

declare(strict_types=1);

namespace DBP\API\StarterBundle\Service;

use DBP\API\StarterBundle\Entity\Place;

class ExternalApi implements PlaceProviderInterface
{
    private $places;

    public function __construct()
    {
        $this->places = [];
        $place1 = new Place();
        $place1->setIdentifier('graz');
        $place1->setName('Graz');

        $place2 = new Place();
        $place2->setIdentifier('vienna');
        $place2->setName('Vienna');

        $this->places[] = $place1;
        $this->places[] = $place2;
    }

    public function getPlaceById(string $identifier): ?Place
    {
        foreach ($this->places as $place) {
            if ($place->getIdentifier() === $identifier) {
                return $place;
            }
        }

        return null;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }
}
