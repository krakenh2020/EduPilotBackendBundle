<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use VC4SM\Bundle\Entity\CourseGrade;
use VC4SM\Bundle\Service\CourseGradeProviderInterface;

class CourseGradeDataPersister implements DataPersisterInterface
{
    private $api;

    public function __construct(CourseGradeProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports($data): bool
    {
        return $data instanceof CourseGrade;
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
