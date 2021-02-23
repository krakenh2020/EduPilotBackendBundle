<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\Diploma;

class ExternalApi implements CredentialProviderInterface, DiplomaProviderInterface
{
    private $diplomas;
    private $credentials;

    public function __construct()
    {
        // diplomas
        $this->diplomas = [];

        $diploma1 = new Diploma();
        $diploma1->setIdentifier('bscInE');
        $diploma1->setName('Bachelor of Science in Engineering');

        $diploma2 = new Diploma();
        $diploma2->setIdentifier('ba');
        $diploma2->setName('Bachelor of Arts');

        $this->diplomas[] = $diploma1;
        $this->diplomas[] = $diploma2;

        // credentials
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

    public function getDiplomaById(string $identifier): ?Diploma
    {
        foreach ($this->diplomas as $diploma) {
            if ($diploma->getIdentifier() === $identifier) {
                return $diploma;
            }
        }

        return null;
    }

    public function getDiplomas(): array
    {
        return $this->diplomas;
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
