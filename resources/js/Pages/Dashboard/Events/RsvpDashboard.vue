<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import TableNoInput from '@/Components/TableNoInput.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    event:   Object,
    rsvps:   Object,
    summary: Object,
    filters: Object,
})

const search       = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? '')
let   pollTimer    = null

function applyFilters() {
    router.get(
        route('dashboard.events.rsvp.index', props.event.slug),
        { status: statusFilter.value || undefined, search: search.value || undefined },
        { preserveScroll: true, replace: true }
    )
}

function sortBy(col) {
    const isActive = props.filters.sort === col
    const dir      = isActive && (props.filters.direction ?? 'desc') === 'asc' ? 'desc' : 'asc'
    router.get(
        route('dashboard.events.rsvp.index', props.event.slug),
        { ...props.filters, sort: col, direction: dir },
        { preserveScroll: true, replace: true }
    )
}

function sortIcon(col) {
    if (props.filters.sort !== col) return '↕'
    return (props.filters.direction ?? 'desc') === 'asc' ? '↑' : '↓'
}

onMounted(() => {
    pollTimer = setInterval(() => {
        router.reload({ only: ['summary'], preserveScroll: true, preserveState: true })
    }, 30000)
})
onUnmounted(() => clearInterval(pollTimer))

const statusColor = { yes: 'text-green-600 bg-green-50', no: 'text-red-600 bg-red-50', maybe: 'text-yellow-700 bg-yellow-50' }
const statusLabel = { yes: 'Tham dự', no: 'Từ chối', maybe: 'Có thể' }
</script>

<template>
    <Head :title="`RSVP — ${event.title}`" />

    <DashboardLayout>
        <!-- Summary cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div v-for="[label, val, color] in [
                ['Tham dự',       summary.yes,          'green'],
                ['Từ chối',        summary.no,           'red'],
                ['Có thể',         summary.maybe,        'yellow'],
                ['Tổng khách đến', summary.total_guests, 'blue'],
                ['Tổng người',     summary.total_people, 'purple'],
            ]" :key="label"
                class="bg-white rounded-lg border p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ val }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ label }}</div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap gap-2 mb-4">
            <input v-model="search" @keyup.enter="applyFilters"
                   placeholder="Tìm tên khách..."
                   class="border rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-1 focus:ring-indigo-400">

            <select v-model="statusFilter" @change="applyFilters"
                    class="border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                <option value="">Tất cả</option>
                <option value="yes">Tham dự</option>
                <option value="no">Từ chối</option>
                <option value="maybe">Có thể</option>
            </select>

            <button @click="applyFilters"
                    class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                Lọc
            </button>

            <a :href="route('dashboard.events.rsvp.export', event.slug)"
               class="px-3 py-1.5 border text-sm rounded-lg hover:bg-gray-50 ml-auto">
                ↓ Export Excel
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium cursor-pointer hover:text-gray-800"
                            @click="sortBy('name')">
                            Tên <span class="text-xs">{{ sortIcon('name') }}</span>
                        </th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">SĐT</th>
                        <th class="px-4 py-3 font-medium cursor-pointer hover:text-gray-800"
                            @click="sortBy('status')">
                            Trạng thái <span class="text-xs">{{ sortIcon('status') }}</span>
                        </th>
                        <th class="px-4 py-3 font-medium cursor-pointer hover:text-gray-800"
                            @click="sortBy('plus_one')">
                            +Người <span class="text-xs">{{ sortIcon('plus_one') }}</span>
                        </th>
                        <th class="px-4 py-3 font-medium">Yêu cầu ăn</th>
                        <th class="px-4 py-3 font-medium">Bàn</th>
                        <th class="px-4 py-3 font-medium cursor-pointer hover:text-gray-800"
                            @click="sortBy('created_at')">
                            Thời gian <span class="text-xs">{{ sortIcon('created_at') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="rsvp in rsvps.data" :key="rsvp.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ rsvp.guest.name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ rsvp.guest.email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ rsvp.guest.phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="statusColor[rsvp.status] ?? 'text-gray-600 bg-gray-100'">
                                {{ statusLabel[rsvp.status] ?? rsvp.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ rsvp.plus_one || '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-[120px] truncate">
                            {{ rsvp.dietary ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <TableNoInput :rsvp="rsvp" :event="event" />
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ rsvp.created_at }}
                        </td>
                    </tr>
                    <tr v-if="!rsvps.data.length">
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">
                            Chưa có RSVP nào.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="rsvps.last_page > 1" class="px-4 py-3 border-t flex gap-1">
                <component
                    v-for="link in rsvps.links" :key="link.label"
                    :is="link.url ? 'a' : 'span'"
                    :href="link.url ?? undefined"
                    v-html="link.label"
                    class="px-3 py-1 text-sm rounded border"
                    :class="link.active
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : link.url
                            ? 'hover:bg-gray-50 text-gray-600'
                            : 'text-gray-300 cursor-not-allowed'"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
