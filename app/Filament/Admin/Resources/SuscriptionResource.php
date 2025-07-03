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
                Forms\Components\Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la suscripción')
                                    ->placeholder('Escribe un nombre de la suscripción')
                                    ->required()
                                    ->unique('suscriptions', 'name', ignoreRecord:true),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Costo de la suscripción')
                                    ->placeholder('Ingresa el precio de está suscripción')
                                    ->numeric()
                                    ->suffix('MX')
                                    ->prefix('$')
                                    ->numeric()
                                    ->minValue(0)
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                                    ->dehydrateStateUsing(fn ($state) => $state * 100)                                    
                                    ->required()
                                    ->helperText('Agrega aquí el costo anual de esta suscripción'),
                                Forms\Components\Checkbox::make('free')
                                    ->label('Marcar si la suscripción es gratuita'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Características')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Repeater::make('attributes')
                                    ->label('Caracteristicas de la suscripción')
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->required()
                                    ->grid(2)
                                    ->addActionLabel('Agrega otra carácteristica')
                                    ->simple(
                                        Forms\Components\TextInput::make('attribute')
                                            ->hiddenLabel()
                                            ->placeholder('Escribe una caracteristica')
                                    ),
                            ]),
                        Forms\Components\Tabs\Tab::make('Beneficios')
                            ->icon('heroicon-o-gift')
                            ->schema([
                                Forms\Components\Repeater::make('benefits')
                                    ->label('Beneficios de la suscripción')
                                    ->hiddenLabel()
                                    ->columnSpanFull()
                                    ->required()
                                    ->grid(2)
                                    ->addActionLabel('Agrega otro beneficio')
                                    ->simple(
                                        Forms\Components\TextInput::make('attribute')
                                            ->hiddenLabel()
                                            ->placeholder('Escribe una caracteristica')
                                    ),
                                ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Suscripción')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Anualidad')
                    ->money('MXN', 100, 'es_MX')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\IconColumn::make('free')
                    ->label('Es gratuita?')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->alignCenter()
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ultima actualización')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
