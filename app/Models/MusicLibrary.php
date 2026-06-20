<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MusicLibrary extends Model
{
    protected $table = 'music_library';

    protected $fillable = ['title', 'artist', 'file_path', 'cover_image', 'duration', 'mood', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(MusicCollection::class, 'music_collection_tracks', 'track_id', 'collection_id');
    }
}
