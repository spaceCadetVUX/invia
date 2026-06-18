<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineProps({
    coupons: { type: Array, required: true },
})

const showForm    = ref(false)
const editTarget  = ref(null)
const form        = ref({ code: '', discount_type: 'percent', discount_value: 10, applicable_plans: '', max_uses: '', expires_at: '', is_active: true })

function openCreate() {
    editTarget.value = null
    form.value = { code: '', discount_type: 'percent', discount_value: 10, applicable_plans: '', max_uses: '', expires_at: '', is_active: true }
    showForm.value = true
}

function openEdit(coupon) {
    editTarget.value = coupon
    form.value = {
        code:              coupon.code,
        discount_type:     coupon.discount_type,
        discount_value:    coupon.discount_value,
        applicable_plans:  coupon.applicable_plans ?? '',
        max_uses:          coupon.max_uses ?? '',
        expires_at:        coupon.expires_at?.slice(0, 10) ?? '',
        is_active:         coupon.is_active,
    }
    showForm.value = true
}

function submitForm() {
    const payload = {
        ...form.value,
        max_uses:   form.value.max_uses  !== '' ? Number(form.value.max_uses)  : null,
        expires_at: form.value.expires_at !== '' ? form.value.expires_at : null,
    }
    if (editTarget.value) {
        router.patch(route('admin.coupons.update', editTarget.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    } else {
        router.post(route('admin.coupons.store'), payload, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    }
}

const deactivateTarget = ref(null)

function deactivate() {
    router.delete(route('admin.coupons.destroy', deactivateTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deactivateTarget.value = null },
    })
}
</script>

<template>
    <Head title="Admin — Coupons" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Coupons</h1>
            <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 transition">
                + Tạo coupon
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Giảm giá</th>
                        <th class="px-4 py-3 font-medium">Áp dụng</th>
                        <th class="px-4 py-3 font-medium">Lượt dùng</th>
                        <th class="px-4 py-3 font-medium">Hết hạn</th>
                        <th class="px-4 py-3 font-medium">Trạng thái</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="coupon in coupons" :key="coupon.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-medium">{{ coupon.code }}</td>
                        <td class="px-4 py-3">
                            {{ coupon.discount_type === 'percent' ? coupon.discount_value + '%' : coupon.discount_value.toLocaleString('vi') + 'đ' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ coupon.applicable_plans ?? 'Tất cả' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ coupon.used_count ?? 0 }}{{ coupon.max_uses ? ' / ' + coupon.max_uses : '' }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ coupon.expires_at?.slice(0, 10) ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="coupon.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ coupon.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <button @click="openEdit(coupon)" class="text-xs text-indigo-600 hover:underline">Sửa</button>
                                <button v-if="coupon.is_active" @click="deactivateTarget = coupon" class="text-xs text-red-600 hover:underline">Ẩn</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!coupons.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Chưa có coupon nào.</td>
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
                        <h3 class="text-lg font-semibold">{{ editTarget ? 'Sửa coupon' : 'Tạo coupon' }}</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                                <input v-model="form.code" :disabled="!!editTarget" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-400 disabled:bg-gray-50" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại</label>
                                    <select v-model="form.discount_type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                        <option value="percent">Phần trăm (%)</option>
                                        <option value="fixed">Cố định (VND)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị</label>
                                    <input v-model.number="form.discount_value" type="number" min="1" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Áp dụng gói (basic,pro,...)</label>
                                <input v-model="form.applicable_plans" placeholder="Để trống = tất cả" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tối đa lượt dùng</label>
                                    <input v-model="form.max_uses" type="number" min="1" placeholder="Không giới hạn" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hết hạn</label>
                                    <input v-model="form.expires_at" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                                </div>
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
            title="Ẩn coupon"
            :description="`Ẩn coupon '${deactivateTarget?.code}'?`"
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
