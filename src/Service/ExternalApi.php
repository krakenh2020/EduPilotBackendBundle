<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\CourseGrade;
use VC4SM\Bundle\Entity\Credential;
use VC4SM\Bundle\Entity\Diploma;

class ExternalApi implements CredentialProviderInterface, DiplomaProviderInterface, CourseGradeProviderInterface
{
    private $diplomas;
    private $courseGrades;
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

        // courseGrades
        $this->courseGrades = [];

        $grade1 = new CourseGrade();
        $grade1->setIdentifier('os');
        $grade1->setName('Operating Systems');

        $grade2 = new CourseGrade();
        $grade2->setIdentifier('hcivc');
        $grade2->setName('Human-Computer Interaction and Visual Computing');

        $grade3 = new CourseGrade();
        $grade3->setIdentifier('dmds');
        $grade3->setName('Data Management and Data Science');

        $grade4 = new CourseGrade();
        $grade4->setIdentifier('tcs');
        $grade4->setName('Theoretical Computer Science');

        $this->courseGrades[] = $grade1;
        $this->courseGrades[] = $grade2;
        $this->courseGrades[] = $grade3;
        $this->courseGrades[] = $grade4;

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

    public function getCourseGradeById(string $identifier): ?CourseGrade
    {
        foreach ($this->courseGrades as $courseGrade) {
            if ($courseGrade->getIdentifier() === $identifier) {
                return $courseGrade;
            }
        }

        return null;
    }

    public function getCourseGrades(): array
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
