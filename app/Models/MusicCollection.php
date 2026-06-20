<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MusicCollection extends Model
{
    protected $fillable = ['name', 'description', 'cover_image'];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(MusicLibrary::class, 'music_collection_tracks', 'collection_id', 'track_id')
            ->withPivot('sort_order')
            ->orderBy('music_collection_tracks.sort_order');
    }
}
