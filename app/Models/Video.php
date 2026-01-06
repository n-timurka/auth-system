<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'channel_id',
        'platform_video_id',
        'title',
        'description',
        'thumbnail_url',
        'published_at',
        'view_count',
        'like_count',
        'comment_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
