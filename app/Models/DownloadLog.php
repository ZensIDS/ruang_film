<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = ['file', 'ip_address', 'user_agent', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
