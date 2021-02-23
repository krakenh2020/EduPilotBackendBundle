<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

// todo: schema
/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     iri="https://schema.org/Place",
 *     normalizationContext={"groups"={"CourseGrade:output"}, "jsonld_embed_context"=true},
 *     denormalizationContext={"groups"={"CourseGrade:input"}, "jsonld_embed_context"=true}
 * )
 */
class CourseGrade
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $identifier;

    /**
     * @ApiProperty(iri="https://schema.org/name")
     * @Groups({"CourseGrade:output", "CourseGrade:input"})
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
