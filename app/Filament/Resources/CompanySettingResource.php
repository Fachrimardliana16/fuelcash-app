<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanySettingResource\Pages;
use App\Models\CompanySetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CompanySettingResource extends Resource
{
    protected static ?string $model = CompanySetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Government Information')
                    ->schema([
                        Forms\Components\TextInput::make('government_name')
                            ->label('Nama Pemerintah Daerah')
                            ->required()
                            ->placeholder('contoh: PEMERINTAH KABUPATEN PURBALINGGA')
                            ->maxLength(255),
                    ])->columns(1),

                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\Textarea::make('company_name')
                            ->label('Nama Perusahaan')
                            ->required()
                            ->placeholder('contoh: PERUMDA AIR MINUM TIRTA PERWIRA KABUPATEN PURBALINGGA')
                            ->autosize()
                            ->rows(2)
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('company_logo')
                            ->image()
                            ->disk('public')
                            ->directory('company-logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->imageEditorViewportWidth('1920')
                            ->imageEditorViewportHeight('1080')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/jpg'
                            ])
                            ->maxSize(10240)
                            ->optimize('jpg'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\TextInput::make('street_address'),
                        Forms\Components\TextInput::make('village'),
                        Forms\Components\TextInput::make('district'),
                        Forms\Components\TextInput::make('regency'),
                        Forms\Components\TextInput::make('province'),
                        Forms\Components\TextInput::make('postal_code'),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number'),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\TextInput::make('website')
                            ->url(),
                        Forms\Components\TextInput::make('tax_number'),
                    ])->columns(2),

                Forms\Components\Section::make('Social Media')
                    ->schema([
                        Forms\Components\TextInput::make('facebook')->url(),
                        Forms\Components\TextInput::make('instagram')->url(),
                        Forms\Components\TextInput::make('twitter')->url(),
                        Forms\Components\TextInput::make('youtube')->url(),
                        Forms\Components\TextInput::make('linkedin')->url(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('company_logo'),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanySettings::route('/'),
            'create' => Pages\CreateCompanySetting::route('/create'),
            'edit' => Pages\EditCompanySetting::route('/{record}/edit'),
        ];
    }
}
