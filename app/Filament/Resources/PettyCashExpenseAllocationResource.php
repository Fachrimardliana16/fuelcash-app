<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PettyCashExpenseAllocationResource\Pages;
use App\Filament\Resources\PettyCashExpenseAllocationResource\RelationManagers;
use App\Models\PettyCashExpenseAllocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PettyCashExpenseAllocationResource extends Resource
{
    protected static ?string $model = PettyCashExpenseAllocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('petty_cash_expense_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('petty_cash_deposit_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('petty_cash_expense_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('petty_cash_deposit_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPettyCashExpenseAllocations::route('/'),
            'create' => Pages\CreatePettyCashExpenseAllocation::route('/create'),
            'edit' => Pages\EditPettyCashExpenseAllocation::route('/{record}/edit'),
        ];
    }
}
