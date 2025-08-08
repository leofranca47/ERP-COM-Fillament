<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'state',
        'ibge_code',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
