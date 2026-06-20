<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MusicLibrary extends Model
{
    protected $table = 'music_library';

    protected $fillable = ['title', 'artist', 'file_path', 'cover_image', 'duration', 'mood', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
