<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const props = defineProps({ event: Object })
const emit  = defineEmits(['close'])

const file    = ref(null)
const result  = ref(null)
const loading = ref(false)
const error   = ref(null)

async function submit() {
    if (!file.value) return
    loading.value = true
    error.value   = null

    const form = new FormData()
    form.append('file', file.value)

    try {
        const res = await axios.post(
            route('dashboard.events.guests.import', props.event.slug),
            form,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        )
        result.value = res.data
        if (res.data.imported > 0) {
            router.reload({ only: ['guests', 'stats'] })
        }
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Lỗi import. Vui lòng thử lại.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input type="file" accept=".xlsx,.xls,.csv"
                   @change="e => { file = e.target.files[0]; result = null }"
                   class="text-sm">
            <button @click="submit" :disabled="!file || loading"
                    class="px-3 py-1.5 bg-amber-600 text-white text-sm rounded-lg disabled:opacity-50 hover:bg-amber-700">
                {{ loading ? 'Đang import...' : 'Import' }}
            </button>
            <a href="/templates/mau-import-khach.xlsx"
               class="text-sm text-blue-600 underline">Tải file mẫu</a>
            <button @click="emit('close')" class="ml-auto text-gray-400 hover:text-gray-600 text-lg leading-none">✕</button>
        </div>

        <p class="text-xs text-gray-500 mt-2">
            File Excel cần có các cột: <code class="bg-amber-100 px-1 rounded">ten</code>,
            <code class="bg-amber-100 px-1 rounded">email</code>,
            <code class="bg-amber-100 px-1 rounded">sdt</code>,
            <code class="bg-amber-100 px-1 rounded">ban</code>
        </p>

        <div v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</div>

        <div v-if="result" class="mt-3">
            <p class="text-sm text-gray-700">
                ✓ Đã import <strong>{{ result.imported }}</strong> khách.
                <span v-if="result.skipped > 0" class="text-amber-700">
                    Bỏ qua {{ result.skipped }} dòng lỗi.
                </span>
            </p>
            <ul v-if="result.errors.length" class="mt-2 text-xs text-red-600 space-y-1">
                <li v-for="err in result.errors" :key="err.row">
                    Dòng {{ err.row }}: {{ err.message }}
                </li>
            </ul>
        </div>
    </div>
</template>
