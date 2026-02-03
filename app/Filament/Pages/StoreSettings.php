<?php

namespace App\Filament\Pages;

use App\Models\GovernorateShippingRate;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class StoreSettings extends Page
{
    protected static ?string $navigationLabel = 'الإعدادات';
    
    protected static ?string $title = 'إعدادات المتجر';
    
    protected static ?int $navigationSort = 5;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.store-settings';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public string $activeTab = 'general';
    
    // General Settings
    public string $storeName = '';
    public string $storeDescription = '';
    public string $storeEmail = '';
    public string $storePhone = '';
    public string $storeAddress = '';
    public string $currency = 'EGP';
    public string $currencySymbol = 'ج.م';
    
    // Shipping Settings
    public bool $enableShipping = true;
    public string $defaultShippingCost = '50';
    public string $freeShippingThreshold = '500';
    public string $shippingCalculation = 'flat';
    
    // Tax Settings
    public bool $enableTax = false;
    public string $taxRate = '14';
    public bool $taxIncludedInPrice = true;
    
    // Order Settings
    public string $orderPrefix = 'ORD-';
    public bool $autoConfirmOrders = false;
    public bool $allowGuestCheckout = true;
    public string $minOrderAmount = '0';
    
    // Inventory Settings
    public bool $trackInventory = true;
    public bool $lowStockNotification = true;
    public bool $outOfStockVisibility = true;
    public bool $allowBackorders = false;
    
    // Shipping Zone Form
    public bool $showZoneForm = false;
    public ?int $editingZoneId = null;
    public string $zoneName = '';
    public string $zoneCities = '';
    public bool $zoneIsActive = true;
    
    // Shipping Method Form
    public bool $showMethodForm = false;
    public ?int $editingMethodId = null;
    public ?int $methodZoneId = null;
    public string $methodName = '';
    public string $methodDescription = '';
    public string $methodType = 'flat';
    public string $methodCost = '0';
    public string $methodMinForFree = '';
    public string $methodEstDaysMin = '';
    public string $methodEstDaysMax = '';
    public bool $methodIsActive = true;
    
    // Governorate Shipping
    public string $filterRegion = 'all';
    public ?int $editingGovernorateId = null;
    public bool $showGovernorateForm = false;
    public string $govShippingCost = '';
    public string $govFreeThreshold = '';
    public string $govEstDaysMin = '';
    public string $govEstDaysMax = '';
    public bool $govIsActive = true;
    public bool $govCashOnDelivery = true;
    public string $govNotes = '';
    
    public function mount(): void
    {
        $this->loadSettings();
    }
    
    public function loadSettings(): void
    {
        $this->storeName = StoreSetting::getValue('store_name', '');
        $this->storeDescription = StoreSetting::getValue('store_description', '');
        $this->storeEmail = StoreSetting::getValue('store_email', '');
        $this->storePhone = StoreSetting::getValue('store_phone', '');
        $this->storeAddress = StoreSetting::getValue('store_address', '');
        $this->currency = StoreSetting::getValue('currency', 'EGP');
        $this->currencySymbol = StoreSetting::getValue('currency_symbol', 'ج.م');
        
        $this->enableShipping = StoreSetting::getValue('enable_shipping', true);
        $this->defaultShippingCost = (string) StoreSetting::getValue('default_shipping_cost', 50);
        $this->freeShippingThreshold = (string) StoreSetting::getValue('free_shipping_threshold', 500);
        $this->shippingCalculation = StoreSetting::getValue('shipping_calculation', 'flat');
        
        $this->enableTax = StoreSetting::getValue('enable_tax', false);
        $this->taxRate = (string) StoreSetting::getValue('tax_rate', 14);
        $this->taxIncludedInPrice = StoreSetting::getValue('tax_included_in_price', true);
        
        $this->orderPrefix = StoreSetting::getValue('order_prefix', 'ORD-');
        $this->autoConfirmOrders = StoreSetting::getValue('auto_confirm_orders', false);
        $this->allowGuestCheckout = StoreSetting::getValue('allow_guest_checkout', true);
        $this->minOrderAmount = (string) StoreSetting::getValue('min_order_amount', 0);
        
        $this->trackInventory = StoreSetting::getValue('track_inventory', true);
        $this->lowStockNotification = StoreSetting::getValue('low_stock_notification', true);
        $this->outOfStockVisibility = StoreSetting::getValue('out_of_stock_visibility', true);
        $this->allowBackorders = StoreSetting::getValue('allow_backorders', false);
    }
    
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function saveGeneralSettings(): void
    {
        StoreSetting::setValue('store_name', $this->storeName);
        StoreSetting::setValue('store_description', $this->storeDescription);
        StoreSetting::setValue('store_email', $this->storeEmail);
        StoreSetting::setValue('store_phone', $this->storePhone);
        StoreSetting::setValue('store_address', $this->storeAddress);
        StoreSetting::setValue('currency', $this->currency);
        StoreSetting::setValue('currency_symbol', $this->currencySymbol);
        
        StoreSetting::clearCache();
        session()->flash('success', 'تم حفظ الإعدادات العامة بنجاح');
    }
    
    public function saveShippingSettings(): void
    {
        StoreSetting::setValue('enable_shipping', $this->enableShipping);
        StoreSetting::setValue('default_shipping_cost', $this->defaultShippingCost);
        StoreSetting::setValue('free_shipping_threshold', $this->freeShippingThreshold);
        StoreSetting::setValue('shipping_calculation', $this->shippingCalculation);
        
        StoreSetting::clearCache();
        session()->flash('success', 'تم حفظ إعدادات الشحن بنجاح');
    }
    
    public function saveTaxSettings(): void
    {
        StoreSetting::setValue('enable_tax', $this->enableTax);
        StoreSetting::setValue('tax_rate', $this->taxRate);
        StoreSetting::setValue('tax_included_in_price', $this->taxIncludedInPrice);
        
        StoreSetting::clearCache();
        session()->flash('success', 'تم حفظ إعدادات الضرائب بنجاح');
    }
    
    public function saveOrderSettings(): void
    {
        StoreSetting::setValue('order_prefix', $this->orderPrefix);
        StoreSetting::setValue('auto_confirm_orders', $this->autoConfirmOrders);
        StoreSetting::setValue('allow_guest_checkout', $this->allowGuestCheckout);
        StoreSetting::setValue('min_order_amount', $this->minOrderAmount);
        
        StoreSetting::clearCache();
        session()->flash('success', 'تم حفظ إعدادات الطلبات بنجاح');
    }
    
    public function saveInventorySettings(): void
    {
        StoreSetting::setValue('track_inventory', $this->trackInventory);
        StoreSetting::setValue('low_stock_notification', $this->lowStockNotification);
        StoreSetting::setValue('out_of_stock_visibility', $this->outOfStockVisibility);
        StoreSetting::setValue('allow_backorders', $this->allowBackorders);
        
        StoreSetting::clearCache();
        session()->flash('success', 'تم حفظ إعدادات المخزون بنجاح');
    }
    
    public function getShippingZonesProperty()
    {
        return ShippingZone::with('methods')->orderBy('name')->get();
    }
    
    public function getShippingMethodsProperty()
    {
        return ShippingMethod::with('zone')->orderBy('sort_order')->get();
    }
    
    // Shipping Zone CRUD
    public function openZoneForm(?int $id = null): void
    {
        $this->resetZoneForm();
        if ($id) {
            $zone = ShippingZone::find($id);
            $this->editingZoneId = $zone->id;
            $this->zoneName = $zone->name;
            $this->zoneCities = $zone->cities ? implode(', ', $zone->cities) : '';
            $this->zoneIsActive = $zone->is_active;
        }
        $this->showZoneForm = true;
    }
    
    public function resetZoneForm(): void
    {
        $this->showZoneForm = false;
        $this->editingZoneId = null;
        $this->zoneName = '';
        $this->zoneCities = '';
        $this->zoneIsActive = true;
    }
    
    public function saveZone(): void
    {
        $this->validate(['zoneName' => 'required'], ['zoneName.required' => 'اسم المنطقة مطلوب']);
        
        $cities = $this->zoneCities ? array_map('trim', explode(',', $this->zoneCities)) : null;
        
        $data = [
            'name' => $this->zoneName,
            'cities' => $cities,
            'is_active' => $this->zoneIsActive,
        ];
        
        if ($this->editingZoneId) {
            ShippingZone::find($this->editingZoneId)->update($data);
        } else {
            ShippingZone::create($data);
        }
        
        $this->resetZoneForm();
        session()->flash('success', 'تم حفظ منطقة الشحن بنجاح');
    }
    
    public function deleteZone(int $id): void
    {
        ShippingZone::find($id)?->delete();
        session()->flash('success', 'تم حذف منطقة الشحن');
    }
    
    // Shipping Method CRUD
    public function openMethodForm(?int $zoneId = null, ?int $id = null): void
    {
        $this->resetMethodForm();
        $this->methodZoneId = $zoneId;
        
        if ($id) {
            $method = ShippingMethod::find($id);
            $this->editingMethodId = $method->id;
            $this->methodZoneId = $method->zone_id;
            $this->methodName = $method->name;
            $this->methodDescription = $method->description ?? '';
            $this->methodType = $method->type;
            $this->methodCost = (string) $method->cost;
            $this->methodMinForFree = $method->minimum_order_for_free ? (string) $method->minimum_order_for_free : '';
            $this->methodEstDaysMin = $method->estimated_days_min ? (string) $method->estimated_days_min : '';
            $this->methodEstDaysMax = $method->estimated_days_max ? (string) $method->estimated_days_max : '';
            $this->methodIsActive = $method->is_active;
        }
        $this->showMethodForm = true;
    }
    
    public function resetMethodForm(): void
    {
        $this->showMethodForm = false;
        $this->editingMethodId = null;
        $this->methodZoneId = null;
        $this->methodName = '';
        $this->methodDescription = '';
        $this->methodType = 'flat';
        $this->methodCost = '0';
        $this->methodMinForFree = '';
        $this->methodEstDaysMin = '';
        $this->methodEstDaysMax = '';
        $this->methodIsActive = true;
    }
    
    public function saveMethod(): void
    {
        $this->validate(['methodName' => 'required'], ['methodName.required' => 'اسم طريقة الشحن مطلوب']);
        
        $data = [
            'zone_id' => $this->methodZoneId,
            'name' => $this->methodName,
            'description' => $this->methodDescription ?: null,
            'type' => $this->methodType,
            'cost' => (float) $this->methodCost,
            'minimum_order_for_free' => $this->methodMinForFree ? (float) $this->methodMinForFree : null,
            'estimated_days_min' => $this->methodEstDaysMin ? (int) $this->methodEstDaysMin : null,
            'estimated_days_max' => $this->methodEstDaysMax ? (int) $this->methodEstDaysMax : null,
            'is_active' => $this->methodIsActive,
        ];
        
        if ($this->editingMethodId) {
            ShippingMethod::find($this->editingMethodId)->update($data);
        } else {
            ShippingMethod::create($data);
        }
        
        $this->resetMethodForm();
        session()->flash('success', 'تم حفظ طريقة الشحن بنجاح');
    }
    
    public function deleteMethod(int $id): void
    {
        ShippingMethod::find($id)?->delete();
        session()->flash('success', 'تم حذف طريقة الشحن');
    }
    
    // Governorate Shipping Methods
    public function getGovernoratesProperty()
    {
        return GovernorateShippingRate::orderBy('sort_order')
            ->when($this->filterRegion !== 'all', fn ($q) => $q->where('region', $this->filterRegion))
            ->get();
    }
    
    public function getRegionsProperty(): array
    {
        return GovernorateShippingRate::getRegions();
    }
    
    public function editGovernorate(int $id): void
    {
        $this->resetGovernorateForm();
        $gov = GovernorateShippingRate::find($id);
        if ($gov) {
            $this->editingGovernorateId = $gov->id;
            $this->govShippingCost = (string) $gov->shipping_cost;
            $this->govFreeThreshold = $gov->free_shipping_threshold ? (string) $gov->free_shipping_threshold : '';
            $this->govEstDaysMin = $gov->estimated_days_min ? (string) $gov->estimated_days_min : '';
            $this->govEstDaysMax = $gov->estimated_days_max ? (string) $gov->estimated_days_max : '';
            $this->govIsActive = $gov->is_active;
            $this->govCashOnDelivery = $gov->cash_on_delivery;
            $this->govNotes = $gov->notes ?? '';
            $this->showGovernorateForm = true;
        }
    }
    
    public function resetGovernorateForm(): void
    {
        $this->showGovernorateForm = false;
        $this->editingGovernorateId = null;
        $this->govShippingCost = '';
        $this->govFreeThreshold = '';
        $this->govEstDaysMin = '';
        $this->govEstDaysMax = '';
        $this->govIsActive = true;
        $this->govCashOnDelivery = true;
        $this->govNotes = '';
    }
    
    public function saveGovernorate(): void
    {
        $this->validate([
            'govShippingCost' => 'required|numeric|min:0',
        ], [
            'govShippingCost.required' => 'تكلفة الشحن مطلوبة',
        ]);
        
        $gov = GovernorateShippingRate::find($this->editingGovernorateId);
        if ($gov) {
            $gov->update([
                'shipping_cost' => (float) $this->govShippingCost,
                'free_shipping_threshold' => $this->govFreeThreshold ? (float) $this->govFreeThreshold : null,
                'estimated_days_min' => $this->govEstDaysMin ? (int) $this->govEstDaysMin : null,
                'estimated_days_max' => $this->govEstDaysMax ? (int) $this->govEstDaysMax : null,
                'is_active' => $this->govIsActive,
                'cash_on_delivery' => $this->govCashOnDelivery,
                'notes' => $this->govNotes ?: null,
            ]);
            session()->flash('success', 'تم تحديث تكلفة الشحن للمحافظة بنجاح');
        }
        $this->resetGovernorateForm();
    }
    
    public function toggleGovernorateStatus(int $id): void
    {
        $gov = GovernorateShippingRate::find($id);
        if ($gov) {
            $gov->update(['is_active' => !$gov->is_active]);
        }
    }
    
    public function updateAllGovernorates(string $field, string $value): void
    {
        if (!in_array($field, ['shipping_cost', 'free_shipping_threshold', 'cash_on_delivery'])) {
            return;
        }
        
        $data = [];
        
        if ($field === 'cash_on_delivery') {
            $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else {
            $data[$field] = (float) $value ?: null;
        }
        
        $query = GovernorateShippingRate::query();
        if ($this->filterRegion !== 'all') {
            $query->where('region', $this->filterRegion);
        }
        
        $query->update($data);
        session()->flash('success', 'تم تحديث جميع المحافظات');
    }
}
