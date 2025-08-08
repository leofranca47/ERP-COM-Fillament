<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'barcode',
        'grade_x',
        'grade_y',
        'stock_location',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Helper method to get the full product name with variants
    public function getFullProductNameAttribute(): string
    {
        $name = $this->product->description;

        if ($this->grade_x) {
            $name .= ' ' . $this->grade_x;
        }

        if ($this->grade_y) {
            $name .= ' ' . $this->grade_y;
        }

        return $name;
    }
}
