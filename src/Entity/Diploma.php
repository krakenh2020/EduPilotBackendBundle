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
 *     iri="https://schema.org/EducationalOccupationalCredential",
 *     normalizationContext={"groups"={"Diploma:output"}, "jsonld_embed_context"=true},
 *     denormalizationContext={"groups"={"Diploma:input"}, "jsonld_embed_context"=true}
 * )
 */
class Diploma
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $identifier;

    /**
     * @ApiProperty(iri="https://schema.org/name")
     * @Groups({"Diploma:output", "Diploma:input"})
     *
     * @var string
     */
    private $name;

    /**
     * @ApiProperty(iri="https://schema.org/EducationalOccupationalProgram")
     * @Groups({"Diploma:output", "Diploma:input"})
     *
     * @var string
     */
    private $studyName;

    /**
     * @ApiProperty(iri="https://schema.org/dateCreated")
     * @Groups({"Diploma:output", "Diploma:input"})
     *
     * @var string
     */
    private $achievenmentDate;

    /**
     * @ApiProperty(iri="https://schema.org/credentialCategory")
     * @Groups({"Diploma:output", "Diploma:input"})
     *
     * @var string
     */
    private $academicDegree;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStudyName(): string
    {
        return $this->studyName;
    }

    public function setStudyName(string $studyName): void
    {
        $this->studyName = $studyName;
    }

    public function getAchievenmentDate(): string
    {
        return $this->achievenmentDate;
    }

    public function setAchievenmentDate(string $achievenmentDate): void
    {
        $this->achievenmentDate = $achievenmentDate;
    }

    public function getAcademicDegree(): string
    {
        return $this->academicDegree;
    }

    public function setAcademicDegree(string $academicDegree): void
    {
        $this->academicDegree = $academicDegree;
    }
}
