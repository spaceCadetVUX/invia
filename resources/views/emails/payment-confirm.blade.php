<x-mail::message>
# Thanh toán thành công

Cảm ơn bạn đã nâng gói cho sự kiện **{{ $event->title }}**.

@if ($payment->type === 'plan')
**Gói đã kích hoạt:** {{ ucfirst($payment->plan) }}
@else
**Đã thêm:** 100 slot khách
@endif

**Số tiền:** {{ number_format($payment->amount) }}đ

<x-mail::button :url="route('dashboard.events.show', $event->slug)">
Quản lý sự kiện
</x-mail::button>

Trân trọng,<br>
Đội ngũ Invia.vn
</x-mail::message>
