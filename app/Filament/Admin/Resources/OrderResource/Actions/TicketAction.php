<?php

namespace App\Filament\Admin\Resources\OrderResource\Actions;

use Filament\Tables\Actions\Action;

class TicketAction extends Action { 

    public static function make(string|null $name = 'download-ticket'): static {
        return parent::make($name)
            ->label("Descargar Ticket")
            ->hiddenLabel()
            ->icon('heroicon-o-ticket')->iconButton()
            ->tooltip('Descargar Comprobante')
            ->openUrlInNewTab()
            ->url(fn ($record) => route('download.ticket', ['folio'=>$record->folio]));
    }

}