<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionReviewScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_review_id',
        'review_rubric_item_id',
        'item_title',
        'item_weight',
        'score',
        'weighted_score',
    ];

    protected $casts = [
        'item_weight' => 'decimal:2',
        'score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
    ];

    public function review()
    {
        return $this->belongsTo(SubmissionReview::class, 'submission_review_id');
    }

    public function rubricItem()
    {
        return $this->belongsTo(ReviewRubricItem::class, 'review_rubric_item_id');
    }
}
