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
            'unit_price'   => 'decimal:2',
            'total_price'  => 'decimal:2',
            'quantity_requested'  => 'integer',
            'quantity_approved'   => 'integer',
            'quantity_fulfilled'  => 'integer',
        ];
    }

    /**
     * Keep some convenient computed values on the model JSON.
     */
    protected $appends = [
        'effective_quantity',      // quantity used to price the line (approved if set, else requested)
        'estimated_line_total',    // effective_quantity * unit_price
        'actual_line_total',       // quantity_fulfilled * unit_price
        'remaining_quantity',      // max(effective - fulfilled, 0)
        'is_fully_fulfilled',
        'is_partially_fulfilled',
    ];

    /**
     * Auto-sync total_price on create/update so lists / exports are consistent.
     */
    protected static function booted(): void
    {
        static::saving(function (self $line) {
            // normalize nulls to 0
            $line->quantity_requested = (int) ($line->quantity_requested ?? 0);
            $line->quantity_approved  = $line->quantity_approved !== null ? (int) $line->quantity_approved : null;
            $line->quantity_fulfilled = (int) ($line->quantity_fulfilled ?? 0);
            $line->unit_price         = (float) ($line->unit_price ?? 0);

            $effectiveQty = $line->quantity_approved ?? $line->quantity_requested;
            $line->total_price = round($effectiveQty * $line->unit_price, 2);
        });
    }

    // ------------------------
    // Accessors / Computed
    // ------------------------

    public function getEffectiveQuantityAttribute(): int
    {
        return (int) ($this->quantity_approved ?? $this->quantity_requested);
    }

    public function getEstimatedLineTotalAttribute(): float
    {
        return round($this->effective_quantity * (float) $this->unit_price, 2);
    }

    public function getActualLineTotalAttribute(): float
    {
        return round(((int) $this->quantity_fulfilled) * (float) $this->unit_price, 2);
    }

    public function getRemainingQuantityAttribute(): int
    {
        $remaining = $this->effective_quantity - (int) $this->quantity_fulfilled;
        return max(0, (int) $remaining);
    }

    public function getIsFullyFulfilledAttribute(): bool
    {
        return $this->quantity_fulfilled >= $this->effective_quantity;
    }

    public function getIsPartiallyFulfilledAttribute(): bool
    {
        return $this->quantity_fulfilled > 0 && !$this->is_fully_fulfilled;
    }

    // ------------------------
    // Business logic helpers
    // ------------------------

    /** Mark approval quantity (defaults to requested if null). */
    public function approve(?int $quantity = null): void
    {
        $qty = $quantity ?? (int) $this->quantity_requested;
        $this->quantity_approved = max(0, (int) $qty);
        $this->save();
    }

    /** Fulfill some quantity (caps at remaining). */
    public function fulfill(int $quantity): void
    {
        $remaining = $this->remaining_quantity;
        $toApply   = max(0, min((int) $quantity, $remaining));

        if ($toApply > 0) {
            $this->quantity_fulfilled = ((int) $this->quantity_fulfilled) + $toApply;
            $this->save();
        }
    }

    /** Recalculate total using current unit price and effective qty (idempotent). */
    public function recalcTotals(): void
    {
        $this->total_price = round($this->effective_quantity * (float) $this->unit_price, 2);
        $this->save();
    }

    // ------------------------
    // Relationships
    // ------------------------

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // ------------------------
    // Scopes
    // ------------------------

    public function scopeFullyFulfilled($query)
    {
        return $query->whereRaw('COALESCE(quantity_fulfilled,0) >= COALESCE(quantity_approved, quantity_requested)');
    }

    public function scopePartiallyFulfilled($query)
    {
        return $query
            ->where('quantity_fulfilled', '>', 0)
            ->whereRaw('COALESCE(quantity_fulfilled,0) < COALESCE(quantity_approved, quantity_requested)');
    }

    public function scopeUnfulfilled($query)
    {
        return $query->whereRaw('COALESCE(quantity_fulfilled,0) = 0');
    }

    public function scopeOfRequest($query, int $purchaseRequestId)
    {
        return $query->where('purchase_request_id', $purchaseRequestId);
    }
}
