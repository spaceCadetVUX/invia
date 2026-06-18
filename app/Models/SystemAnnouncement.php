<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    protected $fillable = [
        'title', 'body', 'type', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }
}
