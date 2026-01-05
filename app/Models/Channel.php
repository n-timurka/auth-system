<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'platform_channel_id',
        'user_id',
        'name',
        'description',
        'custom_url',
        'thumbnail_url',
        'statistics',
        'videos',
        'type',
        'published_at',
        'last_synced_at',
    ];

    protected $casts = [
        'statistics' => 'array',
        'videos' => 'array',
        'published_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
