<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    event:   Object,
    wishes:  Object,
    summary: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
let pollTimer = null

function applySearch() {
    router.get(
        route('dashboard.events.wishes.index', props.event.slug),
        { search: search.value || undefined },
        { preserveScroll: true, replace: true }
    )
}

function togglePin(wish) {
    router.patch(
        route('dashboard.events.wishes.pin', [props.event.slug, wish.id]),
        {},
        { preserveScroll: true, preserveState: true }
    )
}

function toggleHide(wish) {
    router.patch(
        route('dashboard.events.wishes.hide', [props.event.slug, wish.id]),
        {},
        { preserveScroll: true, preserveState: true }
    )
}

function deleteWish(wish) {
    if (!confirm(`Xóa lời chúc của ${wish.guest?.name}?`)) return
    router.delete(
        route('dashboard.events.wishes.destroy', [props.event.slug, wish.id]),
        { preserveScroll: true }
    )
}

onMounted(() => {
    pollTimer = setInterval(() => {
        router.reload({ only: ['summary', 'wishes'], preserveScroll: true, preserveState: true })
    }, 30000)
})
onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
    <Head :title="`Lời chúc — ${event.title}`" />

    <DashboardLayout>
        <!-- Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div v-for="[label, val, color] in [
                ['Tổng',      summary.total,   ''],
                ['Đang hiện', summary.visible, 'text-green-600'],
                ['Đã ghim',   summary.pinned,  'text-yellow-600'],
                ['Đã ẩn',     summary.hidden,  'text-gray-400'],
            ]" :key="label"
                class="bg-white rounded-lg border p-4 text-center">
                <div class="text-2xl font-bold text-gray-800" :class="color">{{ val }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ label }}</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-wrap gap-2 mb-4">
            <input v-model="search" @keyup.enter="applySearch"
                   placeholder="Tìm tên hoặc nội dung..."
                   class="border rounded-lg px-3 py-1.5 text-sm w-72 focus:outline-none focus:ring-1 focus:ring-indigo-400">
            <button @click="applySearch"
                    class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                Tìm
            </button>
            <a :href="route('dashboard.events.wishes.export-pdf', event.slug)"
               class="px-3 py-1.5 border text-sm rounded-lg hover:bg-gray-50 ml-auto">
                ↓ Export PDF Sổ lời chúc
            </a>
        </div>

        <!-- Wish list -->
        <div class="space-y-3">
            <div v-for="wish in wishes.data" :key="wish.id"
                 class="bg-white border rounded-lg p-4 flex gap-3 transition-opacity"
                 :class="{
                     'border-yellow-300 bg-yellow-50': wish.is_pinned,
                     'opacity-50':                     wish.is_hidden,
                 }">

                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                    {{ wish.guest?.name?.[0]?.toUpperCase() ?? '?' }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-sm text-gray-800">{{ wish.guest?.name }}</span>
                        <span v-if="wish.is_pinned" class="text-xs text-yellow-600 font-medium">📌 Đã ghim</span>
                        <span v-if="wish.is_hidden" class="text-xs text-gray-400">🚫 Đã ẩn</span>
                        <time class="text-xs text-gray-400 ml-auto">{{ wish.created_at }}</time>
                    </div>
                    <p class="text-sm text-gray-700 mt-1 whitespace-pre-line leading-relaxed">{{ wish.message }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-1.5 flex-shrink-0">
                    <button @click="togglePin(wish)"
                            :title="wish.is_pinned ? 'Bỏ ghim' : 'Ghim lên đầu'"
                            class="p-1 rounded hover:bg-gray-100 text-base leading-none"
                            :class="wish.is_pinned ? 'text-yellow-500' : 'text-gray-300 hover:text-yellow-500'">
                        📌
                    </button>
                    <button @click="toggleHide(wish)"
                            :title="wish.is_hidden ? 'Hiện lại' : 'Ẩn khỏi thiệp'"
                            class="p-1 rounded hover:bg-gray-100 text-base leading-none"
                            :class="wish.is_hidden ? 'text-indigo-500' : 'text-gray-300 hover:text-indigo-500'">
                        {{ wish.is_hidden ? '👁' : '🙈' }}
                    </button>
                    <button @click="deleteWish(wish)"
                            title="Xóa"
                            class="p-1 rounded hover:bg-red-50 text-gray-300 hover:text-red-500 text-sm leading-none font-bold">
                        ✕
                    </button>
                </div>
            </div>

            <div v-if="!wishes.data.length"
                 class="text-center text-gray-400 py-12 text-sm">
                Chưa có lời chúc nào.
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="wishes.last_page > 1" class="mt-4 flex gap-1">
            <component
                v-for="link in wishes.links" :key="link.label"
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
    </DashboardLayout>
</template>
