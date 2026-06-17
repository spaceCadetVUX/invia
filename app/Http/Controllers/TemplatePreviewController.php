<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class TemplatePreviewController extends Controller
{
    public function show(Template $template): View
    {
        abort_unless($template->is_active, 404);

        // Sample data tĩnh — không bao giờ dùng data thật của host
        $fakeEvent = (object) [
            'id'            => 0,
            'title'         => 'Nguyễn Văn An & Trần Thị Bình',
            'slug'          => 'preview',
            'event_type'    => 'wedding',
            'event_date'    => Carbon::parse('2025-12-25'),
            'event_time'    => '18:00',
            'venue_name'    => 'Trung tâm Hội nghị Quốc gia',
            'venue_address' => '1 Đại lộ Thăng Long, Hà Nội',
            'settings'      => [],
            'og_image_path' => null,
            'rsvp_enabled'  => true,
            'wishes_enabled'=> true,
            'template'      => $template,
        ];

        return view('templates.' . $template->blade_file . '.index', [
            'event'     => $fakeEvent,
            'guest'     => null,
            'music'     => ['type' => 'none'],
            'ogMeta'    => [
                'title'        => $fakeEvent->title,
                'description'  => 'Preview thiệp',
                'url'          => request()->url(),
                'image'        => asset('img/og-default.jpg'),
                'image_width'  => 1200,
                'image_height' => 630,
            ],
            'isPreview' => true,
        ]);
    }
}
