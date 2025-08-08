<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate total price based on quantity and unit price
    public function calculateTotalPrice(): float
    {
        return $this->quantity * $this->unit_price;
    }

    // Update the total price
    public function updateTotalPrice(): void
    {
        $this->update([
            'total_price' => $this->calculateTotalPrice(),
        ]);
    }

    // Set the unit price from the product if not provided
    public function setUnitPriceFromProduct(): void
    {
        if (!$this->unit_price && $this->product) {
            $this->unit_price = $this->product->sale_price;
            $this->updateTotalPrice();
        }
    }
}
