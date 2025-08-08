<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
    ];

    public function subgroups(): HasMany
    {
        return $this->hasMany(ProductSubgroup::class);
    }
}
