<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class StoreReports extends Page
{
    protected static ?string $navigationLabel = 'التقارير';
    
    protected static ?string $title = 'تقارير المتجر';
    
    protected static ?int $navigationSort = 4;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.store-reports';
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المتجر';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public string $activeTab = 'overview';
    public string $period = 'month'; // week, month, year, custom
    public string $startDate = '';
    public string $endDate = '';
    
    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }
    
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function setPeriod(string $period): void
    {
        $this->period = $period;
        
        switch ($period) {
            case 'week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
        }
    }
    
    public function getDateRange(): array
    {
        return [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ];
    }
    
    public function getSalesStatsProperty(): array
    {
        [$start, $end] = $this->getDateRange();
        
        $orders = StoreOrder::whereBetween('created_at', [$start, $end]);
        $paidOrders = (clone $orders)->where('payment_status', 'paid');
        
        return [
            'total_orders' => $orders->count(),
            'paid_orders' => $paidOrders->count(),
            'total_sales' => $paidOrders->sum('total'),
            'average_order' => $paidOrders->avg('total') ?? 0,
            'pending_orders' => (clone $orders)->where('status', 'pending')->count(),
            'shipped_orders' => (clone $orders)->where('status', 'shipped')->count(),
            'delivered_orders' => (clone $orders)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $orders)->where('status', 'cancelled')->count(),
        ];
    }
    
    public function getDailySalesProperty()
    {
        [$start, $end] = $this->getDateRange();
        
        return StoreOrder::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->select(
                DB::raw(\App\Support\DatabaseDateHelper::date() . " as date"),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    public function getTopProductsProperty()
    {
        [$start, $end] = $this->getDateRange();
        
        return StoreOrderItem::join('store_orders', 'store_order_items.store_order_id', '=', 'store_orders.id')
            ->whereBetween('store_orders.created_at', [$start, $end])
            ->where('store_orders.payment_status', 'paid')
            ->select(
                'store_order_items.product_id',
                'store_order_items.product_name',
                DB::raw('SUM(store_order_items.quantity) as total_quantity'),
                DB::raw('SUM(store_order_items.total) as total_revenue')
            )
            ->groupBy('store_order_items.product_id', 'store_order_items.product_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
    }
    
    public function getInventoryStatsProperty(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'total_stock_value' => Product::where('track_quantity', true)
                ->selectRaw('SUM(quantity * COALESCE(cost_price, price)) as value')
                ->value('value') ?? 0,
        ];
    }
    
    public function getLowStockProductsProperty()
    {
        return Product::lowStock()
            ->select('id', 'name', 'sku', 'quantity', 'low_stock_threshold')
            ->orderBy('quantity')
            ->limit(10)
            ->get();
    }
    
    public function getOrdersByStatusProperty()
    {
        [$start, $end] = $this->getDateRange();
        
        return StoreOrder::whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
}
