<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    event: Object,
    stats: Object,
})

const stats    = ref({ ...props.stats })
const sending  = ref(false)
const mode     = ref('unsent')
const queued   = ref(null)
let pollTimer  = null

async function send() {
    if (sending.value) return
    sending.value = true
    queued.value  = null

    try {
        const res = await axios.post(
            route('dashboard.events.send.store', props.event.slug),
            { mode: mode.value }
        )
        queued.value = res.data.queued
        if (res.data.queued > 0) startPolling()
        else sending.value = false
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Có lỗi xảy ra.'
        alert(msg)
        sending.value = false
    }
}

function startPolling() {
    pollTimer = setInterval(async () => {
        try {
            const res = await axios.get(route('dashboard.events.send.progress', props.event.slug))
            stats.value = res.data
            if (res.data.unsent === 0) stopPolling()
        } catch {}
    }, 3000)
}

function stopPolling() {
    clearInterval(pollTimer)
    pollTimer     = null
    sending.value = false
}

onUnmounted(stopPolling)
</script>

<template>
    <Head :title="`Gửi thiệp — ${event.title}`" />
    <DashboardLayout>
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gửi thiệp mời</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ event.title }}</p>
                </div>
                <a :href="route('dashboard.events.show', event.slug)"
                   class="text-sm text-gray-500 hover:text-gray-700">← Quay lại</a>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div v-for="item in [
                    { label: 'Tổng có email', value: stats.total,        color: 'gray' },
                    { label: 'Đã gửi',        value: stats.sent,         color: 'green' },
                    { label: 'Chưa gửi',      value: stats.unsent,       color: 'indigo' },
                    { label: 'Hủy nhận',      value: stats.unsubscribed, color: 'red' },
                ]" :key="item.label"
                    class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ item.value }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ item.label }}</div>
                </div>
            </div>

            <!-- Not published warning -->
            <div v-if="event.status !== 'published'"
                 class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm text-amber-800">
                ⚠️ {{ $t('send.publish_first') }}
            </div>

            <!-- Send form -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h2 class="font-medium text-gray-900 mb-4">Chế độ gửi</h2>

                <div class="space-y-3 mb-6">
                    <label v-for="opt in [
                        { value: 'unsent', label: 'Chỉ chưa gửi', desc: `${stats.unsent} khách` },
                        { value: 'all',    label: 'Gửi tất cả',   desc: `${stats.total} khách (kể cả đã gửi)` },
                    ]" :key="opt.value"
                        class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border transition"
                        :class="mode === opt.value ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" :value="opt.value" v-model="mode" class="mt-0.5">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ opt.label }}</div>
                            <div class="text-xs text-gray-500">{{ opt.desc }}</div>
                        </div>
                    </label>
                </div>

                <!-- Success message -->
                <div v-if="queued !== null && !sending"
                     class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 text-sm text-green-800">
                    ✅ Đã thêm {{ queued }} email vào hàng chờ.
                </div>

                <!-- Progress indicator -->
                <div v-if="sending && pollTimer"
                     class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 mb-4 text-sm text-indigo-800">
                    ⏳ Đang gửi... còn {{ stats.unsent }} email chưa gửi.
                </div>

                <button @click="send"
                        :disabled="sending || event.status !== 'published' || stats.unsent === 0"
                        class="w-full py-2.5 text-sm font-semibold rounded-lg transition"
                        :class="sending || event.status !== 'published' || stats.unsent === 0
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                    {{ sending ? 'Đang xử lý...' : 'Gửi thiệp mời' }}
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>
