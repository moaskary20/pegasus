<x-filament-panels::page>
    <div class="mb-4">
        <a href="{{ \App\Filament\Pages\ShoppingCart::getUrl() }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            <x-filament::icon icon="heroicon-o-shopping-cart" class="h-5 w-5" />
            السلة ({{ count(session('cart', [])) }})
        </a>
    </div>
    
    {{ $this->table }}
</x-filament-panels::page>
