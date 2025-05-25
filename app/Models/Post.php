<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image_url', 'scheduled_time', 'status', 'user_id'];
    protected $casts = ['scheduled_time' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'post_platform')
            ->withPivot('platform_status')
            ->withTimestamps();
    }

    /**
     * Scope to retrieve posts due for publishing.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_time', '<=', now());
    }
}
