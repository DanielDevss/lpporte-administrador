<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
                        Forms\Components\Tabs\Tab::make('Imagenes extra')
                            ->icon('heroicon-m-photo')

                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('titulo')
                    ->searchable()
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
