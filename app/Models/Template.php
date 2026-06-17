<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name', 'category', 'thumbnail_path', 'blade_file', 'version', 'price', 'is_active',
    ];

    public function events() { return $this->hasMany(Event::class); }
}
