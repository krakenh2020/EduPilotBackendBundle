<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use VC4SM\Bundle\Entity\Diploma;
use VC4SM\Bundle\Service\DiplomaProviderInterface;

final class DiplomaItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(DiplomaProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Diploma::class === $resourceClass;
    }

    /**
     * @param array|int|string $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Diploma
    {
        return $this->api->getDiplomaById($id);
    }
}
