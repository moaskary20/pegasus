<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Models\Course;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة تصنيف')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        return view('filament.resources.categories.header', [
            'totalCategories' => Category::count(),
            'activeCategories' => Category::where('is_active', true)->count(),
            'parentCategories' => Category::whereNull('parent_id')->count(),
            'subCategories' => Category::whereNotNull('parent_id')->count(),
            'coursesWithCategory' => Course::whereNotNull('category_id')->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
