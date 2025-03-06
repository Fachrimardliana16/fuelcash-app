<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description('Kelola informasi akun pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255'])
                            ->validationMessages([
                                'required' => 'Nama lengkap wajib diisi',
                                'string' => 'Nama harus berupa teks',
                                'max' => 'Nama tidak boleh lebih dari 255 karakter',
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['required', 'email', 'max:255'])
                            ->validationMessages([
                                'required' => 'Alamat email wajib diisi',
                                'email' => 'Format email tidak valid',
                                'unique' => 'Email sudah terdaftar',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Keamanan')
                    ->description('Atur kata sandi dan verifikasi')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                            ->required(fn($context) => $context === 'create')
                            ->minLength(8)
                            ->rules(['min:8', 'nullable'])
                            ->validationMessages([
                                'min' => 'Kata sandi minimal 8 karakter',
                            ]),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Tanggal Verifikasi Email')
                            ->disabled()
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label('Terverifikasi')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label('Belum Terverifikasi')
                    ->query(fn(Builder $query): Builder => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('created_this_month')
                    ->label('Dibuat Bulan Ini')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),
                    Tables\Actions\Action::make('verify')
                        ->label('Verifikasi Email')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->visible(fn($record) => is_null($record->email_verified_at))
                        ->action(fn($record) => $record->update(['email_verified_at' => now()])),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
