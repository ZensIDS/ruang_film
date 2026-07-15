<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRubricItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_rubric_group_id',
        'title',
        'description',
        'weight',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(ReviewRubricGroup::class, 'review_rubric_group_id');
    }
}
