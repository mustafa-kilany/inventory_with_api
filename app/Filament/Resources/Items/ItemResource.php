<?php

namespace App\Filament\Resources\Items;

use App\Filament\Resources\Items\Pages\ManageItems;
use App\Models\Item;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Section::make('Category & Stock')
                    ->schema([
                        Select::make('category')
                            ->options([
                                'Office Supplies' => 'Office Supplies',
                                'Computer Hardware' => 'Computer Hardware',
                                'Furniture' => 'Furniture',
                                'Cleaning Supplies' => 'Cleaning Supplies',
                                'Safety Equipment' => 'Safety Equipment',
                                'Tools' => 'Tools',
                                'Other' => 'Other',
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpan(1),
                        TextInput::make('unit')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., pieces, kg, liters')
                            ->columnSpan(1),
                        TextInput::make('quantity_on_hand')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                        TextInput::make('reorder_level')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ])->columns(2),
                
                Section::make('Pricing & Supplier')
                    ->schema([
                        TextInput::make('unit_price')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(1),
                        TextInput::make('supplier')
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('location')
                            ->maxLength(255)
                            ->placeholder('e.g., Warehouse A-1')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_on_hand')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')),
                TextColumn::make('unit')
                    ->searchable(),
                TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('supplier')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Office Supplies' => 'Office Supplies',
                        'Computer Hardware' => 'Computer Hardware',
                        'Furniture' => 'Furniture',
                        'Cleaning Supplies' => 'Cleaning Supplies',
                        'Safety Equipment' => 'Safety Equipment',
                        'Tools' => 'Tools',
                        'Other' => 'Other',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageItems::route('/'),
        ];
    }
}