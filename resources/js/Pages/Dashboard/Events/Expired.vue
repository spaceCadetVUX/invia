<script setup>
defineProps({
    event: Object,   // id, slug, title, expires_at
})
</script>

<template>
    <div class="max-w-md mx-auto py-16 text-center px-4">
        <div class="text-6xl mb-5">⏰</div>
        <h2 class="text-2xl font-semibold mb-2">Sự kiện đã hết hạn lưu trữ</h2>
        <p class="text-gray-500 mb-2">
            Sự kiện <strong>{{ event.title }}</strong> đã hết hạn vào {{ new Date(event.expires_at).toLocaleDateString('vi-VN') }}.
        </p>
        <p class="text-gray-400 text-sm mb-8">
            Nâng gói để tiếp tục sử dụng và bảo toàn dữ liệu.
        </p>
        <a :href="route('dashboard.events.upgrade', event.slug)"
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
            Nâng gói ngay
        </a>
    </div>
</template>
