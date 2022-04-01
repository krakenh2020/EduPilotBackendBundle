<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\CourseGrade;
use VC4SM\Bundle\Entity\DidConnection;
use VC4SM\Bundle\Entity\Diploma;

class ExternalApi implements DiplomaProviderInterface, CourseGradeProviderInterface
{
    private $diplomas;
    private $courseGrades;

    public function __construct()
    {
        // Test Data for KRAKEN Evaluation:
        // (replace with api.tugraz.at DataProvider/DataPersister when/if ready)

        // diplomas
        $this->diplomas = [];

        $diploma1 = new Diploma();
        $diploma1->setIdentifier('bsc1');
        $diploma1->setName('Bachelorstudium Elektrotechnik-Toningenieur');
        $diploma1->setStudyName('Bachelorstudium Elektrotechnik-Toningenieur');
        $diploma1->setAchievenmentDate('2015-01-01');
        $diploma1->setAcademicDegree('Bachelor of Science (BSc)');

        $diploma2 = new Diploma();
        $diploma2->setIdentifier('bed1');
        $diploma2->setName('Unterrichtsfach Darstellende Geometrie');
        $diploma2->setStudyName('Unterrichtsfach Darstellende Geometrie');
        $diploma2->setAchievenmentDate('2018-01-01');
        $diploma2->setAcademicDegree('Bachelor of Education (BEd)');

        $this->diplomas[] = $diploma1;
        $this->diplomas[] = $diploma2;

        // courseGrades
        $this->courseGrades = [];

        $grade1 = new CourseGrade();
        $grade1->setIdentifier('os');
        $grade1->setName('Operating Systems');
        $grade1->setCourseTitle('Operating Systems');
        $grade1->setAchievenmentDate('2017-01-01');
        $grade1->setGrade('1');
        $grade1->setCredits('5');

        $grade2 = new CourseGrade();
        $grade2->setIdentifier('hcivc');
        $grade2->setName('Human-Computer Interaction and Visual Computing');
        $grade2->setCourseTitle('Human-Computer Interaction and Visual Computing');
        $grade2->setAchievenmentDate('2017-02-01');
        $grade2->setGrade('2');
        $grade2->setCredits('4');

        $grade3 = new CourseGrade();
        $grade3->setIdentifier('dmds');
        $grade3->setName('Data Management and Data Science');
        $grade3->setCourseTitle('Data Management and Data Science');
        $grade3->setAchievenmentDate('2017-03-01');
        $grade3->setGrade('3');
        $grade3->setCredits('3');

        $grade4 = new CourseGrade();
        $grade4->setIdentifier('tcs');
        $grade4->setName('Theoretical Computer Science');
        $grade4->setCourseTitle('Theoretical Computer Science');
        $grade4->setAchievenmentDate('2017-04-01');
        $grade4->setGrade('4');
        $grade4->setCredits('2');

        $this->courseGrades[] = $grade1;
        $this->courseGrades[] = $grade2;
        $this->courseGrades[] = $grade3;
        $this->courseGrades[] = $grade4;
    }

    public function getDiplomaById(string $identifier): ?Diploma
    {
        foreach ($this->getDiplomas() as $diploma) {
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
        foreach ($this->getCourseGrades() as $courseGrade) {
            if ($courseGrade->getIdentifier() === $identifier) {
                return $courseGrade;
            }
        }

        return null;
    }

    public function getCourseGrades(): array
    {
        return $this->courseGrades;
    }
}
