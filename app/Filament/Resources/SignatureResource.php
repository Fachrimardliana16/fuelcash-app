<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignatureResource\Pages;
use App\Models\Signature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SignatureResource extends Resource
{
    protected static ?string $model = Signature::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->label('Jabatan')
                    ->placeholder('contoh: Kasubag Umum'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('Keterangan')
                    ->placeholder('contoh: Diperiksa Oleh'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Lengkap'),

                Forms\Components\TextInput::make('nip')
                    ->label('NIP')
                    ->nullable(),

                Forms\Components\Toggle::make('show_stamp')
                    ->label('Tampilkan Stempel')
                    ->default(false),

                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0)
                    ->label('Urutan')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->searchable(),
                Tables\Columns\IconColumn::make('show_stamp')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
            ])
            ->defaultSort('order')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignatures::route('/'),
            'create' => Pages\CreateSignature::route('/create'),
            'edit' => Pages\EditSignature::route('/{record}/edit'),
        ];
    }
}
