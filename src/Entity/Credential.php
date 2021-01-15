<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

// todo: schema
/**
 * @ApiResource(
 *     attributes={"security"="is_granted()"},
 *     collectionOperations={"get"},
 *     itemOperations={"get", "put", "delete"},
 *     iri="https://schema.org/Place",
 *     normalizationContext={"groups"={"Credential:output"}, "jsonld_embed_context"=true},
 *     denormalizationContext={"groups"={"Credential:input"}, "jsonld_embed_context"=true}
 * )
 */
class Credential
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $identifier;

    /**
     * @ApiProperty(iri="https://schema.org/name")
     * @Groups({"Credential:output", "Credential:input"})
     *
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
