<?php

namespace App\Filament\Resources\CourseQuestions\Pages;

use App\Filament\Resources\CourseQuestions\CourseQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseQuestion extends CreateRecord
{
    protected static string $resource = CourseQuestionResource::class;
}
