<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\DidConnection;

interface DidConnectionProviderInterface
{
    public function getDidConnectionById(string $identifier): ?DidConnection;

    public function getDidConnections(): array;

    public function getCredentialById(string $identifier): ?Credential;

    public function sendOffer(Credential $data): ?Credential;

    public function provideCredenitalToBatchExporter(Credential $data): ?Credential;

    public function acceptRequest(Credential $data): ?Credential;
}
