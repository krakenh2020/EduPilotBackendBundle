<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use VC4SM\Bundle\Entity\CourseGrade;
use VC4SM\Bundle\Service\CourseGradeProviderInterface;

final class CourseGradeItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $api;

    public function __construct(CourseGradeProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CourseGrade::class === $resourceClass;
    }

    /**
     * @param array|int|string $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?CourseGrade
    {
        return $this->api->getCourseGradeById($id);
    }
}
