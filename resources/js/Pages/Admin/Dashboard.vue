<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/StatCard.vue'

defineProps({
    stats:           { type: Object, required: true },
    recent_payments: { type: Array,  required: true },
})

const fmtK = (v) => (v / 1000).toLocaleString('vi-VN') + 'K'

const statusClass = (s) => ({
    paid:    'bg-emerald-100 text-emerald-700',
    success: 'bg-emerald-100 text-emerald-700',
    pending: 'bg-amber-100  text-amber-700',
    failed:  'bg-red-100    text-red-600',
}[s] ?? 'bg-gray-100 text-gray-500')

const statusLabel = (s) => ({
    paid:    'Đã thanh toán',
    success: 'Thành công',
    pending: 'Chờ xử lý',
    failed:  'Thất bại',
}[s] ?? (s ?? 'Thành công'))

const iconMoney = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v.816a3.836 3.836 0 0 0-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 0 1-.921-.421l-.879-.66a.75.75 0 0 0-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 0 0 1.5 0v-.81a4.124 4.124 0 0 0 1.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 0 0-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 0 0 .933-1.175l-.415-.33a3.836 3.836 0 0 0-1.719-.755V6Z" clip-rule="evenodd"/></svg>`
const iconUsers  = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M7.5 6.75a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3 19.25C3 16.35 5.351 14 8.25 14h7.5C18.649 14 21 16.35 21 19.25A.75.75 0 0 1 20.25 20H3.75A.75.75 0 0 1 3 19.25Z"/></svg>`
const iconEvents = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd"/></svg>`
const iconQueue  = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.47 1.72a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1-1.06 1.06l-1.72-1.72V7.5h-1.5V4.06L9.53 5.78a.75.75 0 0 1-1.06-1.06l3-3ZM11.25 7.5V15a.75.75 0 0 0 1.5 0V7.5h3.75a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9a3 3 0 0 1 3-3h3.75Z"/></svg>`
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#1E1E2D] tracking-tight">Dashboard</h1>
            <p class="text-sm text-gray-400 mt-1">Tổng quan hệ thống Invia</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <StatCard
                label="Doanh thu tháng này"
                :value="fmtK(stats.revenue_month) + 'đ'"
                :sub="`Hôm nay: ${fmtK(stats.revenue_today)}đ`"
                :dark="true"
                :icon="iconMoney"
            />
            <StatCard
                label="Người dùng"
                :value="stats.users.toLocaleString('vi-VN')"
                sub="Tổng tài khoản"
                :icon="iconUsers"
            />
            <StatCard
                label="Sự kiện"
                :value="stats.events_total.toLocaleString('vi-VN')"
                sub="Tổng sự kiện"
                :icon="iconEvents"
            />
            <StatCard
                label="Queue"
                :value="stats.queue_size !== null ? stats.queue_size : '—'"
                :sub="stats.queue_size === null ? 'Xem tại /horizon' : (stats.queue_size > 100 ? 'Tải cao' : 'Bình thường')"
                :icon="iconQueue"
                :accent="stats.queue_size > 100 ? '#FEE2E2' : null"
            />
        </div>

        <!-- Recent payments -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-[#1E1E2D]">Thanh toán gần đây</h2>
                <span class="text-xs text-gray-400">10 giao dịch mới nhất</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Host</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Sự kiện</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Gói</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Trạng thái</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide text-right">Số tiền</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="p in recent_payments" :key="p.id"
                            class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#EEF4FB] flex items-center justify-center text-[#5B9FD6] text-xs font-bold shrink-0">
                                        {{ p.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <span class="font-medium text-[#1E1E2D] whitespace-nowrap">{{ p.user?.name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 max-w-[160px] truncate">{{ p.event?.title ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-[#F0F4FF] text-[#5B9FD6] text-xs font-medium whitespace-nowrap">
                                    {{ p.plan ?? p.type ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium"
                                      :class="statusClass(p.status)">
                                    {{ statusLabel(p.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-[#1E1E2D] whitespace-nowrap">
                                {{ p.amount?.toLocaleString('vi-VN') }}đ
                            </td>
                            <td class="px-6 py-4 text-gray-400 text-xs whitespace-nowrap">{{ p.paid_at }}</td>
                        </tr>
                        <tr v-if="!recent_payments.length">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                Chưa có thanh toán nào.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
