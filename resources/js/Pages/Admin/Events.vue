<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    events:  { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

function applySearch() {
    router.get(route('admin.events.index'), { search: search.value }, { preserveState: true, replace: true })
}

const deleteTarget = ref(null)

function forceDelete() {
    router.delete(route('admin.events.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<template>
    <Head title="Admin — Events" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Events</h1>
            <form @submit.prevent="applySearch" class="flex gap-2">
                <input v-model="search" type="text" placeholder="Tìm theo tiêu đề / slug / email..."
                    class="border border-gray-300 rounded-md px-3 py-1.5 text-sm w-72 focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-sm hover:bg-indigo-700 transition">Tìm</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tiêu đề</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Host</th>
                        <th class="px-4 py-3 font-medium">Template</th>
                        <th class="px-4 py-3 font-medium">Gói</th>
                        <th class="px-4 py-3 font-medium">Tạo lúc</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="event in events.data" :key="event.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium max-w-[200px] truncate">{{ event.title }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ event.slug }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ event.user?.email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ event.template?.name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ event.plan ?? 'free' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ event.created_at?.slice(0, 10) }}</td>
                        <td class="px-4 py-3">
                            <button @click="deleteTarget = event" class="text-xs text-red-600 hover:underline">Force delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="events.last_page > 1" class="mt-4 flex gap-2">
            <a v-for="link in events.links" :key="link.label"
               v-html="link.label"
               :href="link.url ?? '#'"
               @click.prevent="link.url && router.get(link.url)"
               class="px-3 py-1.5 text-sm rounded border"
               :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'" />
        </div>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Force delete event"
            :description="`Xóa vĩnh viễn event '${deleteTarget?.title}' của ${deleteTarget?.user?.email}? Toàn bộ guests, RSVP, lời chúc và files sẽ bị xóa.`"
            danger
            confirm-label="Xóa vĩnh viễn"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="forceDelete"
        />
    </AdminLayout>
</template>
