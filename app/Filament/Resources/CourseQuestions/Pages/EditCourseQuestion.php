<?php

namespace App\Filament\Resources\CourseQuestions\Pages;

use App\Filament\Resources\CourseQuestions\CourseQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseQuestion extends EditRecord
{
    protected static string $resource = CourseQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
