<?php

namespace App\Services;

use App\Exceptions\EventQuotaExceededException;
use App\Jobs\GenerateOgImage;
use App\Models\Event;
use App\Models\User;
use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(
        private EventRepository $eventRepo,
    ) {}

    public function createEvent(User $user, array $data): Event
    {
        $this->checkEventQuota($user);

        $slug = $this->generateSlug($data['title']);

        $event = $this->eventRepo->create([
            'user_id'       => $user->id,
            'template_id'   => $data['template_id'],
            'title'         => $data['title'],
            'slug'          => $slug,
            'event_type'    => $data['event_type'],
            'status'        => 'draft',
            'event_date'    => $data['event_date'],
            'event_time'    => $data['event_time'] ?? null,
            'venue_name'    => $data['venue_name'] ?? null,
            'venue_address' => $data['venue_address'] ?? null,
            'language'      => $data['language'] ?? 'vi',
            'settings'      => [],
            'expires_at'    => now()->addYear(),
        ]);

        GenerateOgImage::dispatch($event);

        Cache::forget("invitation:{$event->slug}");

        return $event;
    }

    private function checkEventQuota(User $user): void
    {
        $plan  = 'free'; // TODO: lấy từ payment record khi Phase 3 xong
        $limit = config("plans.{$plan}.max_events", 1);
        $count = $this->eventRepo->countActive($user->id);

        if ($count >= $limit) {
            throw new EventQuotaExceededException($plan, $limit);
        }
    }

    private function generateSlug(string $title): string
    {
        $base = Str::slug($title);

        if (empty($base)) {
            $base = 'event';
        }

        for ($i = 0; $i < 5; $i++) {
            $candidate = $i === 0 ? $base : $base . '-' . Str::random(6);

            if (!$this->eventRepo->slugExists($candidate)) {
                return $candidate;
            }
        }

        return $base . '-' . Str::random(8);
    }
}
