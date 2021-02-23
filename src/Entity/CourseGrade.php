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
 *     iri="https://schema.org/Thing",
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

    /**
     * @ApiProperty(iri="https://schema.org/Course")
     * @Groups({"CourseGrade:output", "CourseGrade:input"})
     *
     * @var string
     */
    private $courseTitle;

    /**
     * @ApiProperty(iri="https://schema.org/dateCreated")
     * @Groups({"CourseGrade:output", "CourseGrade:input"})
     *
     * @var string
     */
    private $achievenmentDate;


    /**
     * @ApiProperty(iri="https://schema.org/Thing")
     * @Groups({"CourseGrade:output", "CourseGrade:input"})
     *
     * @var string
     */
    private $grade;


    /**
     * @ApiProperty(iri="https://schema.org/Thing")
     * @Groups({"CourseGrade:output", "CourseGrade:input"})
     *
     * @var string
     */
    private $credits;

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

    public function getCourseTitle(): string
    {
        return $this->courseTitle;
    }

    public function setCourseTitle(string $courseTitle): void
    {
        $this->courseTitle = $courseTitle;
    }

    public function getAchievenmentDate(): string
    {
        return $this->achievenmentDate;
    }

    public function setAchievenmentDate(string $achievenmentDate): void
    {
        $this->achievenmentDate = $achievenmentDate;
    }

    public function getGrade(): string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): void
    {
        $this->grade = $grade;
    }

    public function getCredits(): string
    {
        return $this->credits;
    }

    public function setCredits(string $credits): void
    {
        $this->credits = $credits;
    }
}
