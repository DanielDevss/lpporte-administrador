<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $modelLabel = 'cliente';
    protected static ?string $navigationGroup = 'Catalogos';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('Nombre del cliente')
                    ->placeholder('Ingrese el nombre del cliente')
                    ->helperText('Este es el nombre del cliente que se mostrará en la plataforma.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user.email')
                    ->label('Correo electrónico')
                    ->placeholder('Ingrese el correo electrónico')
                    ->helperText('Este es el correo electrónico del cliente que se utilizará para iniciar sesión y recibir notificaciones.')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email', ignoreRecord: true),
                Forms\Components\TextInput::make('user.phone')
                    ->label('Teléfono')
                    ->placeholder('Ingrese el número de teléfono')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('suscription_id')
                    ->label('Suscripción')
                    ->relationship('suscription', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Toggle::make('suscription_active')
                    ->label('Suscripción activa')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nombre del cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('No disponible')
                    ->sortable(),
                Tables\Columns\TextColumn::make('suscription.name')
                    ->label('Suscripción')
                    ->searchable()
                    ->icon('heroicon-o-cube')
                    ->iconColor(fn ($record) => $record->suscription_active ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado el')
                    ->dateTime('d/m/Y')
                    ->alignEnd()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(false),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
