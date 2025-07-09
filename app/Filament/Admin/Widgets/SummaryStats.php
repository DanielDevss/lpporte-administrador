<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SummaryStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make("Clientes Activos", "45")
                ->icon('heroicon-m-user')
                ->url(route('filament.admin.resources.categories.index'))
                ->description('Presiona para ir a clientes'),
            Stat::make("Clientes Suscritos", "22")
                ->icon('heroicon-m-user-plus')
                ->url('filament.admin.resources.categories.index')
                ->description('Presiona para ir a clientes'),
            Stat::make("Productos Publicos", value: "32")
                ->icon('heroicon-m-shopping-bag')
                ->url(route('filament.admin.resources.products.index'))
                ->description('Presiona para ir a productos'),
        ];
    }
}
