<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use VC4SM\Bundle\Entity\Place;
use VC4SM\Bundle\Service\PlaceProviderInterface;

final class PlaceItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(PlaceProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Place::class === $resourceClass;
    }

    /**
     * @param array|int|string $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Place
    {
        return $this->api->getPlaceById($id);
    }
}
