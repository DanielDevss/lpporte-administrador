<?php

namespace App\Filament\Admin\Resources\SuscriptionResource\Pages;

use App\Filament\Admin\Resources\SuscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuscription extends EditRecord
{
    protected static string $resource = SuscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
