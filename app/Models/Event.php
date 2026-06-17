<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'title', 'slug', 'event_type', 'status',
        'plan', 'extra_guests',
        'event_date', 'event_time', 'venue_name', 'venue_address', 'language',
        'music_type', 'music_source', 'livestream_url',
        'settings', 'og_image_path', 'view_count',
        'rsvp_enabled', 'wishes_enabled', 'self_register_enabled',
        'expires_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'settings'              => 'array',
            'event_date'            => 'date',
            'expires_at'            => 'datetime',
            'published_at'          => 'datetime',
            'rsvp_enabled'          => 'boolean',
            'wishes_enabled'        => 'boolean',
            'self_register_enabled' => 'boolean',
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
