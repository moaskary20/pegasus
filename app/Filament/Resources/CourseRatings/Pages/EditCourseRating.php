<?php

namespace App\Filament\Resources\CourseRatings\Pages;

use App\Filament\Resources\CourseRatings\CourseRatingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseRating extends EditRecord
{
    protected static string $resource = CourseRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
