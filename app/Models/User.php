<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'google_id', 'avatar_url', 'email_verified_at',
        'banned_at',
        'bio', 'job_title', 'website',
        'twitter_url', 'linkedin_url', 'facebook_url',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'banned_at'         => 'datetime',
        ];
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function purchasedTemplates()
    {
        return $this->hasMany(UserTemplate::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }
}
