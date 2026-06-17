<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateOgImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public \App\Models\Event $event) {}

    public function handle(): void
    {
        $path = "og/{$this->event->slug}.jpg";

        // TODO: dùng intervention/image vẽ text lên ảnh nền template (1200×630)
        // Placeholder: chỉ update path để route logic hoạt động
        $this->event->update(['og_image_path' => $path]);
    }
}
