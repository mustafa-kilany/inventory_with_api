<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'item_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
        ];
    }

    // Boot method to generate transaction number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->transaction_number)) {
                $prefix = strtoupper(substr($model->type, 0, 2));
                $model->transaction_number = $prefix . '-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
            
            if (empty($model->transaction_date)) {
                $model->transaction_date = now();
            }
        });
    }

    // Business logic methods
    public function isStockIn(): bool
    {
        return $this->type === 'in';
    }

    public function isStockOut(): bool
    {
        return $this->type === 'out';
    }

    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
    }

    public static function createStockIn(Item $item, int $quantity, User $performer, string $referenceType = null, int $referenceId = null, string $notes = null): self
    {
        $quantityBefore = $item->quantity_on_hand;
        $quantityAfter = $quantityBefore + $quantity;

        $transaction = self::create([
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'performed_by' => $performer->id,
        ]);

        $item->updateStock($quantity, 'add');

        return $transaction;
    }

    public static function createStockOut(Item $item, int $quantity, User $performer, string $referenceType = null, int $referenceId = null, string $notes = null): self
    {
        $quantityBefore = $item->quantity_on_hand;
        $quantityAfter = max(0, $quantityBefore - $quantity);
        $actualQuantity = $quantityBefore - $quantityAfter; // Actual quantity removed (can't go negative)

        $transaction = self::create([
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => -$actualQuantity, // Negative for stock out
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'performed_by' => $performer->id,
        ]);

        $item->updateStock($actualQuantity, 'subtract');

        return $transaction;
    }

    public static function createAdjustment(Item $item, int $newQuantity, User $performer, string $notes = null): self
    {
        $quantityBefore = $item->quantity_on_hand;
        $adjustment = $newQuantity - $quantityBefore;

        $transaction = self::create([
            'item_id' => $item->id,
            'type' => 'adjustment',
            'quantity' => $adjustment,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'reference_type' => 'manual_adjustment',
            'notes' => $notes,
            'performed_by' => $performer->id,
        ]);

        $item->quantity_on_hand = $newQuantity;
        $item->save();

        return $transaction;
    }

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeByReference($query, $referenceType, $referenceId = null)
    {
        $query = $query->where('reference_type', $referenceType);
        
        if ($referenceId) {
            $query = $query->where('reference_id', $referenceId);
        }
        
        return $query;
    }
}
