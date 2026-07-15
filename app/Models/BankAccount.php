<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'rek_name',
        'rek_bank_name',
        'rek_bank_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
