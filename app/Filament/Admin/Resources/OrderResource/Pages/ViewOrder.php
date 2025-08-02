<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Enums\PaymentIntentStatusEnum;
use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Nette\Schema\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = "Detalle de venta";
    
    public function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Section::make()
                    ->columns([
                        'xl' => 4,
                        'lg' => 3,
                        'sm' => 2,
                    ])
                    ->schema([
                        TextEntry::make('folio'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->formatStateUsing(
                            fn ($state):string => PaymentIntentStatusEnum::from($state)->label()
                            )
                            ->color(fn($state):string =>PaymentIntentStatusEnum::from($state)->color())
                            ->icon(fn($state):string =>PaymentIntentStatusEnum::from($state)->icon()),
                        TextEntry::make('customer.user.name')
                            ->label('Cliente'),
                        TextEntry::make('created_at')
                            ->label('Fecha de compra')
                            ->dateTime('d/m/Y, h:i a'),
                        TextEntry::make('amount')
                            ->label('Monto vendido')
                            ->money(divideBy:100, currency: 'MXN'),
                        TextEntry::make('tax')
                            ->label('IVA 16%')
                            ->color('danger')
                            ->money(divideBy:100, currency: 'MXN'),
                        TextEntry::make('stripe_commission')
                            ->label('Stripe (3.6% + 3MXN)')
                            ->color('danger')
                            ->state(function ($record) {
                                $amountInPesos = $record->amount / 100;
                                return ($amountInPesos * 0.036) + 3;
                            })
                            ->money(currency: 'MXN'),
                        TextEntry::make('net_profit')
                            ->label('Ganancia Neta')
                            ->state(function ($record) {
                                $amountInPesos = $record->amount / 100;
                                $tax = $amountInPesos * 0.16;
                                $stripeCommission = ($amountInPesos * 0.036) + 3;
                                return $amountInPesos - $tax - $stripeCommission;
                            })
                            ->money(currency: 'MXN')
                            ->color('success')
                            ->weight(\Filament\Support\Enums\FontWeight::Bold),
                        ]),
                RepeatableEntry::make('products')
                    ->label('Productos agregados')
                    ->schema([
                        TextEntry::make('title')->label('Producto')
                            ->hintIcon('heroicon-o-shopping-bag')
                            ->url(fn($record) => route('filament.admin.resources.products.edit', $record)),
                        TextEntry::make('pivot.amount')
                            ->label('Precio')
                            ->money(currency: 'MXN', divideBy: 100)
                            ->inlineLabel()
                            ->color('success'),
                        TextEntry::make('pivot.plan')
                            ->label('Plan')
                            ->badge()
                            ->inlineLabel()
                    ])
                    ->columnSpanFull()
                    ->grid(3),
            ]);
    }

}
