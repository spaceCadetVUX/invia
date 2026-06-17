<script setup>
defineProps({
    event:       Object,
    feature:     String,   // 'guests' | 'email' | 'export' | 'table'
    currentPlan: String,
    remaining:   { type: Number, default: null },
})

const messages = {
    guests: 'Danh sách khách đã đầy.',
    email:  'Gửi email thiệp yêu cầu gói Basic trở lên.',
    export: 'Export Excel yêu cầu gói Pro trở lên.',
    table:  'Quản lý bàn tiệc yêu cầu gói Pro trở lên.',
}
</script>

<template>
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
        <span class="text-amber-500 text-lg">⚠</span>
        <div class="flex-1">
            <p class="text-sm font-medium text-amber-800">{{ messages[feature] }}</p>
            <p v-if="feature === 'guests' && remaining !== null" class="text-xs text-amber-600 mt-0.5">
                Còn {{ remaining }} slot. Mua Extra +100 khách (49,000đ) hoặc nâng gói.
            </p>
        </div>
        <a :href="route('dashboard.events.upgrade', event.slug)"
           class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-lg hover:bg-amber-700 whitespace-nowrap">
            Nâng gói
        </a>
    </div>
</template>
