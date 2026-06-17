<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    event:           Object,
    templates:       Array,
    currentTemplate: Number,
})

const page = usePage()
const filter = ref('all')
const previewTemplate = ref(null)
const confirmChange = ref(false)
const pendingTemplateId = ref(null)
const isSubmitting = ref(false)

const filterOptions = [
    { value: 'all',        label: 'Tất cả' },
    { value: 'wedding',    label: 'Đám cưới' },
    { value: 'birthday',   label: 'Sinh nhật' },
    { value: 'conference', label: 'Sự kiện' },
    { value: 'owned',      label: 'Đã mua' },
]

const filtered = computed(() => {
    if (filter.value === 'owned')
        return props.templates.filter(t => t.is_premium && t.is_owned)
    if (filter.value === 'all')
        return props.templates
    return props.templates.filter(t => t.category === filter.value)
})

function openPreview(template) {
    previewTemplate.value = template
}

function selectTemplate(template) {
    previewTemplate.value = null

    if (!template.is_owned) {
        // Placeholder — F3.x sẽ implement route payment.template
        alert('Tính năng mua template sẽ có ở Phase 3.')
        return
    }

    if (template.id === props.currentTemplate) return

    pendingTemplateId.value = template.id
    confirmChange.value = true
}

function confirmAndChange() {
    if (!pendingTemplateId.value || isSubmitting.value) return
    isSubmitting.value = true

    router.patch(
        route('dashboard.events.template.update', props.event.slug),
        { template_id: pendingTemplateId.value },
        {
            onFinish: () => {
                isSubmitting.value = false
                confirmChange.value = false
            },
        }
    )
}
</script>

<template>
    <Head :title="`Chọn template — ${event.title}`" />
    <DashboardLayout>
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Chọn mẫu thiệp</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ event.title }}</p>
                </div>
                <a :href="route('dashboard.events.show', event.slug)"
                    class="text-sm text-gray-500 hover:text-gray-700">← Quay lại</a>
            </div>

            <!-- Filter tabs -->
            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button v-for="opt in filterOptions" :key="opt.value"
                    @click="filter = opt.value"
                    :class="[
                        'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition',
                        filter === opt.value
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]">
                    {{ opt.label }}
                </button>
            </div>

            <!-- Template grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div v-for="tpl in filtered" :key="tpl.id"
                    class="relative border-2 rounded-xl overflow-hidden cursor-pointer group transition"
                    :class="tpl.id === currentTemplate ? 'border-indigo-600' : 'border-gray-200 hover:border-gray-300'"
                    @click="openPreview(tpl)">

                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gray-100">
                        <img v-if="tpl.thumbnail_url" :src="tpl.thumbnail_url"
                            :alt="tpl.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                            Preview
                        </div>
                    </div>

                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <span class="text-white text-sm font-medium bg-black/50 px-3 py-1 rounded-full">Xem thử</span>
                    </div>

                    <!-- Info -->
                    <div class="p-3">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ tpl.name }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span v-if="tpl.price === 0"
                                class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">
                                FREE
                            </span>
                            <span v-else-if="tpl.is_owned"
                                class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium">
                                Đã sở hữu
                            </span>
                            <span v-else
                                class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">
                                {{ tpl.price.toLocaleString('vi-VN') }}đ
                            </span>
                        </div>
                    </div>

                    <!-- Selected checkmark -->
                    <div v-if="tpl.id === currentTemplate"
                        class="absolute top-2 right-2 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold shadow">
                        ✓
                    </div>
                </div>
            </div>
        </div>

        <!-- Full-screen preview modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="previewTemplate"
                    class="fixed inset-0 z-50 flex bg-gray-900">

                    <!-- iframe preview -->
                    <div class="flex-1 relative">
                        <div class="absolute inset-0 flex items-center justify-center text-white text-sm opacity-50">
                            Đang tải...
                        </div>
                        <iframe
                            :src="route('template.preview', previewTemplate.id)"
                            class="absolute inset-0 w-full h-full border-0"
                            :title="previewTemplate.name" />
                    </div>

                    <!-- Sidebar -->
                    <div class="w-72 bg-white flex flex-col p-6 gap-4 shadow-xl">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ previewTemplate.name }}</h2>
                            <p class="text-sm text-gray-500 mt-1 capitalize">{{ previewTemplate.category }}</p>
                        </div>

                        <div>
                            <span v-if="previewTemplate.price === 0" class="text-green-600 font-medium">Miễn phí</span>
                            <span v-else-if="previewTemplate.is_owned" class="text-indigo-600 font-medium">Đã sở hữu</span>
                            <span v-else class="text-gray-900 font-medium">
                                {{ previewTemplate.price.toLocaleString('vi-VN') }}đ
                            </span>
                        </div>

                        <div class="mt-auto space-y-2">
                            <button v-if="previewTemplate.is_owned"
                                :disabled="previewTemplate.id === currentTemplate"
                                @click="selectTemplate(previewTemplate)"
                                :class="[
                                    'w-full py-2.5 text-sm font-medium rounded-md transition',
                                    previewTemplate.id === currentTemplate
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-indigo-600 text-white hover:bg-indigo-700'
                                ]">
                                {{ previewTemplate.id === currentTemplate ? 'Đang dùng' : 'Chọn template này' }}
                            </button>
                            <button v-else
                                @click="selectTemplate(previewTemplate)"
                                class="w-full py-2.5 text-sm font-medium bg-amber-500 text-white rounded-md hover:bg-amber-600 transition">
                                Mua — {{ previewTemplate.price.toLocaleString('vi-VN') }}đ
                            </button>

                            <button @click="previewTemplate = null"
                                class="w-full py-2.5 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Confirm reset settings dialog -->
        <ConfirmDialog
            v-model="confirmChange"
            title="Đổi template?"
            description="Đổi template sẽ xóa toàn bộ thiết lập vị trí, font, màu sắc hiện tại. Hành động này không thể hoàn tác."
            confirm-label="Đổi template"
            @confirm="confirmAndChange" />
    </DashboardLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
