<?php

namespace App\Filament\Resources\Certificates;

use App\Filament\Resources\Certificates\Pages\CreateCertificate;
use App\Filament\Resources\Certificates\Pages\EditCertificate;
use App\Filament\Resources\Certificates\Pages\ListCertificates;
use App\Filament\Resources\Certificates\Pages\ViewCertificate;
use App\Filament\Resources\Certificates\Schemas\CertificateForm;
use App\Filament\Resources\Certificates\Tables\CertificatesTable;
use App\Models\Certificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationLabel = 'الشهادات';
    
    protected static ?string $modelLabel = 'شهادة';
    
    protected static ?string $pluralModelLabel = 'الشهادات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected static ?int $navigationSort = 10;
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CertificateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CertificatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificates::route('/'),
            'create' => CreateCertificate::route('/create'),
            'view' => ViewCertificate::route('/{record}'),
            'edit' => EditCertificate::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is instructor (not admin), show only certificates for their courses
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->whereHas('course', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        return $query;
    }
    
    public static function canView($record): bool
    {
        $user = auth()->user();
        if ($user?->hasRole('admin')) {
            return true;
        }
        if ($user?->hasRole('instructor')) {
            return $record->course->user_id === $user->id;
        }
        if ($user?->hasRole('student')) {
            return $record->user_id === $user->id;
        }
        return false;
    }
}
