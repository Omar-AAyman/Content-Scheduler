<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'max_content_length'];
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_platform')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_platforms')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
