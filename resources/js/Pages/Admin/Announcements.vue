<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineProps({
    announcements: { type: Array, required: true },
})

const showForm   = ref(false)
const editTarget = ref(null)
const form       = ref({ title: '', body: '', type: 'info', starts_at: '', ends_at: '', is_active: true })

function openCreate() {
    editTarget.value = null
    form.value = { title: '', body: '', type: 'info', starts_at: '', ends_at: '', is_active: true }
    showForm.value = true
}

function openEdit(ann) {
    editTarget.value = ann
    form.value = {
        title:     ann.title,
        body:      ann.body ?? '',
        type:      ann.type,
        starts_at: ann.starts_at?.slice(0, 16) ?? '',
        ends_at:   ann.ends_at?.slice(0, 16) ?? '',
        is_active: ann.is_active,
    }
    showForm.value = true
}

function submitForm() {
    const payload = {
        ...form.value,
        starts_at: form.value.starts_at || null,
        ends_at:   form.value.ends_at || null,
    }
    if (editTarget.value) {
        router.patch(route('admin.announcements.update', editTarget.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    } else {
        router.post(route('admin.announcements.store'), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    }
}

const deleteTarget = ref(null)

function deleteAnn() {
    router.delete(route('admin.announcements.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
    })
}

const typeColors = {
    info:    'bg-blue-100 text-blue-700',
    warning: 'bg-yellow-100 text-yellow-700',
    error:   'bg-red-100 text-red-700',
}
</script>

<template>
    <Head title="Admin — Announcements" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">System Announcements</h1>
            <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 transition">
                + Thông báo mới
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tiêu đề</th>
                        <th class="px-4 py-3 font-medium">Nội dung</th>
                        <th class="px-4 py-3 font-medium">Loại</th>
                        <th class="px-4 py-3 font-medium">Bắt đầu</th>
                        <th class="px-4 py-3 font-medium">Kết thúc</th>
                        <th class="px-4 py-3 font-medium">Trạng thái</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ann in announcements" :key="ann.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ ann.title }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">{{ ann.body ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full" :class="typeColors[ann.type]">{{ ann.type }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ ann.starts_at?.slice(0, 16) ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ ann.ends_at?.slice(0, 16) ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="ann.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ ann.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <button @click="openEdit(ann)" class="text-xs text-indigo-600 hover:underline">Sửa</button>
                                <button @click="deleteTarget = ann" class="text-xs text-red-600 hover:underline">Xóa</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!announcements.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Chưa có thông báo nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Form modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
                    <div class="absolute inset-0 bg-black/50" @click="showForm = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">{{ editTarget ? 'Sửa thông báo' : 'Thông báo mới' }}</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                                <input v-model="form.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung</label>
                                <textarea v-model="form.body" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Loại</label>
                                <select v-model="form.type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                    <option value="info">Info</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bắt đầu</label>
                                    <input v-model="form.starts_at" type="datetime-local" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kết thúc</label>
                                    <input v-model="form.ends_at" type="datetime-local" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_active" type="checkbox" class="rounded" />
                                Kích hoạt ngay
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="showForm = false" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitForm" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Lưu</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Xóa thông báo"
            :description="`Xóa thông báo '${deleteTarget?.title}'?`"
            danger
            confirm-label="Xóa"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="deleteAnn"
        />
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
