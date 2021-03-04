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
        $this->didConnections = [];
        $didConnection1 = new DidConnection();
        $didConnection1->setIdentifier('graz');
        $didConnection1->setName('Graz');

        $didConnection2 = new DidConnection();
        $didConnection2->setIdentifier('vienna');
        $didConnection2->setName('Vienna');

        $this->didConnections[] = $didConnection1;
        $this->didConnections[] = $didConnection2;
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
