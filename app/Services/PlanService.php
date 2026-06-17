<?php

namespace App\Services;

use App\Exceptions\PlanFeatureException;
use App\Exceptions\QuotaExceededException;
use App\Models\Event;
use App\Models\Guest;
use Carbon\Carbon;

class PlanService
{
    public function getQuota(Event $event): array
    {
        $cfg = config("plans.{$event->plan}", config('plans.free'));

        $maxGuests     = ($cfg['guests'] ?? 0) + $event->extra_guests;
        $currentGuests = Guest::where('event_id', $event->id)->count();

        return [
            'plan'           => $event->plan,
            'max_guests'     => $maxGuests,
            'current_guests' => $currentGuests,
            'remaining'      => max(0, $maxGuests - $currentGuests),
            'can_email'      => (bool) ($cfg['email']  ?? false),
            'can_export'     => (bool) ($cfg['export'] ?? false),
            'can_table'      => (bool) ($cfg['table']  ?? false),
        ];
    }

    public function assertCanAddGuests(Event $event, int $count = 1): void
    {
        $quota = $this->getQuota($event);

        if ($quota['remaining'] < $count) {
            throw new QuotaExceededException('guests', $quota['remaining'], $event->plan);
        }
    }

    public function assertFeature(Event $event, string $feature): void
    {
        $quota = $this->getQuota($event);

        $allowed = match ($feature) {
            'email'  => $quota['can_email'],
            'export' => $quota['can_export'],
            'table'  => $quota['can_table'],
            default  => false,
        };

        if (!$allowed) {
            throw new PlanFeatureException($feature, $event->plan);
        }
    }

    public function isExpired(Event $event): bool
    {
        if (!$event->expires_at) return false;

        return $event->expires_at->isPast();
    }

    public function computeExpiresAt(string $plan): ?Carbon
    {
        $years = config("plans.{$plan}.storage_years");

        return $years ? now()->addYears($years) : null;
    }
}
