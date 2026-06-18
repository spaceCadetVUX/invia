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
</script>

<template>
    <Head title="Admin — Users" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Users</h1>
            <form @submit.prevent="applySearch" class="flex gap-2">
                <input v-model="search" type="text" placeholder="Tìm theo tên / email..."
                    class="border border-gray-300 rounded-md px-3 py-1.5 text-sm w-64 focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-sm hover:bg-indigo-700 transition">
                    Tìm
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tên</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Events</th>
                        <th class="px-4 py-3 font-medium">Trạng thái</th>
                        <th class="px-4 py-3 font-medium">Đăng ký</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ user.email }}</td>
                        <td class="px-4 py-3">
                            <select
                                :value="user.roles?.[0]?.name ?? 'host'"
                                @change="changeRole(user, $event.target.value)"
                                class="border border-gray-300 rounded px-2 py-1 text-xs">
                                <option value="host">host</option>
                                <option value="admin">admin</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">{{ user.events_count }}</td>
                        <td class="px-4 py-3">
                            <span v-if="user.banned_at" class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Bị khóa</span>
                            <span v-else class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Hoạt động</span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ user.created_at?.slice(0, 10) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button v-if="!user.banned_at"
                                    @click="openConfirm('ban', user, `Khóa tài khoản ${user.email}?`)"
                                    class="text-xs text-yellow-600 hover:underline">Khóa</button>
                                <button v-else
                                    @click="openConfirm('unban', user, `Mở khóa tài khoản ${user.email}?`)"
                                    class="text-xs text-green-600 hover:underline">Mở khóa</button>
                                <button
                                    @click="openConfirm('delete', user, `Xóa vĩnh viễn ${user.email}? Hành động không thể hoàn tác.`)"
                                    class="text-xs text-red-600 hover:underline">Xóa</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-4 flex gap-2">
            <a v-for="link in users.links" :key="link.label"
               v-html="link.label"
               :href="link.url ?? '#'"
               @click.prevent="link.url && router.get(link.url)"
               class="px-3 py-1.5 text-sm rounded border"
               :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'" />
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
