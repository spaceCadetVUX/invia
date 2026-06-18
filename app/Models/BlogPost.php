<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\CommonMarkConverter;

class BlogPost extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'excerpt', 'content',
        'cover_image_path', 'is_published', 'published_at',
        'meta_title', 'meta_description', 'faq',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'faq'          => 'array',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    public function getContentHtmlAttribute(): string
    {
        $content = $this->content ?? '';

        // HTML từ Tiptap — trả về trực tiếp
        if (str_starts_with(ltrim($content), '<')) {
            return $content;
        }

        // Legacy Markdown — render qua CommonMark
        $converter = new CommonMarkConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level'  => 20,
        ]);

        return $converter->convert($content)->getContent();
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));

        return max(1, (int) ceil($words / 200));
    }
}
