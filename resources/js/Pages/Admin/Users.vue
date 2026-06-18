<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    users:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

function applySearch() {
    router.get(route('admin.users.index'), { search: search.value }, { preserveState: true, replace: true })
}

const confirmTarget = ref(null)
const confirmAction = ref(null)
const confirmMsg    = ref('')

function openConfirm(action, user, msg) {
    confirmAction.value = action
    confirmTarget.value = user
    confirmMsg.value    = msg
}

function executeConfirm() {
    const user = confirmTarget.value
    if (confirmAction.value === 'ban') {
        router.patch(route('admin.users.ban', user.id), {}, { preserveScroll: true })
    } else if (confirmAction.value === 'unban') {
        router.patch(route('admin.users.unban', user.id), {}, { preserveScroll: true })
    } else if (confirmAction.value === 'delete') {
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true })
    }
    confirmTarget.value = null
}

function changeRole(user, role) {
    router.patch(route('admin.users.role', user.id), { role }, { preserveScroll: true })
}

const avatarColor = (name) => {
    const colors = [
        '#5B9FD6','#6C7FD8','#8B7FD4','#D47FB8','#D4887F',
        '#D4A97F','#A8D47F','#7FD4A8','#7FC8D4','#7F9FD4',
    ]
    let hash = 0
    for (let i = 0; i < (name?.length ?? 0); i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
    return colors[Math.abs(hash) % colors.length]
}
</script>

<template>
    <Head title="Admin — Users" />
    <AdminLayout>
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E1E2D] tracking-tight">Người dùng</h1>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ users.total?.toLocaleString('vi-VN') }} tài khoản
                    </p>
                </div>

                <!-- Search -->
                <form @submit.prevent="applySearch" class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Tìm tên, email..."
                            class="pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 bg-white w-full sm:w-64
                                   focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6]
                                   transition placeholder-gray-400"
                        />
                    </div>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-[#1E1E2D] text-white text-sm font-medium
                               hover:bg-[#2a2a3d] transition-colors shrink-0">
                        Tìm
                    </button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Người dùng</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Role</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Sự kiện</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Trạng thái</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-400 uppercase tracking-wide">Đăng ký</th>
                            <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-400 uppercase tracking-wide">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users.data" :key="user.id"
                            class="hover:bg-gray-50/60 transition-colors group">

                            <!-- User (avatar + name + email) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
                                         :style="{ background: avatarColor(user.name) }">
                                        {{ user.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-[#1E1E2D] truncate">{{ user.name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role select -->
                            <td class="px-6 py-4">
                                <div class="relative inline-flex items-center overflow-hidden rounded-lg">
                                    <select
                                        :value="user.roles?.[0]?.name ?? 'host'"
                                        @change="changeRole(user, $event.target.value)"
                                        style="-webkit-appearance:none;-moz-appearance:none;appearance:none"
                                        class="text-xs font-medium pl-2.5 pr-6 py-1.5 border-0 cursor-pointer
                                               focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 transition"
                                        :class="(user.roles?.[0]?.name ?? 'host') === 'admin'
                                            ? 'bg-[#1E1E2D] text-white'
                                            : 'bg-[#F0F4FF] text-[#5B9FD6]'">
                                        <option value="host">host</option>
                                        <option value="admin">admin</option>
                                    </select>
                                    <svg class="pointer-events-none absolute right-1.5 w-3 h-3 shrink-0"
                                         :class="(user.roles?.[0]?.name ?? 'host') === 'admin' ? 'text-white/60' : 'text-[#5B9FD6]/60'"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </td>

                            <!-- Events count -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-[#1E1E2D]">
                                    {{ user.events_count }}
                                    <span class="text-xs text-gray-400 font-normal">sự kiện</span>
                                </span>
                            </td>

                            <!-- Status badge -->
                            <td class="px-6 py-4">
                                <span v-if="user.banned_at"
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0" />
                                    Bị khóa
                                </span>
                                <span v-else
                                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" />
                                    Hoạt động
                                </span>
                            </td>

                            <!-- Joined -->
                            <td class="px-6 py-4 text-gray-400 text-xs whitespace-nowrap">
                                {{ user.created_at?.slice(0, 10) }}
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Ban / Unban -->
                                    <button v-if="!user.banned_at"
                                        @click="openConfirm('ban', user, `Khóa tài khoản ${user.email}?`)"
                                        title="Khóa tài khoản"
                                        class="p-1.5 rounded-lg text-amber-500 hover:bg-amber-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 1 0 0 10.5A5.25 5.25 0 0 0 12 1.5ZM3 20.25a9 9 0 0 1 14.818-6.856L4.964 26.35A9 9 0 0 1 3 20.25Zm9.964 5.6 12.855-12.856A9 9 0 0 1 21 20.25c0 4.59-3.44 8.375-7.894 8.917L12.964 25.85Z" clip-rule="evenodd"/>
                                            <path fill-rule="evenodd" d="M6.72 5.66a.75.75 0 0 1 1.06 0l12 12a.75.75 0 1 1-1.06 1.06l-12-12a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <button v-else
                                        @click="openConfirm('unban', user, `Mở khóa tài khoản ${user.email}?`)"
                                        title="Mở khóa"
                                        class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 1 0 0 10.5A5.25 5.25 0 0 0 12 1.5ZM3 20.25a9 9 0 0 1 18 0A.75.75 0 0 1 20.25 21H3.75A.75.75 0 0 1 3 20.25Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>

                                    <!-- Delete -->
                                    <button
                                        @click="openConfirm('delete', user, `Xóa vĩnh viễn ${user.email}? Hành động không thể hoàn tác.`)"
                                        title="Xóa tài khoản"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!users.data.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-gray-200">
                                        <path d="M7.5 6.75a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3 19.25C3 16.35 5.351 14 8.25 14h7.5C18.649 14 21 16.35 21 19.25A.75.75 0 0 1 20.25 20H3.75A.75.75 0 0 1 3 19.25Z"/>
                                    </svg>
                                    <p class="text-sm">Không tìm thấy người dùng nào.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-5 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Hiển thị {{ users.from }}–{{ users.to }} / {{ users.total }} người dùng
            </p>
            <div class="flex gap-1">
                <button
                    v-for="link in users.links"
                    :key="link.label"
                    v-html="link.label"
                    :disabled="!link.url"
                    @click="link.url && router.get(link.url)"
                    class="min-w-[36px] h-9 px-2.5 text-sm rounded-xl border transition-colors"
                    :class="link.active
                        ? 'bg-[#1E1E2D] text-white border-[#1E1E2D]'
                        : link.url
                            ? 'bg-white text-gray-600 border-gray-200 hover:border-[#5B9FD6] hover:text-[#5B9FD6]'
                            : 'bg-white text-gray-300 border-gray-100 cursor-not-allowed'"
                />
            </div>
        </div>

        <ConfirmDialog
            :model-value="!!confirmTarget"
            :title="confirmAction === 'delete' ? 'Xóa tài khoản' : 'Xác nhận'"
            :description="confirmMsg"
            :danger="confirmAction === 'delete'"
            confirm-label="Xác nhận"
            @update:model-value="val => { if (!val) confirmTarget = null }"
            @confirm="executeConfirm"
        />
    </AdminLayout>
</template>
