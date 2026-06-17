<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'event_type', 'slug', 'title',
        'date', 'venue', 'venue_map_url', 'settings',
        'music_type', 'music_source', 'livestream_url',
        'og_image_path', 'plan', 'guest_quota', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'settings'   => 'array',
            'date'       => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()      { return $this->belongsTo(User::class); }
    public function template()  { return $this->belongsTo(Template::class); }
    public function guests()    { return $this->hasMany(Guest::class); }
    public function rsvp()      { return $this->hasMany(Rsvp::class); }
    public function wishes()    { return $this->hasMany(Wish::class); }
    public function payments()  { return $this->hasMany(Payment::class); }
    public function collaborators() { return $this->hasMany(EventCollaborator::class); }
}
