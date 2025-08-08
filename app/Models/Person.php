<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = [
        'registration_type',
        'person_type',
        'birth_date',
        'name',
        'company_name',
        'state_registration',
        'document',
        'status',
        'profile_photo',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'status' => 'string',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
