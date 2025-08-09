<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SummaryStats extends BaseWidget
{
    protected function getHeading(): string|null
    {
        return "Estadisiticas de tienda";
    }
    protected function getStats(): array
    {
        return [
            Stat::make("Clientes Activos", Customer::count())
                ->icon('heroicon-m-user')
                ->url(route('filament.admin.resources.categories.index'))
                ->description('Presiona para ir a clientes'),
            Stat::make("Clientes Suscritos", Customer::where('suscription_active', true)->where('suscription_id', '>', '1')->count())
                ->icon('heroicon-m-user-plus')
                ->url(route('filament.admin.resources.customers.index'))
                ->description('Presiona para ir a clientes'),
            Stat::make("Productos Publicos", value: Product::publics()->count())
                ->icon('heroicon-m-shopping-bag')
                ->url(route('filament.admin.resources.products.index'))
                ->description('Presiona para ir a productos'),
            Stat::make(
                "Ventas pendientes",
                value: Order::where(
                    'status',
                    '!=',
                    OrderStatusEnum::Succeeded->value
                )
                    ->where('status', '!=', OrderStatusEnum::Canceled->value)
                    ->count()
            )
                ->icon('heroicon-m-clock')
                ->url(route('filament.admin.resources.orders.index'))
                ->description('Presiona para ir a ventas'),
        ];
    }
}
