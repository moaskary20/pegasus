<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAssignments extends ListRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة واجب'),
        ];
    }
    
    public function getHeader(): ?View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $query = Assignment::query();
        if (!$isAdmin) {
            $query->whereHas('course', fn($q) => $q->where('user_id', $user->id));
        }
        
        $assignmentIds = $query->pluck('id');
        
        return view('filament.resources.assignments.header', [
            'totalAssignments' => $query->count(),
            'totalProjects' => (clone $query)->where('type', 'project')->count(),
            'totalHomework' => (clone $query)->where('type', 'assignment')->count(),
            'totalSubmissions' => AssignmentSubmission::whereIn('assignment_id', $assignmentIds)->count(),
            'pendingGrading' => AssignmentSubmission::whereIn('assignment_id', $assignmentIds)->where('status', 'submitted')->count(),
            'gradedCount' => AssignmentSubmission::whereIn('assignment_id', $assignmentIds)->where('status', 'graded')->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
