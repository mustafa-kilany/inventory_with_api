<?php

namespace App\Filament\Resources\StockTransactions;

use App\Filament\Resources\StockTransactions\Pages\ManageStockTransactions;
use App\Models\StockTransaction;
use App\Models\Item;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;

class StockTransactionResource extends Resource
{
    protected static ?string $model = StockTransaction::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
    protected static ?string $recordTitleAttribute = 'transaction_number';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventory Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Details')
                    ->schema([
                        TextInput::make('transaction_number')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Select::make('item_id')
                            ->label('Item')
                            ->relationship('item', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        Select::make('type')
                            ->options([
                                'in' => 'Stock In',
                                'out' => 'Stock Out',
                                'adjustment' => 'Adjustment',
                            ])
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->label('Quantity')
                            ->helperText('Positive for stock in, negative for stock out')
                            ->columnSpan(1),
                    ])->columns(2),
                
                Section::make('Stock Levels')
                    ->schema([
                        TextInput::make('quantity_before')
                            ->required()
                            ->numeric()
                            ->label('Quantity Before')
                            ->columnSpan(1),
                        TextInput::make('quantity_after')
                            ->required()
                            ->numeric()
                            ->label('Quantity After')
                            ->columnSpan(1),
                    ])->columns(2),
                
                Section::make('Reference & Notes')
                    ->schema([
                        Select::make('reference_type')
                            ->options([
                                'purchase_request' => 'Purchase Request',
                                'procurement' => 'Procurement',
                                'manual' => 'Manual Adjustment',
                                'return' => 'Return',
                                'damage' => 'Damage',
                                'other' => 'Other',
                            ])
                            ->columnSpan(1),
                        TextInput::make('reference_id')
                            ->numeric()
                            ->label('Reference ID')
                            ->columnSpan(1),
                        Select::make('performed_by')
                            ->label('Performed By')
                            ->relationship('performedBy', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        DateTimePicker::make('transaction_date')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_number')
            ->columns([
                TextColumn::make('transaction_number')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('item.name')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                        'warning' => 'adjustment',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'adjustment' => 'Adjustment',
                    }),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->type === 'out' ? "-{$state}" : "+{$state}"
                    ),
                TextColumn::make('quantity_before')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity_after')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('performedBy.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'in' => 'Stock In',
                        'out' => 'Stock Out',
                        'adjustment' => 'Adjustment',
                    ]),
                SelectFilter::make('reference_type')
                    ->options([
                        'purchase_request' => 'Purchase Request',
                        'procurement' => 'Procurement',
                        'manual' => 'Manual Adjustment',
                        'return' => 'Return',
                        'damage' => 'Damage',
                        'other' => 'Other',
                    ]),
                DateFilter::make('transaction_date'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockTransactions::route('/'),
        ];
    }
}
