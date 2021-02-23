<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use DBP\API\CoreBundle\Helpers\ArrayFullPaginator;
use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Service\DidConnectionProviderInterface;

final class DidConnectionCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(DidConnectionProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return DidConnection::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): ArrayFullPaginator
    {
        $perPage = 30;
        $page = 1;

        $filters = $context['filters'] ?? [];
        if (isset($filters['page'])) {
            $page = (int) $filters['page'];
        }
        if (isset($filters['perPage'])) {
            $perPage = (int) $filters['perPage'];
        }

        return new ArrayFullPaginator($this->api->getDidConnections(), $page, $perPage);
    }
}
