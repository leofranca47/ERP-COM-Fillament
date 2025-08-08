<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'person_id',
        'type',
        'email',
        'phone',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
