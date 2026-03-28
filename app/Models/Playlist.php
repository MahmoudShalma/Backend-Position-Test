<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'playlist_id',
        'title',
        'description',
        'thumbnail',
        'channel_name',
        'category',
        'video_count',
        'total_duration',
        'view_count',
    ];

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getFormattedViewCountAttribute(): string
    {
        $count = $this->view_count;

        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        }

        if ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }

        return (string) $count;
    }

    public function getYoutubeUrlAttribute(): string
    {
        return "https://www.youtube.com/playlist?list={$this->playlist_id}";
    }
}
