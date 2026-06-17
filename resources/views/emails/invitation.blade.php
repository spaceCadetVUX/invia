<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>{{ __('email.invitation_subject', ['title' => $event->title]) }}</title>
</head>
<body style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; color:#111827; margin:0; padding:0; background:#f3f4f6;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; margin:2rem auto; background:#fff; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.08); overflow:hidden;">
    <tr>
        <td style="background:#4f46e5; padding:1.5rem 2rem;">
            <h1 style="color:#fff; margin:0; font-size:1.25rem; font-weight:700;">Invia.vn</h1>
        </td>
    </tr>
    <tr>
        <td style="padding:2rem;">
            <p style="margin:0 0 1rem; font-size:1rem;">
                Kính mời <strong>{{ $guest->name }}</strong> tham dự sự kiện.
            </p>

            <h2 style="font-size:1.375rem; font-weight:700; color:#111827; margin:0 0 1.25rem;">
                {{ $event->title }}
            </h2>

            <table width="100%" cellpadding="8" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:0.5rem; margin-bottom:1.5rem;">
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="color:#6b7280; font-size:0.875rem; width:35%;">Thời gian</td>
                    <td style="font-weight:500; font-size:0.875rem;">
                        {{ $event->event_date->format('d/m/Y') }}
                        @if ($event->event_time)
                            — {{ \Illuminate\Support\Str::substr((string) $event->event_time, 0, 5) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color:#6b7280; font-size:0.875rem;">Địa điểm</td>
                    <td style="font-size:0.875rem;">
                        {{ $event->venue_name }}
                        @if ($event->venue_address)
                            <br><span style="color:#9ca3af; font-size:0.8rem;">{{ $event->venue_address }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div style="text-align:center; margin-bottom:1.5rem;">
                <a href="{{ route('invitation.show', $event->slug) . '?t=' . $guest->token }}"
                   style="display:inline-block; padding:0.75rem 2rem; background:#4f46e5; color:#fff; border-radius:0.5rem; text-decoration:none; font-size:1rem; font-weight:600;">
                    Xem thiệp mời
                </a>
            </div>

            <hr style="border:none; border-top:1px solid #e5e7eb; margin:1.5rem 0;">

            <p style="font-size:0.75rem; color:#9ca3af; margin:0;">
                Bạn nhận được email này vì được mời tới sự kiện.
                <a href="{{ route('unsubscribe', $guest->token) }}" style="color:#9ca3af;">Hủy nhận email nhắc nhở</a>
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding:1rem 2rem; background:#f9fafb; border-top:1px solid #e5e7eb; font-size:0.75rem; color:#9ca3af; text-align:center;">
            Invia.vn — Thiệp mời trực tuyến
        </td>
    </tr>
</table>
</body>
</html>
