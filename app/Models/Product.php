<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'gross_weight',
        'net_weight',
        'brand',
        'unit_of_measure',
        'product_group_id',
        'product_subgroup_id',
        'image_path',
        'active',
        'sale_price',
        'average_cost',
        'current_cost',
        'minimum_stock',
        'maximum_stock',
    ];

    protected $casts = [
        'gross_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
        'sale_price' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'current_cost' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }

    public function subgroup(): BelongsTo
    {
        return $this->belongsTo(ProductSubgroup::class, 'product_subgroup_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
