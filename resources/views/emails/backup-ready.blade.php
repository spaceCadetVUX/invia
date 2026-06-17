<x-mail::message>
# Backup sẵn sàng tải về

Backup cho sự kiện **{{ $event->title }}** đã được tạo xong.

Link tải sẽ **hết hạn sau 24 giờ**.

<x-mail::button :url="route('dashboard.events.backup.download', [$event->slug, $backup->token])">
Tải backup (.zip)
</x-mail::button>

Cảm ơn bạn đã sử dụng Invia.vn!
</x-mail::message>
