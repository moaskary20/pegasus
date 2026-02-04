<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة منتج')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.products.header', [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('is_active', true)->count(),
            'lowStock' => Product::lowStock()->count(),
            'outOfStock' => Product::outOfStock()->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
