<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import ImportExcelPanel from '@/Components/ImportExcelPanel.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    event:           Object,
    guests:          Object,
    stats:           Object,
    selfRegisterUrl: String,
})

const showAddForm = ref(false)
const showImport  = ref(false)
const copiedLink  = ref(null)

const addForm = useForm({ name: '', email: '', phone: '', table_no: '' })

function addGuest() {
    addForm.post(route('dashboard.events.guests.store', props.event.slug), {
        onSuccess: () => { addForm.reset(); showAddForm.value = false },
        preserveScroll: true,
    })
}

function deleteGuest(guest) {
    if (!confirm(`Xóa ${guest.name}?`)) return
    router.delete(route('dashboard.events.guests.destroy', [props.event.slug, guest.id]), {
        preserveScroll: true,
    })
}

function copyPersonalLink(guest) {
    const url = route('invitation.show', props.event.slug) + '?t=' + guest.token
    navigator.clipboard.writeText(url)
    copiedLink.value = guest.id
    setTimeout(() => { copiedLink.value = null }, 2000)
}

function copySelfRegisterLink() {
    navigator.clipboard.writeText(props.selfRegisterUrl)
}

const rsvpColor = { yes: 'text-green-600', no: 'text-red-500', maybe: 'text-yellow-600' }
const rsvpLabel = { yes: 'Tham dự', no: 'Từ chối', maybe: 'Có thể' }
</script>

<template>
    <Head :title="`Khách mời — ${event.title}`" />

    <DashboardLayout>
        <!-- Stats bar -->
        <div class="grid grid-cols-5 gap-3 mb-6">
            <div v-for="(val, label) in {
                'Tổng': stats.total,
                'Có email': stats.withEmail,
                'Tham dự': stats.rsvpYes,
                'Từ chối': stats.rsvpNo,
                'Chưa RSVP': stats.noRsvp,
            }" :key="label"
                class="bg-white rounded-lg border p-4 text-center">
                <div class="text-2xl font-bold text-gray-800">{{ val }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ label }}</div>
            </div>
        </div>

        <!-- Actions bar -->
        <div class="flex flex-wrap gap-2 mb-4">
            <button @click="showAddForm = !showAddForm"
                    class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                + Thêm khách
            </button>
            <button @click="showImport = !showImport"
                    class="px-3 py-1.5 border text-sm rounded-lg hover:bg-gray-50">
                ↑ Import Excel
            </button>
            <a :href="route('dashboard.events.guests.export', event.slug)"
               class="px-3 py-1.5 border text-sm rounded-lg hover:bg-gray-50">
                ↓ Export Excel
            </a>
            <button @click="copySelfRegisterLink"
                    class="px-3 py-1.5 border text-sm rounded-lg hover:bg-gray-50 ml-auto">
                📋 Link tự đăng ký
            </button>
        </div>

        <!-- Add form inline -->
        <form v-if="showAddForm" @submit.prevent="addGuest"
              class="bg-gray-50 rounded-lg border p-4 mb-4 grid grid-cols-4 gap-3">
            <div>
                <input v-model="addForm.name" placeholder="Tên *" required
                       class="w-full border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                <span v-if="addForm.errors.name" class="text-xs text-red-500">{{ addForm.errors.name }}</span>
            </div>
            <input v-model="addForm.email"    type="email" placeholder="Email"
                   class="border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
            <input v-model="addForm.phone"    placeholder="SĐT"
                   class="border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
            <input v-model="addForm.table_no" placeholder="Bàn"
                   class="border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
            <div class="col-span-4 flex gap-2 justify-end">
                <button type="button" @click="showAddForm = false"
                        class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900">Hủy</button>
                <button type="submit" :disabled="addForm.processing"
                        class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg disabled:opacity-50">
                    Thêm
                </button>
            </div>
        </form>

        <!-- Import panel -->
        <ImportExcelPanel v-if="showImport" :event="event" @close="showImport = false" />

        <!-- Guest table -->
        <div class="bg-white rounded-lg border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium">Tên</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">SĐT</th>
                        <th class="px-4 py-3 font-medium">Bàn</th>
                        <th class="px-4 py-3 font-medium">RSVP</th>
                        <th class="px-4 py-3 font-medium">Nguồn</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="guest in guests.data" :key="guest.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ guest.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ guest.email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ guest.phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ guest.table_no ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span v-if="guest.rsvp" :class="rsvpColor[guest.rsvp.status] ?? 'text-gray-500'" class="text-xs font-medium">
                                {{ rsvpLabel[guest.rsvp.status] ?? guest.rsvp.status }}
                            </span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-400">{{ guest.source }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2 justify-end">
                                <button @click="copyPersonalLink(guest)"
                                        :title="copiedLink === guest.id ? 'Đã copy!' : 'Copy link cá nhân'"
                                        class="text-gray-400 hover:text-indigo-600 text-sm">
                                    {{ copiedLink === guest.id ? '✓' : '🔗' }}
                                </button>
                                <button @click="deleteGuest(guest)"
                                        class="text-gray-400 hover:text-red-500 text-sm">✕</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!guests.data.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                            Chưa có khách mời. Thêm thủ công hoặc import Excel.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="guests.last_page > 1" class="px-4 py-3 border-t flex gap-1">
                <component
                    v-for="link in guests.links" :key="link.label"
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
