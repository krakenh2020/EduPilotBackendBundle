<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Diploma;

interface DiplomaProviderInterface
{
    public function getDiplomaById(string $identifier): ?Diploma;

    public function getDiplomas(): array;
}
