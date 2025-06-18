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
    protected static ?string $modelLabel = 'suscripción';
    protected static ?string $pluralModelLabel = 'suscripciones';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la suscripción')
                    ->placeholder('Escribe el nombre de la suscripción')
                    ->unique('suscriptions', 'name', ignoreRecord: true)
                    ->maxLength(100)
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label(fn (callable $get) => $get('free')
                        ? 'Anualidad GRATIS'
                        : 'Precio de la anualidad')
                    ->placeholder('Ingresa el precio de la anualidad')
                    ->maxLength(25)
                    ->numeric()
                    ->required(fn (callable $get) => !$get('free'))
                    ->disabled(fn (callable $get) => $get('free'))
                    ->dehydrated(fn (callable $get) => $get('free')),
                Forms\Components\Checkbox::make('free')
                    ->label('Suscripción gratuita')
                    ->helperText('Marca esta casilla si la suscripción no tiene un costo')
                    ->live(),
                Forms\Components\Repeater::make('attributes')
                    ->label('Caracteristicas y beneficios')
                    ->columnSpanFull()
                    ->grid(2)
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->hiddenLabel()
                            ->placeholder('Escribe una caracteristica o beneficio')
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Anualidad')
                    ->numeric(),
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
