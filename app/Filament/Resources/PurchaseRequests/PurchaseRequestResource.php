<?php

namespace App\Filament\Resources\PurchaseRequests;

use App\Filament\Resources\PurchaseRequests\Pages\ManagePurchaseRequests;
use App\Models\PurchaseRequest;
use App\Models\User;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Information')
                    ->schema([
                        TextInput::make('request_number')
                            ->label('Request Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),
                        Select::make('requested_by')
                            ->label('Requested By')
                            ->relationship('requestedBy', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium')
                            ->columnSpan(1),
                        DatePicker::make('needed_by')
                            ->label('Needed By')
                            ->columnSpan(1),
                    ])->columns(2),
                
                Section::make('Status & Workflow')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'fulfilled' => 'Fulfilled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->columnSpan(1),
                        Select::make('workflow_status')
                            ->label('Workflow Status')
                            ->options([
                                'pending_department_head' => 'Pending Department Head',
                                'pending_manager' => 'Pending Manager',
                                'pending_purchase_department' => 'Pending Purchase Department',
                                'pending_owner' => 'Pending Owner',
                                'pending_purchase_execution' => 'Pending Purchase Execution',
                                'pending_stock_keeper' => 'Pending Stock Keeper',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->columnSpan(1),
                    ])->columns(2),
                
                Section::make('Justification')
                    ->schema([
                        Textarea::make('justification')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Items Requested')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->relationship('item', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                TextInput::make('quantity_requested')
                                    ->label('Quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(1),
                            ])->columns(4)
                            ->addActionLabel('Add Item')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('Request #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'fulfilled',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'fulfilled' => 'Fulfilled',
                        'cancelled' => 'Cancelled',
                        default => $state,
                    }),
                BadgeColumn::make('priority')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                        default => $state,
                    }),
                TextColumn::make('workflow_status')
                    ->label('Workflow')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending_department_head' => 'Dept. Head',
                        'pending_manager' => 'Manager',
                        'pending_purchase_department' => 'Purchase Dept.',
                        'pending_owner' => 'Owner',
                        'pending_purchase_execution' => 'Purchase Exec.',
                        'pending_stock_keeper' => 'Stock Keeper',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $state ?? 'Unknown',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('success'),
                TextColumn::make('estimated_total')
                    ->label('Est. Total')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('needed_by')
                    ->label('Needed By')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : null),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'fulfilled' => 'Fulfilled',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                SelectFilter::make('workflow_status')
                    ->label('Workflow Status')
                    ->options([
                        'pending_department_head' => 'Pending Department Head',
                        'pending_manager' => 'Pending Manager',
                        'pending_purchase_department' => 'Pending Purchase Department',
                        'pending_owner' => 'Pending Owner',
                        'pending_purchase_execution' => 'Pending Purchase Execution',
                        'pending_stock_keeper' => 'Pending Stock Keeper',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                TernaryFilter::make('is_overdue')
                    ->label('Overdue')
                    ->nullable()
                    ->trueLabel('Overdue')
                    ->falseLabel('Not Overdue')
                    ->queries(
                        true: fn ($query) => $query->where('needed_by', '<', now())
                                                   ->whereNotIn('status', ['fulfilled', 'cancelled']),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->where('needed_by', '>=', now())
                              ->orWhereIn('status', ['fulfilled', 'cancelled'])
                              ->orWhereNull('needed_by');
                        }),
                    ),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePurchaseRequests::route('/'),
        ];
    }
}
