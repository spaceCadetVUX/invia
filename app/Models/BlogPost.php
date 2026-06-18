<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'excerpt', 'content',
        'cover_image_path', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
