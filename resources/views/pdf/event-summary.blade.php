<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Serif', serif; color: #333; margin: 0; padding: 40px; }
        h1   { font-size: 22px; color: #7c5c2e; border-bottom: 2px solid #c9a96e; padding-bottom: 10px; }
        h2   { font-size: 15px; color: #555; margin-top: 24px; margin-bottom: 8px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0e8d8; font-size: 13px; }
        .stat-grid { display: flex; gap: 16px; margin-top: 8px; }
        .stat { flex: 1; background: #fdf8f0; border: 1px solid #e8d8b8; border-radius: 6px; padding: 12px; text-align: center; }
        .stat .num { font-size: 28px; font-weight: bold; color: #7c5c2e; }
        .stat .lbl { font-size: 11px; color: #999; margin-top: 2px; }
        .footer { margin-top: 40px; font-size: 10px; color: #ccc; text-align: center; }
    </style>
</head>
<body>
    <h1>Tóm tắt sự kiện</h1>

    <h2>Thông tin</h2>
    <div class="row"><span>Tên sự kiện</span><span>{{ $event->title }}</span></div>
    <div class="row"><span>Ngày tổ chức</span><span>{{ $event->event_date->format('d/m/Y') }}</span></div>
    @if($event->event_time)
    <div class="row"><span>Giờ</span><span>{{ $event->event_time }}</span></div>
    @endif
    <div class="row"><span>Địa điểm</span><span>{{ $event->venue_name }}</span></div>
    @if($event->venue_address)
    <div class="row"><span>Địa chỉ</span><span>{{ $event->venue_address }}</span></div>
    @endif

    <h2>RSVP</h2>
    <div class="stat-grid">
        <div class="stat">
            <div class="num">{{ $rsvpStats['yes'] }}</div>
            <div class="lbl">Tham dự</div>
        </div>
        <div class="stat">
            <div class="num">{{ $rsvpStats['no'] }}</div>
            <div class="lbl">Từ chối</div>
        </div>
        <div class="stat">
            <div class="num">{{ $rsvpStats['maybe'] }}</div>
            <div class="lbl">Có thể</div>
        </div>
        <div class="stat">
            <div class="num">{{ $rsvpStats['total'] }}</div>
            <div class="lbl">Tổng khách</div>
        </div>
    </div>

    <div class="footer">Tạo bởi Invia.vn &mdash; {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
