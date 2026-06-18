<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineProps({
    templates: { type: Array, required: true },
})

const showForm   = ref(false)
const editTarget = ref(null)
const form       = ref({ name: '', category: '', blade_file: '', price: 0, is_active: true })

function openCreate() {
    editTarget.value = null
    form.value = { name: '', category: '', blade_file: '', price: 0, is_active: true }
    showForm.value = true
}

function openEdit(tpl) {
    editTarget.value = tpl
    form.value = { name: tpl.name, category: tpl.category, blade_file: tpl.blade_file, price: tpl.price, is_active: tpl.is_active }
    showForm.value = true
}

function submitForm() {
    if (editTarget.value) {
        router.patch(route('admin.templates.update', editTarget.value.id), form.value, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    } else {
        router.post(route('admin.templates.store'), form.value, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    }
}

const deactivateTarget = ref(null)

function deactivate() {
    router.delete(route('admin.templates.destroy', deactivateTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deactivateTarget.value = null },
    })
}
</script>

<template>
    <Head title="Admin — Templates" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Templates</h1>
            <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 transition">
                + Thêm template
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tên</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">blade_file</th>
                        <th class="px-4 py-3 font-medium">Giá</th>
                        <th class="px-4 py-3 font-medium">v.</th>
                        <th class="px-4 py-3 font-medium">Events</th>
                        <th class="px-4 py-3 font-medium">Trạng thái</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tpl in templates" :key="tpl.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ tpl.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ tpl.category }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ tpl.blade_file }}</td>
                        <td class="px-4 py-3">{{ tpl.price === 0 ? 'Miễn phí' : tpl.price.toLocaleString('vi') + 'đ' }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ tpl.version }}</td>
                        <td class="px-4 py-3">{{ tpl.events_count }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="tpl.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ tpl.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <button @click="openEdit(tpl)" class="text-xs text-indigo-600 hover:underline">Sửa</button>
                                <button v-if="tpl.is_active" @click="deactivateTarget = tpl" class="text-xs text-red-600 hover:underline">Ẩn</button>
                            </div>
                        </td>
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
                        <h3 class="text-lg font-semibold">{{ editTarget ? 'Sửa template' : 'Thêm template' }}</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
                                <input v-model="form.name" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <input v-model="form.category" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">blade_file</label>
                                <input v-model="form.blade_file" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giá (VND)</label>
                                <input v-model.number="form.price" type="number" min="0" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_active" type="checkbox" class="rounded" />
                                Active
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
            :model-value="!!deactivateTarget"
            title="Ẩn template"
            :description="`Ẩn template '${deactivateTarget?.name}'? Template bị ẩn sẽ không hiển thị khi tạo thiệp mới.`"
            confirm-label="Ẩn"
            @update:model-value="val => { if (!val) deactivateTarget = null }"
            @confirm="deactivate"
        />
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
