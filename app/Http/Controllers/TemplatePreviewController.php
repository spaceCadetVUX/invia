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

        abort_if(
            !in_array($template->blade_file, Template::active()->pluck('blade_file')->toArray()),
            404
        );

        return view('thiep.templates.' . $template->blade_file . '.index', [
            'event'     => $fakeEvent,
            'guest'     => null,
            'isPreview' => true,
        ]);
    }
}
