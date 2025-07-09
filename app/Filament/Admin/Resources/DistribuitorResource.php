<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DistribuitorResource\Pages;
use App\Filament\Admin\Resources\DistribuitorResource\RelationManagers;
use App\Models\Distribuitor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistribuitorResource extends Resource
{
    protected static ?string $model = Distribuitor::class;
    protected static ?string $modelLabel = "distribuidor";
    protected static ?string $pluralLabel = "distribuidores";

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del distribuidor')
                    ->placeholder('Escribe el nombre del distribuidor')
                    ->maxLength(125)
                    ->required(),
                Forms\Components\FileUpload::make('photo')
                    ->label('Fotografía del distribuidor')
                    ->disk('public')
                    ->directory('distribuitors')
                    ->image()
                    ->imageCropAspectRatio('4:5')
                    ->imageEditor()
                    ->imageEditorAspectRatios(['4:5'])
                    ->imageResizeTargetWidth(256)
                    ->imageResizeTargetHeight(320)
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('Número de teléfono')
                    ->placeholder('Escribe el número de teléfono')
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    ->helperText('El número de teléfono es opcional'),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Agrega la dirección del distribuidor')
                    ->maxLength(50)
                    ->helperText('La dirección del distribuidor es opcional'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Distribuidor')->weight(FontWeight::Bold)->searchable(),
                Tables\Columns\ImageColumn::make('photo')->circular()->label('Fotografía')->alignCenter(),
                Tables\Columns\TextColumn::make('address')->placeholder('Sin dirección')->label('Dirección'),
                Tables\Columns\TextColumn::make('phone')->placeholder('Sin número de teléfono')->label('Número de teléfono'),
            ])
            ->filters([
                //
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDistribuitors::route('/'),
        ];
    }
}
