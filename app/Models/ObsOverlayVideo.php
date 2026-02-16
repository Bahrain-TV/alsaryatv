<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObsOverlayVideo extends Model
{
    protected $table = 'obs_overlay_videos';

    protected $fillable = [
        'filename',
        'path',
        'file_size',
        'mime_type',
        'recorded_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the public URL for the video.
     */
    public function getPublicUrl(): string
    {
        return url('storage/'.$this->path);
    }

    /**
     * Get the full filesystem path.
     */
    public function getFullPath(): string
    {
        return storage_path('app/public/'.$this->path);
    }

    /**
     * Scope to only ready videos.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Scope to order by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('recorded_at');
    }
}
