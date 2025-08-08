<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'created_date',
        'crt',
        'fantasy_name',
        'company_name',
        'cnpj',
        'state_registration',
        'municipal_registration',
        'zip_code',
        'state',
        'city',
        'street',
        'number',
        'neighborhood',
        'complement',
        'phone',
        'email',
    ];

    protected $casts = [
        'created_date' => 'date',
    ];
}
