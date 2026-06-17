<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    event:  Object,
    wishes: Object,
})

const working = ref({})

function toggle(wish, action) {
    if (working.value[wish.id]) return
    working.value[wish.id] = true

    router.patch(
        route(`dashboard.events.wishes.${action}`, [props.event.slug, wish.id]),
        {},
        {
            preserveScroll: true,
            onFinish: () => delete working.value[wish.id],
        }
    )
}

function destroy(wish) {
    if (!confirm('Xoá lời chúc này?')) return
    router.delete(
        route('dashboard.events.wishes.destroy', [props.event.slug, wish.id]),
        { preserveScroll: true }
    )
}
</script>

<template>
    <Head :title="`Lời chúc — ${event.title}`" />
    <DashboardLayout>
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Sổ lời chúc</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ event.title }}</p>
                </div>
                <a :href="route('dashboard.events.show', event.slug)"
                   class="text-sm text-gray-500 hover:text-gray-700">← Quay lại</a>
            </div>

            <div v-if="!wishes.data.length" class="text-center py-16 text-gray-400">
                Chưa có lời chúc nào.
            </div>

            <div v-else class="space-y-3">
                <div v-for="wish in wishes.data" :key="wish.id"
                     class="bg-white border border-gray-200 rounded-xl p-4"
                     :class="{ 'border-indigo-300 bg-indigo-50/30': wish.is_pinned }">

                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span v-if="wish.is_pinned"
                                      class="text-xs text-indigo-600 font-semibold">📌 Ghim</span>
                                <span class="font-medium text-sm text-gray-900">
                                    {{ wish.guest?.name ?? 'Ẩn danh' }}
                                </span>
                                <span v-if="wish.guest?.email" class="text-xs text-gray-400">
                                    {{ wish.guest.email }}
                                </span>
                                <span v-if="wish.is_hidden"
                                      class="ml-auto text-xs text-red-500 font-medium">Đã ẩn</span>
                            </div>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ wish.message }}</p>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="toggle(wish, 'pin')"
                                    :disabled="working[wish.id]"
                                    :title="wish.is_pinned ? 'Bỏ ghim' : 'Ghim'"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition disabled:opacity-40">
                                {{ wish.is_pinned ? '📌' : '📍' }}
                            </button>
                            <button @click="toggle(wish, 'hide')"
                                    :disabled="working[wish.id]"
                                    :title="wish.is_hidden ? 'Hiện' : 'Ẩn'"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 transition disabled:opacity-40">
                                {{ wish.is_hidden ? '👁' : '🚫' }}
                            </button>
                            <button @click="destroy(wish)"
                                    :disabled="working[wish.id]"
                                    title="Xoá"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition disabled:opacity-40">
                                🗑
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="wishes.last_page > 1" class="flex justify-center gap-2 mt-6">
                <a v-for="link in wishes.links" :key="link.label"
                   :href="link.url ?? '#'"
                   v-html="link.label"
                   :class="[
                       'px-3 py-1.5 text-sm rounded-md border transition',
                       link.active
                           ? 'bg-indigo-600 text-white border-indigo-600'
                           : 'text-gray-600 border-gray-300 hover:bg-gray-50',
                       !link.url ? 'opacity-40 pointer-events-none' : ''
                   ]" />
            </div>
        </div>
    </DashboardLayout>
</template>
