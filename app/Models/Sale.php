<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'sale_date',
        'person_id',
        'customer_name',
        'observations',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    // Generate a unique order number
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', now())->latest()->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Calculate total amount based on items
    public function calculateTotal(): float
    {
        return $this->items->sum('total_price');
    }

    // Update the total amount
    public function updateTotal(): void
    {
        $this->update([
            'total_amount' => $this->calculateTotal(),
        ]);
    }

    // Helper method to get the client name (either from person or customer_name)
    public function getClientNameAttribute(): string
    {
        if ($this->person) {
            return $this->person->name;
        }

        return $this->customer_name ?? 'Cliente não identificado';
    }
}
