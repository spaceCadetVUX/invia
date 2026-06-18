<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineProps({
    tracks: { type: Array, required: true },
})

const showForm   = ref(false)
const uploading  = ref(false)
const form       = ref({ title: '', artist: '', mood: 'romantic', file: null })
const fileInput  = ref(null)

function selectFile(e) {
    form.value.file = e.target.files[0] ?? null
}

function submitUpload() {
    if (!form.value.file) return
    uploading.value = true
    router.post(route('admin.music.store'), {
        file:   form.value.file,
        title:  form.value.title,
        artist: form.value.artist,
        mood:   form.value.mood,
    }, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showForm.value  = false
            uploading.value = false
            form.value = { title: '', artist: '', mood: 'romantic', file: null }
        },
        onError:   () => { uploading.value = false },
    })
}

const deleteTarget = ref(null)

function deleteTrack() {
    router.delete(route('admin.music.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
    })
}

const MOODS = ['romantic', 'classical', 'acoustic', 'traditional']
</script>

<template>
    <Head title="Admin — Music" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Music Library</h1>
            <button @click="showForm = true" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 transition">
                + Upload nhạc
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tên bài</th>
                        <th class="px-4 py-3 font-medium">Nghệ sĩ</th>
                        <th class="px-4 py-3 font-medium">Mood</th>
                        <th class="px-4 py-3 font-medium">Duration</th>
                        <th class="px-4 py-3 font-medium">Upload</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="track in tracks" :key="track.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ track.title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ track.artist ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">{{ track.mood }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ track.duration ? `${Math.floor(track.duration / 60)}:${String(track.duration % 60).padStart(2, '0')}` : '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ track.created_at?.slice(0, 10) }}</td>
                        <td class="px-4 py-3">
                            <button @click="deleteTarget = track" class="text-xs text-red-600 hover:underline">Xóa</button>
                        </td>
                    </tr>
                    <tr v-if="!tracks.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Chưa có bài nhạc nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Upload modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
                    <div class="absolute inset-0 bg-black/50" @click="showForm = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">Upload nhạc</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">File MP3 (tối đa 15MB)</label>
                                <input ref="fileInput" type="file" accept=".mp3" @change="selectFile"
                                    class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên bài</label>
                                <input v-model="form.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nghệ sĩ</label>
                                <input v-model="form.artist" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mood</label>
                                <select v-model="form.mood" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                    <option v-for="m in MOODS" :key="m" :value="m">{{ m }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="showForm = false" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitUpload" :disabled="uploading" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ uploading ? 'Đang upload...' : 'Upload' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Xóa bài nhạc"
            :description="`Xóa bài '${deleteTarget?.title}'? File nhạc sẽ bị xóa vĩnh viễn.`"
            danger
            confirm-label="Xóa"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="deleteTrack"
        />
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
