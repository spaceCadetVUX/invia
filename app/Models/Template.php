<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name', 'category', 'thumbnail_path', 'blade_file', 'version', 'price', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'price' => 'integer'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function events() { return $this->hasMany(Event::class); }

    public function isPremium(): bool
    {
        return $this->price > 0;
    }
}
