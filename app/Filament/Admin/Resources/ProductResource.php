<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ProductStatusEnum;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Filament\Admin\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $modelLabel = 'producto';
    protected static ?string $navigationGroup = 'Catalogos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información principal')
                            ->columns(2)
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->label('Marca')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('categories')
                                    ->label('Categoria')
                                    ->multiple()
                                    ->relationship('categories', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('title')
                                    ->label('Titulo')
                                    ->placeholder('Escribe un titulo al producto')
                                    ->maxLength(120)
                                    ->helperText('Máximo 120 carácteres')
                                    ->required(),
                                Forms\Components\TextInput::make('stock')
                                    ->label('Stock actual')
                                    ->placeholder('Ingresa la cantidad de stock')
                                    ->helperText('El stock es la cantidad que tienes de este producto')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                Forms\Components\FileUpload::make('thumb')
                                    ->label('Miniatura')
                                    ->placeholder('Selecciona una imagen o arrastrala hasta aquí para colocarla como miniatura del producto')
                                    ->image()
                                    ->imageEditor()
                                    ->imageCropAspectRatio('4:5')
                                    ->imageEditorAspectRatios(['4:5'])
                                    ->disk('public')
                                    ->directory('products')
                                    ->required(),
                                Forms\Components\ToggleButtons::make('status')
                                    ->label('¿Públicar producto al guardar?')
                                    ->inline()
                                    ->options([
                                        'activo' => 'Si, públicar',
                                        'pausado' => 'No públicar'
                                    ])
                                    ->icons([
                                        'activo' => 'heroicon-m-eye',
                                        'pausado' => 'heroicon-m-eye-slash',
                                    ])
                                    ->helperText('Si lo públicas aparecerá de inmediato en el sitio web. Si escoges "No", entonces no será visible, hasta que tu lo decidas')
                                    ->required()

                            ]),
                        Forms\Components\Tabs\Tab::make('Ajuste de precios')
                            ->columns(2)
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio unitario')
                                    ->placeholder('Ingresa el precio')
                                    ->helperText('El precio es la cantidad que una persona sin suscripción pagara.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                                    ->dehydrateStateUsing(fn ($state) => $state * 100)
                                    ->required(),
                                Forms\Components\TextInput::make('price_wholesale')
                                    ->label('Precio al por mayor')
                                    ->placeholder('Ingresa el precio')
                                    ->helperText('El precio al por mayor es el precio que se le cobrará a los clientes que compren más de 10 unidades.')
                                    ->numeric() 
                                    ->minValue(0)
                                    ->required()
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                                    ->dehydrateStateUsing(fn ($state) => $state * 100)                                    ,
                                Forms\Components\TextInput::make('price_basic_plan')
                                    ->label('Precio de suscripción básica')
                                    ->placeholder('Ingresa el precio')
                                    ->helperText('Solo a miembros de suscripción básica se les cobrará este precio.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                                    ->dehydrateStateUsing(fn ($state) => $state * 100)
                                    ->required(),
                                Forms\Components\TextInput::make('price_premium_plan')
                                    ->label('Precio de suscripción premium')
                                    ->placeholder('Ingresa el precio')
                                    ->helperText('Solo a miembros de suscripción premium se les cobrará este precio.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                                    ->dehydrateStateUsing(fn ($state) => $state * 100)
                                    ->required(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Descripción')
                            ->columns(1)
                            ->icon('heroicon-m-bars-3-bottom-left')
                            ->schema([
                                Forms\Components\Textarea::make('description_short')
                                    ->label('Descripción corta')
                                    ->placeholder('Ingresa una pequeña descripción del producto')
                                    ->helperText('Recomendable para SEO y descripción del producto al momento de pagar (Máximo 170 carácteres).')
                                    ->maxLength(170),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Descripción y detalles')
                                    ->placeholder('Agrega detalles y descripción de tu producto.')
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                    ])

                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumb')
                    ->label('Miniatura')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->description(fn ($record) => $record?->brand?->name ?? 'Sin marca'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Visibilidad')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        ProductStatusEnum::ACTIVE->value => 'Activo',
                        ProductStatusEnum::PAUSED->value => 'Pausado',
                    })
                    ->icon(fn ($state) => match ($state) {
                        ProductStatusEnum::ACTIVE->value => 'heroicon-m-eye',
                        ProductStatusEnum::PAUSED->value => 'heroicon-m-eye-slash',
                    })
                    ->color(fn ($state) => match ($state) {
                        ProductStatusEnum::ACTIVE->value => 'success',
                        ProductStatusEnum::PAUSED->value => 'warning',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Existencias')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(true),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN', divideBy: 100)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_wholesale')
                    ->label('Mayoreo')
                    ->money('MXN', divideBy: 100)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_basic_plan')
                    ->label('Plan básico')
                    ->money('MXN', divideBy: 100)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_premium_plan')
                    ->label('Plan premium')
                    ->money('MXN', divideBy: 100)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el día')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Editado el día')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
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
            ImagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
