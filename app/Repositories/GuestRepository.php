<?php

namespace App\Repositories;

use App\Models\Guest;

class GuestRepository
{
    public function create(array $data): Guest
    {
        return Guest::create($data);
    }

    public function findByToken(string $token, int $eventId): ?Guest
    {
        return Guest::where('token', $token)->where('event_id', $eventId)->first();
    }
}
