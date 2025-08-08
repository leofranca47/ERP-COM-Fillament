<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'person_id',
        'type',
        'zip_code',
        'city_id',
        'state',
        'street',
        'number',
        'neighborhood',
        'complement',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
