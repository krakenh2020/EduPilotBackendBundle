<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Credential;

interface CredentialProviderInterface
{
    public function getCredentialById(string $identifier): ?Credential;

    public function getCredentials(): array;
}
