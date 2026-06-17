<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    protected $fillable = ['user_id', 'template_id', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function template() { return $this->belongsTo(Template::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
