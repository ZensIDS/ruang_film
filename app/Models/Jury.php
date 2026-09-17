<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jury extends Model
{
    use HasFactory;

    const TYPE_JURI = 'juri';
    const TYPE_KURATOR = 'kurator';

    public static function types()
    {
        return [
            self::TYPE_JURI    => 'Juri',
            self::TYPE_KURATOR => 'Kurator',
        ];
    }

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeJuri($query)
    {
        return $query->where('type', self::TYPE_JURI);
    }

    public function scopeKurator($query)
    {
        return $query->where('type', self::TYPE_KURATOR);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getIsKuratorAttribute()
    {
        return $this->type === self::TYPE_KURATOR;
    }

    public function getPhotoUrlAttribute()
    {
        return PublicMedia::url($this->photo, 'landing/images/user.png');
    }
}