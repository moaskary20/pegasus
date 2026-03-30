<?php

namespace App\Filament\Resources\Assignments;

use App\Filament\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Resources\Assignments\Pages\ViewAssignment;
use App\Filament\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Resources\Assignments\Tables\AssignmentsTable;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationLabel = 'الواجبات والمشاريع';
    
    protected static ?string $modelLabel = 'واجب';
    
    protected static ?string $pluralModelLabel = 'الواجبات والمشاريع';

    protected static ?int $navigationSort = 11;
    
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole('instructor') && ! auth()->user()?->hasRole('admin')) {
            $query->whereHas('course', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return AssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'view' => ViewAssignment::route('/{record}'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user?->hasAnyRole(['admin', 'instructor'])) {
            return null;
        }

        $query = AssignmentSubmission::query()->where('status', AssignmentSubmission::STATUS_SUBMITTED);

        if ($user->hasRole('instructor') && ! $user->hasRole('admin')) {
            $query->whereHas('assignment.course', fn ($q) => $q->where('user_id', $user->id));
        }

        $pending = $query->count();

        return $pending > 0 ? (string) $pending : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
