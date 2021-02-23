<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Service;

use VC4SM\Bundle\Entity\CourseGrade;

interface CourseGradeProviderInterface
{
    public function getCourseGradeById(string $identifier): ?CourseGrade;

    public function getCourseGrades(): array;
}
