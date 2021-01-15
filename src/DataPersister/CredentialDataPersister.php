<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Service\CredentialProviderInterface;

class CredentialDataPersister implements DataPersisterInterface
{
    private $api;

    public function __construct(CredentialProviderInterface $api)
    {
        $this->api = $api;
    }

    public function supports($data): bool
    {
        return $data instanceof Credential;
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
