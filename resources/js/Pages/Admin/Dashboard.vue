<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/StatCard.vue'

defineProps({
    stats:           { type: Object, required: true },
    recent_payments: { type: Array,  required: true },
})
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-6">Dashboard</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
            <StatCard label="Users"       :value="stats.users" />
            <StatCard label="Events"      :value="stats.events_total" />
            <StatCard label="Hôm nay"     :value="(stats.revenue_today / 1000).toFixed(0) + 'K'" color="green" />
            <StatCard label="Tháng này"   :value="(stats.revenue_month / 1000).toFixed(0) + 'K'" color="green" />
            <StatCard
                label="Queue"
                :value="stats.queue_size !== null ? stats.queue_size : '—'"
                :color="stats.queue_size > 100 ? 'red' : 'gray'"
                :tooltip="stats.queue_size === null ? 'Xem tại /horizon' : null"
            />
        </div>

        <h2 class="text-sm font-medium text-gray-500 mb-3">Thanh toán gần đây</h2>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Host</th>
                        <th class="px-4 py-3 font-medium">Sự kiện</th>
                        <th class="px-4 py-3 font-medium">Gói</th>
                        <th class="px-4 py-3 font-medium">Số tiền</th>
                        <th class="px-4 py-3 font-medium">Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in recent_payments" :key="p.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">{{ p.user?.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ p.event?.title ?? '—' }}</td>
                        <td class="px-4 py-3">{{ p.plan ?? p.type }}</td>
                        <td class="px-4 py-3 font-medium">{{ p.amount?.toLocaleString('vi') }}đ</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ p.paid_at }}</td>
                    </tr>
                    <tr v-if="!recent_payments.length">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Chưa có thanh toán nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
