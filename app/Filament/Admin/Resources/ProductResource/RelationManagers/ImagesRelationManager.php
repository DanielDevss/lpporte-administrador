<?php

namespace App\Filament\Admin\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';
    protected static ?string $title = "Imagenes extra";
    protected static ?string $modelLabel = 'imagen de producto';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('Imagen extra')
                    ->helperText('Está imágen aparecerá cuando abran el producto en el sitio web.')
                    ->visibility('public')
                    ->directory('products')
                    ->imageEditor()
                    ->imageEditorAspectRatios(['4:5' => '4:5'])
                    ->imageCropAspectRatio('4:5')
                    ->imageResizeTargetWidth(520)
                    ->imageResizeTargetHeight(650)
                    ->downloadable()
                    ->openable()
                    ->required(),
                Forms\Components\TextInput::make('alt')
                    ->label('Titulo de la imagen')
                    ->placeholder('Escribe un titulo descriptivo de la imagen')
                    ->maxLength(45)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('path')
                        ->height('100%')
                        ->width('100%'),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('alt')
                    ])
                ])->space(3),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
                '2xl' => 4
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
