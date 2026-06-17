<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TrackEventView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $eventId,
        public ?int $guestId,
    ) {}

    public function handle(): void
    {
        Event::where('id', $this->eventId)->increment('view_count');
    }
}
