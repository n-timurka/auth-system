<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'platform_channel_id',
        'upload_playlist_id',
        'user_id',
        'name',
        'description',
        'custom_url',
        'thumbnail_url',
        'statistics',
        'type',
        'published_at',
        'last_synced_at',
    ];

    protected $casts = [
        'statistics' => 'array',
        'published_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
