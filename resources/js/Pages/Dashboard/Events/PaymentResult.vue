<script setup>
defineProps({
    event:  Object,
    status: String,   // 'paid' | 'pending' | 'cancelled'
    plan:   String,
    type:   String,
})
</script>

<template>
    <div class="max-w-md mx-auto py-16 text-center px-4">
        <!-- Success -->
        <template v-if="status === 'paid'">
            <div class="text-6xl mb-5">🎉</div>
            <h2 class="text-2xl font-semibold mb-2">Thanh toán thành công!</h2>
            <p class="text-gray-500 mb-6">
                <span v-if="type === 'plan'">Gói <strong class="capitalize">{{ plan }}</strong> đã được kích hoạt.</span>
                <span v-else>Đã thêm 100 slot khách vào sự kiện.</span>
                <br>Email xác nhận đã được gửi.
            </p>
        </template>

        <!-- Pending — webhook chưa đến -->
        <template v-else-if="status === 'pending'">
            <div class="text-6xl mb-5">⏳</div>
            <h2 class="text-2xl font-semibold mb-2">Đang xử lý...</h2>
            <p class="text-gray-500 mb-6">Thanh toán đang được xác nhận. Vui lòng đợi vài giây rồi refresh lại trang.</p>
        </template>

        <!-- Cancelled / other -->
        <template v-else>
            <div class="text-6xl mb-5">😕</div>
            <h2 class="text-2xl font-semibold mb-2">Thanh toán bị huỷ</h2>
            <p class="text-gray-500 mb-6">Bạn có thể thử lại bất cứ lúc nào.</p>
        </template>

        <a :href="route('dashboard.events.show', event.slug)"
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
            Quay lại sự kiện
        </a>
    </div>
</template>
