<?php

namespace App\Services;

use App\Exceptions\TemplateNotPurchasedException;
use App\Jobs\GenerateOgImage;
use App\Models\Event;
use App\Models\Template;
use App\Models\User;
use App\Repositories\TemplateRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TemplateService
{
    public function __construct(private TemplateRepository $templateRepo) {}

    public function listForUser(User $user): Collection
    {
        $ownedIds = $user->purchasedTemplates()->pluck('template_id')->toArray();

        return Template::active()
            ->orderBy('price')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'thumbnail_path', 'price', 'blade_file', 'version'])
            ->map(fn($t) => array_merge($t->toArray(), [
                'is_owned'      => $t->price === 0 || in_array($t->id, $ownedIds),
                'is_premium'    => $t->price > 0,
                'thumbnail_url' => $t->thumbnail_path ? Storage::url($t->thumbnail_path) : null,
            ]));
    }

    public function changeTemplate(User $user, Event $event, int $templateId): void
    {
        $template = Template::active()->findOrFail($templateId);

        if ($template->isPremium()) {
            $owned = $user->purchasedTemplates()
                ->where('template_id', $templateId)
                ->exists();

            if (!$owned) {
                throw new TemplateNotPurchasedException($template);
            }
        }

        $event->update([
            'template_id' => $templateId,
            'settings'    => [],
        ]);

        GenerateOgImage::dispatch($event->fresh());
    }
}
