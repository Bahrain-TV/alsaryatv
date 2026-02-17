<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'youtube_url',
        'youtube_id',
        'is_live_stream',
        'is_enabled',
        'scheduled_at',
        'expires_at',
        'sort_order',
    ];

    protected $casts = [
        'is_live_stream' => 'boolean',
        'is_enabled' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function ($video) {
            // Extract YouTube ID from URL if not set
            if (! $video->youtube_id && $video->youtube_url) {
                $video->youtube_id = static::extractYoutubeId($video->youtube_url);
            }
        });
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at && now()->isBefore($this->scheduled_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    public function isActive(): bool
    {
        return $this->is_enabled &&
               ! $this->isExpired() &&
               (! $this->scheduled_at || now()->isAfter($this->scheduled_at));
    }

    public function getEmbedUrlAttribute(): string
    {
        if (! $this->youtube_id) {
            return '';
        }

        $baseUrl = 'https://www.youtube.com/embed/'.$this->youtube_id;
        $params = [];

        if ($this->is_live_stream) {
            $params[] = 'autoplay=1';
            $params[] = 'mute=1';
        }

        return $baseUrl.(count($params) > 0 ? '?'.implode('&', $params) : '');
    }

    public static function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function getActiveVideos()
    {
        return static::where('is_enabled', true)
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
