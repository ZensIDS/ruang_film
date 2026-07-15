<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'no_hp',
        'role',
        'category_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function films()
    {
        return $this->hasMany(Film::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function juryScores()
    {
        return $this->hasMany(JuryScore::class, 'jury_id');
    }

    public function submissionReviews()
    {
        return $this->hasMany(SubmissionReview::class, 'reviewer_id');
    }

    public function normalizedRole()
    {
        return Str::lower(trim((string) $this->role));
    }

    public function hasRole(...$roles)
    {
        $normalizedRole = $this->normalizedRole();

        $allowedRoles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return Str::lower(trim((string) $role));
            })
            ->filter()
            ->values();

        return $allowedRoles->contains($normalizedRole);
    }

    public function ownsUserId($userId)
    {
        $currentUserId = trim((string) $this->getAuthIdentifier());
        $ownerUserId = trim((string) $userId);

        return $currentUserId !== ''
            && $ownerUserId !== ''
            && $currentUserId === $ownerUserId;
    }

    public function isGeneralBuyer()
    {
        return $this->hasRole('umum');
    }
}
