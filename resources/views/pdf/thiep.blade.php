<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 20mm; }
        body {
            font-family: 'DejaVu Serif', serif;
            text-align: center;
            color: #3d2b1f;
            margin: 0;
        }
        .ornament { font-size: 18px; color: #c9a96e; margin: 16px 0; letter-spacing: 8px; }
        .label    { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; color: #c9a96e; margin-bottom: 6px; }
        .name     { font-size: 32px; font-style: italic; color: #5a3825; margin: 4px 0; }
        .connector{ font-size: 24px; color: #c9a96e; margin: 8px 0; }
        .divider  { border: none; border-top: 1px solid #c9a96e; width: 60%; margin: 16px auto; }
        .date     { font-size: 18px; color: #5a3825; margin: 8px 0; }
        .time     { font-size: 13px; color: #7c6432; margin: 4px 0; }
        .venue    { font-size: 14px; color: #5a3825; margin: 4px 0; }
        .address  { font-size: 11px; color: #9ca3af; margin: 2px 0; }
        .footer   { margin-top: 32px; font-size: 9px; color: #ccc; }
        @php
            $settings  = $event->settings ?? [];
            $brideName = $settings['bride_name']['value'] ?? 'Cô Dâu';
            $groomName = $settings['groom_name']['value'] ?? 'Chú Rể';
        @endphp
    </style>
</head>
<body>
    @php
        $settings  = $event->settings ?? [];
        $brideName = $settings['bride_name']['value'] ?? 'Cô Dâu';
        $groomName = $settings['groom_name']['value'] ?? 'Chú Rể';
    @endphp

    <div class="ornament">✦ ✦ ✦</div>
    <div class="label">Trân trọng kính mời</div>

    <div class="name">{{ $brideName }}</div>
    <div class="connector">&amp;</div>
    <div class="name">{{ $groomName }}</div>

    <hr class="divider">

    <div class="date">{{ $event->event_date->format('d · m · Y') }}</div>
    @if($event->event_time)
    <div class="time">{{ \Illuminate\Support\Str::substr((string) $event->event_time, 0, 5) }}</div>
    @endif

    <div class="venue">{{ $event->venue_name }}</div>
    @if($event->venue_address)
    <div class="address">{{ $event->venue_address }}</div>
    @endif

    <div class="ornament" style="margin-top:24px;">✦</div>

    <div class="footer">Invia.vn</div>
</body>
</html>
