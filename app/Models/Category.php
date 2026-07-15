<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function films()
    {
        return $this->hasMany(Film::class);
    }

    public function rubrics()
    {
        return $this->hasMany(ReviewRubric::class);
    }

    public function activeRubric($stage)
    {
        return $this->rubrics()
            ->active()
            ->forStage($stage)
            ->with('groups.items')
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getResolvedSummaryAttribute()
    {
        return $this->landing_summary ?: $this->description ?: 'Kategori kompetisi film Festival Film Horor.';
    }

    public function getResolvedDetailRouteAttribute()
    {
        return $this->detail_route ?: '#';
    }

    public function getImageUrlAttribute()
    {
        return PublicMedia::url($this->image, 'landing/images/user.png');
    }
}
