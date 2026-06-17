<?php

namespace App\Repositories;

use App\Models\Rsvp;

class RsvpRepository
{
    public function upsert(array $data): Rsvp
    {
        return Rsvp::updateOrCreate(
            ['guest_id' => $data['guest_id'], 'event_id' => $data['event_id']],
            $data,
        );
    }

    public function create(array $data): Rsvp
    {
        return Rsvp::create($data);
    }
}
