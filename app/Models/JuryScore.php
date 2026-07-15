<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuryScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'film_id',
        'jury_id',
        'score',
        'note',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function film()
    {
        return $this->belongsTo(Film::class);
    }

    public function jury()
    {
        return $this->belongsTo(User::class, 'jury_id');
    }
}
