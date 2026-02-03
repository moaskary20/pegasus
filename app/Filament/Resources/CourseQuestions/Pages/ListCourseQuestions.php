<?php

namespace App\Filament\Resources\CourseQuestions\Pages;

use App\Filament\Resources\CourseQuestions\CourseQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseQuestions extends ListRecords
{
    protected static string $resource = CourseQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
