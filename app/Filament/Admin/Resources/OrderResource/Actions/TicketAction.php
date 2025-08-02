<?php

namespace App\Filament\Admin\Resources\OrderResource\Actions;

use Filament\Tables\Actions\Action;

class TicketAction extends Action { 

    public static function make(string|null $name = 'download-ticket'): static {
        return parent::make($name)->label("Descargar Ticket");
    }

}