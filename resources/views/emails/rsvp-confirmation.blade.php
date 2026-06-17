<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận tham dự — {{ $event->title }}</title>
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
            <p style="margin:0 0 0.75rem; font-size:1rem;">Xin chào <strong>{{ $guest->name }}</strong>,</p>

            <p style="margin:0 0 1rem; color:#374151;">
                Chúng tôi đã nhận được xác nhận tham dự của bạn cho sự kiện
                <strong>{{ $event->title }}</strong>.
            </p>

            @php
                $statusLabel = match($rsvp->status) {
                    'yes'   => 'Sẽ tham dự',
                    'no'    => 'Không thể tham dự',
                    'maybe' => 'Có thể tham dự',
                    default => $rsvp->status,
                };
            @endphp

            <table width="100%" cellpadding="8" cellspacing="0" style="border:1px solid #e5e7eb; border-radius:0.5rem; margin-bottom:1.5rem;">
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="color:#6b7280; font-size:0.875rem; width:40%;">Trạng thái</td>
                    <td style="font-weight:600; font-size:0.875rem;">{{ $statusLabel }}</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="color:#6b7280; font-size:0.875rem;">Ngày tổ chức</td>
                    <td style="font-size:0.875rem;">{{ $event->event_date->format('d/m/Y') }}</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="color:#6b7280; font-size:0.875rem;">Địa điểm</td>
                    <td style="font-size:0.875rem;">{{ $event->venue_name }}</td>
                </tr>
                @if ($rsvp->plus_one > 0)
                <tr>
                    <td style="color:#6b7280; font-size:0.875rem;">Số người đi cùng</td>
                    <td style="font-size:0.875rem;">{{ $rsvp->plus_one }}</td>
                </tr>
                @endif
            </table>

            <a href="{{ route('invitation.show', $event->slug) }}"
               style="display:inline-block; padding:0.625rem 1.5rem; background:#4f46e5; color:#fff; border-radius:0.5rem; text-decoration:none; font-size:0.875rem; font-weight:500;">
                Xem lại thiệp mời
            </a>
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
