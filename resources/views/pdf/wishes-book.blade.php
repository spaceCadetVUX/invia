<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Serif', serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .cover {
            text-align: center;
            padding: 80px 40px;
            border-bottom: 2px solid #c9a96e;
        }

        .cover h1    { font-size: 28px; color: #7c5c2e; margin-bottom: 8px; }
        .cover .sub  { font-size: 13px; color: #999; margin-top: 4px; }

        .wish-item {
            padding: 20px 40px;
            border-bottom: 1px solid #f0e8d8;
            page-break-inside: avoid;
        }

        .guest-name {
            font-weight: bold;
            font-size: 13px;
            color: #7c5c2e;
            margin-bottom: 4px;
        }

        .pinned-badge {
            font-size: 10px;
            color: #c9a96e;
            margin-left: 6px;
        }

        .message {
            font-size: 13px;
            line-height: 1.7;
            color: #444;
            white-space: pre-line;
        }

        .date {
            font-size: 10px;
            color: #bbb;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #ccc;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="cover">
        <h1>Sổ lời chúc</h1>
        <div class="sub">{{ $event->title }}</div>
        <div class="sub">{{ $event->event_date->format('d/m/Y') }} &bull; {{ $event->venue_name }}</div>
    </div>

    @foreach ($wishes as $wish)
    <div class="wish-item">
        <div class="guest-name">
            {{ $wish->guest->name }}
            @if ($wish->is_pinned)
                <span class="pinned-badge">&#9733; Nổi bật</span>
            @endif
        </div>
        <div class="message">{{ $wish->message }}</div>
        <div class="date">{{ $wish->created_at->format('d/m/Y H:i') }}</div>
    </div>
    @endforeach

    <div class="footer">Tạo bởi Invia.vn &mdash; {{ now()->format('d/m/Y') }}</div>
</body>
</html>
