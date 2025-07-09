<?php

namespace App\Filament\Admin\Resources\DistribuitorResource\Pages;

use App\Filament\Admin\Resources\DistribuitorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDistribuitors extends ManageRecords
{
    protected static string $resource = DistribuitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Agregar distribuidor'),
        ];
    }
}
