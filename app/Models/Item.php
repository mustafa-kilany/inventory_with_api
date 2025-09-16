<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'unit',
        'quantity_on_hand',
        'reorder_level',
        'unit_price',
        'supplier',
        'location',
        'is_active',
        'wikidata_qid',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Business logic methods
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity_on_hand <= 0;
    }

    public function updateStock(int $quantity, string $operation = 'add'): void
    {
        if ($operation === 'add') {
            $this->quantity_on_hand += $quantity;
        } else {
            $this->quantity_on_hand -= $quantity;
        }
        
        // Ensure stock doesn't go negative
        if ($this->quantity_on_hand < 0) {
            $this->quantity_on_hand = 0;
        }
        
        $this->save();
    }

    public function canFulfill(int $requestedQuantity): bool
    {
        return $this->quantity_on_hand >= $requestedQuantity;
    }

    // Relationships
    public function purchaseRequestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_on_hand <= reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity_on_hand', '<=', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
