<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({ event: Object })

const status      = ref('idle')
const downloadUrl = ref(null)
const expiresAt   = ref(null)
let   pollTimer   = null

function stopPoll() { clearTimeout(pollTimer) }

async function checkStatus() {
    try {
        const res    = await axios.get(route('dashboard.events.backup.status', props.event.slug))
        status.value = res.data.status ?? 'idle'
        downloadUrl.value = res.data.download_url ?? null
        expiresAt.value   = res.data.expires_at   ?? null

        if (status.value === 'pending') {
            pollTimer = setTimeout(checkStatus, 5000)
        }
    } catch {
        // silently ignore poll errors
    }
}

async function createBackup() {
    stopPoll()
    status.value = 'pending'
    try {
        await axios.post(route('dashboard.events.backup.create', props.event.slug))
    } catch (e) {
        // Nếu already_processing hoặc ready — server trả 200/202, không throw
        const data = e.response?.data
        if (data?.status === 'ready') {
            status.value      = 'ready'
            downloadUrl.value = data.download_url
            expiresAt.value   = data.expires_at
            return
        }
    }
    pollTimer = setTimeout(checkStatus, 5000)
}

function formatExpiry(iso) {
    if (!iso) return ''
    return new Date(iso).toLocaleString('vi-VN')
}

onMounted(checkStatus)
onUnmounted(stopPoll)
</script>

<template>
    <div class="border rounded-lg p-5 bg-white">
        <h3 class="font-semibold text-gray-800 mb-1">Tải backup sự kiện</h3>
        <p class="text-sm text-gray-500 mb-4">
            1 file .zip gồm: danh sách khách (Excel), lời chúc (Excel + PDF),
            tóm tắt sự kiện (PDF), thiệp cưới (PDF + HTML offline).
        </p>

        <!-- Idle / Failed -->
        <button v-if="status === 'idle' || status === 'failed' || status === 'expired' || status === 'none'"
                @click="createBackup"
                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
            {{ status === 'failed' ? '↻ Thử lại' : 'Tạo backup' }}
        </button>

        <!-- Pending -->
        <div v-else-if="status === 'pending'"
             class="flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Đang tạo backup... (khoảng 1–2 phút)
        </div>

        <!-- Ready -->
        <div v-else-if="status === 'ready'" class="space-y-2">
            <a :href="downloadUrl"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                ↓ Tải backup (.zip)
            </a>
            <p class="text-xs text-gray-400">
                Hết hạn: {{ formatExpiry(expiresAt) }}
            </p>
            <button @click="createBackup"
                    class="text-xs text-indigo-500 underline hover:text-indigo-700">
                Tạo lại backup mới
            </button>
        </div>
    </div>
</template>
