<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_fulfilled',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Business logic methods
    public function isFullyFulfilled(): bool
    {
        return $this->quantity_fulfilled >= ($this->quantity_approved ?? $this->quantity_requested);
    }

    public function isPartiallyFulfilled(): bool
    {
        return $this->quantity_fulfilled > 0 && !$this->isFullyFulfilled();
    }

    public function getRemainingQuantity(): int
    {
        $target = $this->quantity_approved ?? $this->quantity_requested;
        return max(0, $target - $this->quantity_fulfilled);
    }

    public function fulfill(int $quantity): void
    {
        $maxQuantity = ($this->quantity_approved ?? $this->quantity_requested) - $this->quantity_fulfilled;
        $actualQuantity = min($quantity, $maxQuantity);
        
        $this->quantity_fulfilled += $actualQuantity;
        $this->save();
    }

    public function calculateTotalPrice(): float
    {
        $quantity = $this->quantity_approved ?? $this->quantity_requested;
        return $quantity * ($this->unit_price ?? 0);
    }

    public function calculateActualTotalPrice(): float
    {
        return $this->quantity_fulfilled * ($this->unit_price ?? 0);
    }

    // Relationships
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes
    public function scopeFullyFulfilled($query)
    {
        return $query->whereRaw('quantity_fulfilled >= COALESCE(quantity_approved, quantity_requested)');
    }

    public function scopePartiallyFulfilled($query)
    {
        return $query->where('quantity_fulfilled', '>', 0)
                    ->whereRaw('quantity_fulfilled < COALESCE(quantity_approved, quantity_requested)');
    }

    public function scopeUnfulfilled($query)
    {
        return $query->where('quantity_fulfilled', 0);
    }
}
