<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'community_name',
        'provinsi_code',
        'provinsi_name',
        'kabupaten_code',
        'kabupaten_name',
        'kecamatan_code',
        'kecamatan_name',
        'desa_code',
        'desa_name',
        'username_ig',
        'posisi',
        'alamat_lengkap',
        'tanggal_lahir',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAddressAttribute()
    {
        return collect([
            $this->alamat_lengkap,
            $this->desa_name,
            $this->kecamatan_name,
            $this->kabupaten_name,
            $this->provinsi_name,
        ])->filter()->implode(', ');
    }
}
