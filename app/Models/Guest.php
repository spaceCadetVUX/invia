<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'event_id', 'name', 'email', 'phone', 'table_no', 'token', 'source',
        'email_sent_at', 'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'email_sent_at'   => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function event()  { return $this->belongsTo(Event::class); }
    public function rsvp()   { return $this->hasOne(Rsvp::class); }
    public function wishes() { return $this->hasMany(Wish::class); }
}
