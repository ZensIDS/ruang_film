<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'film_id',
        'reviewer_id',
        'review_rubric_id',
        'stage',
        'total_score',
        'note',
        'submitted_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function film()
    {
        return $this->belongsTo(Film::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function rubric()
    {
        return $this->belongsTo(ReviewRubric::class, 'review_rubric_id');
    }

    public function scores()
    {
        return $this->hasMany(SubmissionReviewScore::class);
    }

    public function scopeStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }
}
