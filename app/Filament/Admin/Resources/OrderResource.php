<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PaymentIntentStatusEnum;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Filament\Admin\Resources\OrderResource\Actions;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $navigationLabel = 'Todas las ventas';
    protected static ?string $modelLabel = 'venta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folio')
                    ->searchable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn ($record) => PaymentIntentStatusEnum::from($record->status)->label())
                    ->color(fn ($record) => PaymentIntentStatusEnum::from($record->status)->color())
                    ->icon(fn ($record) => PaymentIntentStatusEnum::from($record->status)->icon()),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->getStateUsing(fn ($record) => $record->amount / 100)
                    ->numeric()
                    ->money('MXN')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de compra')
                    ->dateTime('d/m/Y, h:i a')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.reference_code')
                    ->label('No. Cliente')
                    ->tooltip(fn ($record) => $record?->customer?->user?->name ?? "No encontrado")
                    ->url(fn($record) => route('filament.admin.resources.customers.edit', $record))
                    ->icon('heroicon-o-user')
            ])
            ->filters([
            ])
            ->actions([
                ViewAction::make(),
                Actions\TicketAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
