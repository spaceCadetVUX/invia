<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Notification;

class NotificationService
{
    public function notifyHost(Event $event, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $event->user_id,
            'type'    => $type,
            'title'   => $this->buildTitle($type, $data),
            'body'    => $this->buildBody($type, $data),
            'data'    => $data,
            'read_at' => null,
        ]);
    }

    private function buildTitle(string $type, array $data): string
    {
        return match ($type) {
            'rsvp'  => __('notification.rsvp_title', ['name' => $data['guest_name']]),
            'wish'  => __('notification.wish_title', ['name' => $data['guest_name']]),
            default => __('notification.system_title'),
        };
    }

    private function buildBody(string $type, array $data): string
    {
        return match ($type) {
            'rsvp'  => __('notification.rsvp_body', ['status' => $data['rsvp_status']]),
            'wish'  => $data['preview'] ?? '',
            default => '',
        };
    }
}
