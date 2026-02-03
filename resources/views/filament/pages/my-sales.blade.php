<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
    />
    {{-- Sales content will be added here --}}
</x-filament-panels::page>
