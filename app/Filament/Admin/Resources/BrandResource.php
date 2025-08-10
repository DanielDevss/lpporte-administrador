<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    protected static ?string $navigationGroup = "Catalogos";
    protected static ?string $modelLabel = "marca";

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->label('Nombre de la marca')
                    ->placeholder('Escribe el nombre de la marca')
                    ->required()
                    ->unique('brands', 'name', ignoreRecord: true),
                FileUpload::make('brand')
                    ->label('Logotipo de la marca')
                    ->placeholder('Agrega un logotipo de la marca desde tu galería o arrastralo hasta aquí.')
                    ->image()
                    ->maxSize(1024)
                    ->disk('public')
                    ->directory('brands')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Marca')->searchable(),
                ImageColumn::make('brand')->label('Logotipo')->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Agregado el')
                    ->dateTime('d/m/Y')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Editado el')
                    ->dateTime('d/m/Y')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBrands::route('/'),
        ];
    }
}
