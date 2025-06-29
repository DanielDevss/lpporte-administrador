<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuscriptionResource\Pages;
use App\Filament\Admin\Resources\SuscriptionResource\RelationManagers;
use App\Models\Suscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuscriptionResource extends Resource
{
    protected static ?string $model = Suscription::class;
    protected static ?string $navigationGroup = 'Ajustes';
    protected static ?string $navigationLabel = 'Suscripciones';
    protected static ?string $modelLabel = 'suscripción';
    protected static ?string $pluralModelLabel = 'suscripciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la suscripción')
                    ->placeholder('Escribe un nombre de la suscripción')
                    ->required()
                    ->unique('suscriptions', 'name'),
                Forms\Components\TextInput::make('amount')
                    ->label('Costo de la suscripción')
                    ->placeholder('Ingresa el precio de está suscripción')
                    ->numeric()
                    ->required()
                    ->helperText('Agrega aquí el costo anual de esta membresía'),
                Forms\Components\Checkbox::make('free')
                    ->label('Marcar si la suscripción es gratuita'),
                Forms\Components\Repeater::make('attributes')
                    ->label('Caracteristicas de la suscripción')
                    ->columnSpanFull()
                    ->required()
                    ->grid(3)
                    ->addActionLabel('Agrega otra carácteristica')
                    ->simple(Forms\Components\TextInput::make('attribute')
                        ->hiddenLabel()
                        ->placeholder('Escribe una caracteristica')
                    )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSuscriptions::route('/'),
            'create' => Pages\CreateSuscription::route('/create'),
            'edit' => Pages\EditSuscription::route('/{record}/edit'),
        ];
    }
}
