<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository
{
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function countActive(int $userId): int
    {
        return Event::where('user_id', $userId)
            ->whereIn('status', ['draft', 'published'])
            ->count();
    }

    public function slugExists(string $slug): bool
    {
        return Event::where('slug', $slug)->exists();
    }

    public function findBySlugForUser(string $slug, int $userId): ?Event
    {
        return Event::where('slug', $slug)
            ->where('user_id', $userId)
            ->with('template')
            ->first();
    }
}
