<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { useForm, Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    templates: Array,
    eventTypes: Array,
})

const form = useForm({
    title:         '',
    event_date:    '',
    event_time:    '',
    venue_name:    '',
    venue_address: '',
    event_type:    'wedding',
    template_id:   null,
    language:      'vi',
})

const pastDateWarning = computed(() => {
    if (!form.event_date) return false
    const chosen = new Date(form.event_date)
    chosen.setHours(0, 0, 0, 0)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return chosen < today
})

function submit() {
    form.post(route('dashboard.events.store'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Tạo thiệp mời" />
    <DashboardLayout>
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold text-gray-900 mb-8">Tạo thiệp mới</h1>

            <form @submit.prevent="submit" class="space-y-6 bg-white rounded-xl shadow-sm border border-gray-200 p-8">

                <!-- Lỗi quota -->
                <div v-if="form.errors.quota"
                    class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                    {{ form.errors.quota }}
                </div>

                <!-- Tên sự kiện -->
                <div>
                    <InputLabel for="title" value="Tên sự kiện *" />
                    <TextInput id="title" v-model="form.title" type="text"
                        class="mt-1 block w-full" placeholder="Ví dụ: Đám cưới Minh & Lan"
                        required autofocus />
                    <InputError class="mt-1" :message="form.errors.title" />
                </div>

                <!-- Loại sự kiện -->
                <div>
                    <InputLabel for="event_type" value="Loại sự kiện *" />
                    <select id="event_type" v-model="form.event_type"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option v-for="t in eventTypes" :key="t.value" :value="t.value">
                            {{ t.label }}
                        </option>
                    </select>
                    <InputError class="mt-1" :message="form.errors.event_type" />
                </div>

                <!-- Ngày & Giờ -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="event_date" value="Ngày tổ chức *" />
                        <TextInput id="event_date" v-model="form.event_date" type="date"
                            class="mt-1 block w-full" required />
                        <InputError class="mt-1" :message="form.errors.event_date" />
                    </div>
                    <div>
                        <InputLabel for="event_time" value="Giờ (tuỳ chọn)" />
                        <TextInput id="event_time" v-model="form.event_time" type="time"
                            class="mt-1 block w-full" />
                        <InputError class="mt-1" :message="form.errors.event_time" />
                    </div>
                </div>

                <!-- Cảnh báo ngày quá khứ -->
                <div v-if="pastDateWarning"
                    class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-md text-sm">
                    ⚠ Ngày tổ chức đã qua, bạn có muốn tiếp tục?
                </div>

                <!-- Địa điểm -->
                <div>
                    <InputLabel for="venue_name" value="Tên địa điểm" />
                    <TextInput id="venue_name" v-model="form.venue_name" type="text"
                        class="mt-1 block w-full" placeholder="Nhà hàng Tiệc Cưới ABC" />
                    <InputError class="mt-1" :message="form.errors.venue_name" />
                </div>
                <div>
                    <InputLabel for="venue_address" value="Địa chỉ" />
                    <TextInput id="venue_address" v-model="form.venue_address" type="text"
                        class="mt-1 block w-full" placeholder="123 Đường Lê Lợi, Q.1, TP.HCM" />
                    <InputError class="mt-1" :message="form.errors.venue_address" />
                </div>

                <!-- Chọn template -->
                <div>
                    <InputLabel value="Mẫu thiệp *" />
                    <InputError class="mt-1" :message="form.errors.template_id" />
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <button v-for="tpl in templates" :key="tpl.id" type="button"
                            @click="form.template_id = tpl.id"
                            :class="[
                                'relative border-2 rounded-lg p-3 text-left transition',
                                form.template_id === tpl.id
                                    ? 'border-indigo-600 bg-indigo-50'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]">
                            <div class="aspect-video bg-gray-100 rounded mb-2 overflow-hidden">
                                <img v-if="tpl.thumbnail_path" :src="tpl.thumbnail_path"
                                    :alt="tpl.name" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                    Preview
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ tpl.name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ tpl.price > 0 ? tpl.price.toLocaleString('vi-VN') + '₫' : 'Miễn phí' }}
                            </p>
                            <span v-if="form.template_id === tpl.id"
                                class="absolute top-2 right-2 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs">
                                ✓
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-2">
                    <PrimaryButton :disabled="form.processing || !form.template_id">
                        <span v-if="form.processing">Đang tạo...</span>
                        <span v-else>Tạo thiệp →</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>
