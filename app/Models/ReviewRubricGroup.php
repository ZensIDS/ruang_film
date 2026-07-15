<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRubricGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_rubric_id',
        'title',
        'weight',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function rubric()
    {
        return $this->belongsTo(ReviewRubric::class, 'review_rubric_id');
    }

    public function items()
    {
        return $this->hasMany(ReviewRubricItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
