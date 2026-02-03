<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Models\AssignmentComment;
use App\Models\AssignmentSubmission;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ViewAssignment extends Page
{
    use WithFileUploads;
    
    protected static string $resource = AssignmentResource::class;
    
    protected string $view = 'filament.resources.assignments.view';
    
    public Assignment $record;
    
    public string $activeTab = 'submissions';
    
    public ?int $selectedSubmissionId = null;
    
    public ?int $gradeScore = null;
    public string $gradeFeedback = '';
    public string $newComment = '';
    
    public function mount(int|string $record): void
    {
        $this->record = Assignment::with(['course', 'lesson', 'submissions.user', 'submissions.files'])->findOrFail($record);
    }
    
    public function getTitle(): string
    {
        return $this->record->title;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->record($this->record),
        ];
    }
    
    public function getSubmissionsProperty()
    {
        return $this->record->submissions()
            ->with(['user', 'files', 'comments.user'])
            ->orderByDesc('submitted_at')
            ->get();
    }
    
    public function getSelectedSubmissionProperty()
    {
        if (!$this->selectedSubmissionId) {
            return null;
        }
        
        return AssignmentSubmission::with(['user', 'files', 'comments.user', 'grader'])
            ->find($this->selectedSubmissionId);
    }
    
    public function getStatsProperty(): array
    {
        return $this->record->getSubmissionStats();
    }
    
    public function selectSubmission(int $id): void
    {
        $this->selectedSubmissionId = $id;
        $submission = $this->selectedSubmission;
        $this->gradeScore = $submission?->score;
        $this->gradeFeedback = $submission?->feedback ?? '';
    }
    
    public function gradeSubmission(): void
    {
        $this->validate([
            'gradeScore' => ['required', 'numeric', 'min:0', 'max:' . $this->record->max_score],
        ]);
        
        $submission = AssignmentSubmission::find($this->selectedSubmissionId);
        
        if ($submission) {
            $submission->update([
                'score' => $this->gradeScore,
                'feedback' => $this->gradeFeedback,
                'status' => 'graded',
                'graded_at' => now(),
                'graded_by' => auth()->id(),
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'تم حفظ التقييم بنجاح',
            ]);
        }
    }
    
    public function requestResubmission(): void
    {
        $submission = AssignmentSubmission::find($this->selectedSubmissionId);
        
        if ($submission) {
            $submission->update([
                'status' => 'resubmit_requested',
                'feedback' => $this->gradeFeedback,
            ]);
            
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'تم طلب إعادة التسليم',
            ]);
        }
    }
    
    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'required|min:2',
        ]);
        
        AssignmentComment::create([
            'submission_id' => $this->selectedSubmissionId,
            'user_id' => auth()->id(),
            'content' => $this->newComment,
        ]);
        
        $this->newComment = '';
    }
    
    public function downloadFile(int $fileId): void
    {
        $file = \App\Models\SubmissionFile::find($fileId);
        
        if ($file && Storage::exists($file->file_path)) {
            $this->redirect(Storage::url($file->file_path));
        }
    }
}
