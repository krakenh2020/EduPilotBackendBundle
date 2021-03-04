<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\DidConnection;

class DidExternalApi implements DidConnectionProviderInterface
{
    private $didConnections;

    public function __construct()
    {
        // DidConnections
        $this->DidConnections = [];
        $DidConnection1 = new DidConnection();
        $DidConnection1->setIdentifier('graz');
        $DidConnection1->setName('Graz');

        $DidConnection2 = new DidConnection();
        $DidConnection2->setIdentifier('vienna');
        $DidConnection2->setName('Vienna');

        $this->DidConnections[] = $DidConnection1;
        $this->DidConnections[] = $DidConnection2;
    }

    public function getDidConnectionById(string $identifier): ?DidConnection
    {
        foreach ($this->didConnections as $DidConnection) {
            if ($DidConnection->getIdentifier() === $identifier) {
                return $DidConnection;
            }
        }

        return null;
    }

    public function getDidConnections(): array
    {
        return $this->didConnections;
    }
}
