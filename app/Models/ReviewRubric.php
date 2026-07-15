<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRubric extends Model
{
    use HasFactory;

    public const STAGE_CURATION = 'curation';
    public const STAGE_JURY = 'jury';

    protected $fillable = [
        'category_id',
        'stage',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function stages()
    {
        return [
            static::STAGE_CURATION,
            static::STAGE_JURY,
        ];
    }

    public static function stageLabels()
    {
        return [
            static::STAGE_CURATION => 'Kurasi',
            static::STAGE_JURY => 'Penjurian',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function groups()
    {
        return $this->hasMany(ReviewRubricGroup::class)->orderBy('sort_order')->orderBy('id');
    }

    public function items()
    {
        return $this->hasManyThrough(ReviewRubricItem::class, ReviewRubricGroup::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }
}
