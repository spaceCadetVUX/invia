<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    protected $fillable = ['guest_id', 'event_id', 'message', 'is_pinned', 'is_hidden'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'is_hidden' => 'boolean'];
    }

    public function guest() { return $this->belongsTo(Guest::class); }
    public function event() { return $this->belongsTo(Event::class); }
}
