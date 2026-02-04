<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\SubmissionFile;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class MyAssignments extends Component
{
    use WithFileUploads;

    public string $activeTab = 'pending';

    public ?int $selectedAssignmentId = null;

    public string $submissionContent = '';

    public $submissionFiles = [];

    public function mount(): void
    {
        // Auth middleware handles unauthenticated users
    }

    public function getEnrolledCourseIdsProperty()
    {
        return Enrollment::where('user_id', auth()->id())->pluck('course_id');
    }

    public function getAssignmentsProperty()
    {
        $query = Assignment::whereIn('course_id', $this->enrolledCourseIds)
            ->where('is_published', true)
            ->with(['course', 'lesson', 'submissions' => fn ($q) => $q->where('user_id', auth()->id())]);

        return $query->orderByDesc('created_at')->get();
    }

    public function getFilteredAssignmentsProperty()
    {
        $assignments = $this->assignments;

        return match ($this->activeTab) {
            'pending' => $assignments->filter(fn ($a) => $a->submissions->isEmpty() || $a->submissions->last()?->status === 'resubmit_requested'),
            'submitted' => $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'submitted'),
            'graded' => $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'graded'),
            default => $assignments,
        };
    }

    public function getSelectedAssignmentProperty()
    {
        if (! $this->selectedAssignmentId) {
            return null;
        }

        return Assignment::with(['course', 'lesson', 'submissions' => fn ($q) => $q->where('user_id', auth()->id())->with('files')])
            ->find($this->selectedAssignmentId);
    }

    public function getStatsProperty(): array
    {
        $assignments = $this->assignments;

        return [
            'total' => $assignments->count(),
            'pending' => $assignments->filter(fn ($a) => $a->submissions->isEmpty() || $a->submissions->last()?->status === 'resubmit_requested')->count(),
            'submitted' => $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'submitted')->count(),
            'graded' => $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'graded')->count(),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->selectedAssignmentId = null;
    }

    public function selectAssignment(int $id): void
    {
        $this->selectedAssignmentId = $id;
        $this->submissionContent = '';
        $this->submissionFiles = [];
    }

    public function submitAssignment(): void
    {
        $assignment = $this->selectedAssignment;

        if (! $assignment || ! $assignment->canSubmit(auth()->user())) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'لا يمكنك تسليم هذا الواجب']);
            return;
        }

        $attemptNumber = $assignment->submissions()->where('user_id', auth()->id())->count() + 1;

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => auth()->id(),
            'content' => $this->submissionContent,
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $assignment->isOverdue(),
            'attempt_number' => $attemptNumber,
        ]);

        foreach ($this->submissionFiles as $file) {
            $path = $file->store('submissions/'.$submission->id, 'public');

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        $this->submissionContent = '';
        $this->submissionFiles = [];
        $this->selectedAssignmentId = null;
        $this->activeTab = 'submitted';

        $this->dispatch('notify', ['type' => 'success', 'message' => 'تم تسليم الواجب بنجاح']);
    }

    public function render(): View
    {
        return view('livewire.my-assignments')
            ->layout('layouts.site', ['title' => 'واجباتي - ' . config('app.name')]);
    }
}
