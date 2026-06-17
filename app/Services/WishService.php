<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Wish;
use App\Repositories\WishRepository;
use Illuminate\Support\Str;

class WishService
{
    public function __construct(
        private WishRepository    $wishRepo,
        private NotificationService $notifService,
    ) {}

    public function submit(Event $event, array $data, ?string $token, string $ip): Wish
    {
        $guest = $token
            ? Guest::where('token', $token)->where('event_id', $event->id)->first()
            : null;

        if (!$guest) {
            $name  = $data['name'] ?? 'Ẩn danh';
            $guest = Guest::firstOrCreate(
                ['event_id' => $event->id, 'name' => $name, 'source' => 'self_register'],
                ['token' => Str::random(32)],
            );
        }

        $wish = $this->wishRepo->create([
            'event_id'  => $event->id,
            'guest_id'  => $guest->id,
            'message'   => $data['message'],
            'is_pinned' => false,
            'is_hidden' => false,
        ]);

        $this->notifService->notifyHost($event, 'wish', [
            'guest_name' => $guest->name,
            'wish_id'    => $wish->id,
            'preview'    => Str::limit($wish->message, 60),
            'event_id'   => $event->id,
        ]);

        return $wish->load('guest:id,name');
    }

    public function getPublicWishes(Event $event, ?string $afterId): array
    {
        $query = Wish::where('event_id', $event->id)
            ->where('is_hidden', false)
            ->with('guest:id,name')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($afterId) {
            $query->where('id', '>', (int) $afterId);
        } else {
            $query->limit(50);
        }

        return $query->get()->map(fn ($w) => [
            'id'         => $w->id,
            'name'       => $w->guest->name ?? 'Ẩn danh',
            'message'    => $w->message,
            'is_pinned'  => $w->is_pinned,
            'created_at' => $w->created_at->diffForHumans(),
        ])->toArray();
    }
}
