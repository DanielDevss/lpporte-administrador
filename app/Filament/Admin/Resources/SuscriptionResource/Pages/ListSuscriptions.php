<?php

namespace App\Filament\Admin\Resources\SuscriptionResource\Pages;

use App\Filament\Admin\Resources\SuscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuscriptions extends ListRecords
{
    protected static string $resource = SuscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
