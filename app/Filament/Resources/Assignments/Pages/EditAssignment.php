<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Course;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if ($user?->hasRole('instructor') && ! $user?->hasRole('admin')) {
            $course = Course::find($data['course_id'] ?? null);
            if (! $course || (int) $course->user_id !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'course_id' => 'لا يمكنك ربط الواجب بدورة لا تملكها.',
                ]);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
