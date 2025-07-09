<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array {
        return [
            'Todos' => Tab::make()
                ->icon('heroicon-m-shopping-bag')
                ->badge(Product::count()),
            'Solo Activos' => Tab::make()
                ->icon('heroicon-m-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->publics())
                ->badge(Product::publics()->count()),
            'Solo Pausados' => Tab::make()
                ->icon('heroicon-m-eye-slash')
                ->modifyQueryUsing(fn (Builder $query) => $query->offPublics())
                ->badge(Product::offPublics()->count()),
        ];
    }
}
