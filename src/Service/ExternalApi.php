<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Credential;

class ExternalApi implements CredentialProviderInterface
{
    private $credentials;

    public function __construct()
    {
        $this->credentials = [];
        $credential1 = new Credential();
        $credential1->setIdentifier('graz');
        $credential1->setName('Graz');

        $credential2 = new Credential();
        $credential2->setIdentifier('vienna');
        $credential2->setName('Vienna');

        $this->credentials[] = $credential1;
        $this->credentials[] = $credential2;
    }

    public function getCredentialById(string $identifier): ?Credential
    {
        foreach ($this->credentials as $credential) {
            if ($credential->getIdentifier() === $identifier) {
                return $credential;
            }
        }

        return null;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }
}
