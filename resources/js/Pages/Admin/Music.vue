<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    tracks: { type: Array, required: true },
})

// ── Upload form ──────────────────────────────────────────────
const showForm  = ref(false)
const uploading = ref(false)
const form      = ref({ title: '', artist: '', mood: 'romantic', file: null, cover_image: null })
const fileInput = ref(null)

function selectFile(e) {
    form.value.file = e.target.files[0] ?? null
}

function submitUpload() {
    if (!form.value.file) return
    uploading.value = true
    router.post(route('admin.music.store'), {
        file:         form.value.file,
        title:        form.value.title,
        artist:       form.value.artist,
        mood:         form.value.mood,
        cover_image:  form.value.cover_image,
    }, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showForm.value  = false
            uploading.value = false
            form.value = { title: '', artist: '', mood: 'romantic', file: null, cover_image: null }
            router.reload({ only: ['tracks'] })
            showToast('Upload thành công!')
        },
        onError: () => { uploading.value = false },
    })
}

// ── Edit / Delete ────────────────────────────────────────────
const deleteTarget = ref(null)
const editTarget   = ref(null)
const editForm     = ref({ title: '', artist: '', mood: 'romantic', cover_image: null })
const editSaving   = ref(false)

function openEdit(track) {
    editTarget.value = track
    editForm.value   = { title: track.title, artist: track.artist ?? '', mood: track.mood, cover_image: null }
}

function submitEdit() {
    if (!editTarget.value) return
    editSaving.value = true
    router.post(route('admin.music.update', editTarget.value.id), {
        ...editForm.value,
        _method: 'PATCH',
    }, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            editTarget.value = null
            editSaving.value = false
            router.reload({ only: ['tracks'] })
            showToast('Đã lưu thay đổi.')
        },
        onError: () => { editSaving.value = false },
    })
}

function deleteTrack() {
    const deletingId = deleteTarget.value.id
    router.delete(route('admin.music.destroy', deletingId), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (playingId.value === deletingId) closePlayer()
            deleteTarget.value = null
            router.reload({ only: ['tracks'] })
            showToast('Đã xóa bài nhạc.', 'error')
        },
    })
}

// ── Audio + player state ─────────────────────────────────────
const playingId      = ref(null)
const isPlaying      = ref(false)
const audioProgress  = ref(0)
const audioDuration  = ref(0)
const volume         = ref(parseFloat(localStorage.getItem('player_volume') ?? '0.8'))
const isMuted        = ref(localStorage.getItem('player_muted') === '1')

let audioEl    = null
let rafId      = null

const currentTrack = computed(() =>
    props.tracks.find(t => t.id === playingId.value) ?? null
)
const currentIdx = computed(() =>
    props.tracks.findIndex(t => t.id === playingId.value)
)

function fmtTime(secs) {
    if (!secs || isNaN(secs)) return '0:00'
    const s = Math.floor(secs)
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`
}

// ── Animation loop (progress tracking) ──────────────────────
function animLoop() {
    if (!isPlaying.value) return
    if (audioEl && audioDuration.value > 0) {
        audioProgress.value = audioEl.currentTime / audioDuration.value
    }
    rafId = requestAnimationFrame(animLoop)
}

// ── Playback controls ────────────────────────────────────────
function stopAudio() {
    cancelAnimationFrame(rafId)
    if (audioEl) { audioEl.pause(); audioEl = null }
    isPlaying.value = false
}

function playTrack(track) {
    stopAudio()
    playingId.value     = track.id
    isPlaying.value     = true
    audioProgress.value = 0
    audioDuration.value = track.duration ?? 0

    audioEl = new Audio(route('admin.music.stream', track.id))
    audioEl.volume = isMuted.value ? 0 : volume.value
    audioEl.addEventListener('loadedmetadata', () => {
        if (audioEl?.duration && isFinite(audioEl.duration)) {
            audioDuration.value = audioEl.duration
        }
    })
    audioEl.play().catch(() => {})
    audioEl.addEventListener('ended', () => {
        isPlaying.value     = false
        audioProgress.value = 1
        cancelAnimationFrame(rafId)
        const next = props.tracks[currentIdx.value + 1]
        if (next) playTrack(next)
    })

    rafId = requestAnimationFrame(animLoop)
}

function togglePlay(track) {
    if (playingId.value === track.id) {
        if (isPlaying.value) {
            audioEl?.pause()
            isPlaying.value = false
            cancelAnimationFrame(rafId)
        } else {
            audioEl?.play().catch(() => {})
            isPlaying.value = true
            rafId = requestAnimationFrame(animLoop)
        }
        return
    }
    playTrack(track)
}

function playerToggle() {
    if (!currentTrack.value) return
    togglePlay(currentTrack.value)
}

function playNext() {
    const next = props.tracks[currentIdx.value + 1]
    if (next) playTrack(next)
}

function playPrev() {
    if (audioEl && audioEl.currentTime > 3) {
        audioEl.currentTime = 0
        audioProgress.value = 0
        return
    }
    const prev = props.tracks[currentIdx.value - 1]
    if (prev) playTrack(prev)
}

function scrubSeek(e) {
    const rect  = e.currentTarget.getBoundingClientRect()
    const ratio = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width))
    audioProgress.value = ratio
    if (audioEl && audioDuration.value > 0) audioEl.currentTime = ratio * audioDuration.value
}

function toggleMute() {
    isMuted.value = !isMuted.value
    if (audioEl) audioEl.volume = isMuted.value ? 0 : volume.value
}

watch(volume, (v) => {
    if (audioEl) audioEl.volume = isMuted.value ? 0 : v
    if (isMuted.value && v > 0) isMuted.value = false
    localStorage.setItem('player_volume', v)
})

watch(isMuted, (v) => {
    localStorage.setItem('player_muted', v ? '1' : '0')
})

function closePlayer() {
    stopAudio()
    playingId.value     = null
    audioProgress.value = 0
}

onBeforeUnmount(() => { stopAudio() })

// ── Search + filter ──────────────────────────────────────────
const search     = ref('')
const moodFilter = ref('')

const filteredTracks = computed(() => {
    const q = search.value.toLowerCase().trim()
    return props.tracks.filter(t => {
        const matchMood = !moodFilter.value || t.mood === moodFilter.value
        const matchQ    = !q || t.title.toLowerCase().includes(q) || (t.artist ?? '').toLowerCase().includes(q)
        return matchMood && matchQ
    })
})

const uniqueMoods = computed(() => [...new Set(props.tracks.map(t => t.mood))].length)

// ── Toast ────────────────────────────────────────────────────
const toast = ref(null)
let toastTimer = null

function showToast(message, type = 'success') {
    clearTimeout(toastTimer)
    toast.value = { message, type }
    toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

const MOODS = ['romantic', 'classical', 'acoustic', 'traditional']

const MOOD_COLORS = {
    romantic:    { bg: '#FDF2F8', text: '#9D174D', dot: '#EC4899' },
    classical:   { bg: '#EFF6FF', text: '#1E40AF', dot: '#3B82F6' },
    acoustic:    { bg: '#F0FDF4', text: '#166534', dot: '#22C55E' },
    traditional: { bg: '#FFFBEB', text: '#92400E', dot: '#F59E0B' },
}
</script>

<template>
    <Head title="Admin — Music" />
    <AdminLayout>

        <!-- ── Hero header ─────────────────────────────────────── -->
        <div class="lib-hero">
            <div class="lib-hero-deco" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg>
            </div>
            <div class="lib-hero-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg>
            </div>
            <div class="lib-hero-text">
                <p class="lib-hero-eyebrow">Thư viện âm nhạc</p>
                <h1 class="lib-hero-title">Music Library</h1>
                <p class="lib-hero-stats">
                    <span>{{ tracks.length }} bài nhạc</span>
                    <span class="lib-stat-dot">·</span>
                    <span>{{ uniqueMoods }} thể loại</span>
                </p>
            </div>
            <button class="lib-hero-btn" @click="showForm = true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;flex-shrink:0"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                Upload nhạc
            </button>
        </div>

        <!-- ── Search + mood filter ────────────────────────────── -->
        <div class="lib-filters">
            <div class="lib-search-wrap">
                <svg class="lib-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
                <input v-model="search" class="lib-search-input" placeholder="Tìm bài nhạc, nghệ sĩ…" />
            </div>
            <div class="lib-mood-pills">
                <button class="lib-mood-btn" :class="{ active: !moodFilter }" @click="moodFilter = ''">Tất cả</button>
                <button
                    v-for="m in MOODS" :key="m"
                    class="lib-mood-btn"
                    :class="{ active: moodFilter === m }"
                    :style="moodFilter === m ? { background: MOOD_COLORS[m]?.bg, color: MOOD_COLORS[m]?.text, borderColor: MOOD_COLORS[m]?.dot } : {}"
                    @click="moodFilter = m">{{ m }}</button>
            </div>
        </div>

        <!-- ── Track list ──────────────────────────────────────── -->
        <div class="lib-tracklist">
            <div class="tl-header">
                <div class="tl-col-num">#</div>
                <div class="tl-col-title">Tên bài</div>
                <div class="tl-col-mood">Mood</div>
                <div class="tl-col-date">Ngày up</div>
                <div class="tl-col-dur">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" /></svg>
                </div>
                <div class="tl-col-actions" />
            </div>

            <TransitionGroup name="tl-item" tag="div" class="tl-body">
                <div
                    v-for="(track, idx) in filteredTracks"
                    :key="track.id"
                    class="tl-row"
                    :class="{ 'is-current': playingId === track.id }">

                    <div class="tl-col-num tl-num-cell">
                        <span v-if="playingId === track.id && isPlaying" class="playing-bars" aria-hidden="true">
                            <i /><i /><i />
                        </span>
                        <span v-else class="row-idx" :class="{ 'is-rose': playingId === track.id }">{{ idx + 1 }}</span>
                        <button class="row-play" @click="togglePlay(track)" :aria-label="playingId === track.id && isPlaying ? 'Pause' : 'Play'">
                            <svg v-if="!(playingId === track.id && isPlaying)" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px">
                                <path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" />
                            </svg>
                            <svg v-else viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px">
                                <path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z" />
                            </svg>
                        </button>
                    </div>

                    <div class="tl-col-title tl-title-cell">
                        <div class="tl-thumb">
                            <img v-if="track.cover_image" :src="route('admin.music.cover', track.id)" class="tl-thumb-img" />
                            <div v-else class="tl-thumb-empty">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg>
                            </div>
                        </div>
                        <div class="tl-name-wrap">
                            <span class="tl-name" :class="{ 'is-rose': playingId === track.id }">{{ track.title }}</span>
                            <span class="tl-artist">{{ track.artist ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="tl-col-mood">
                        <span class="tl-mood-tag"
                            :style="{ background: MOOD_COLORS[track.mood]?.bg ?? '#F3F4F6', color: MOOD_COLORS[track.mood]?.text ?? '#374151' }">
                            <span class="tl-mood-dot" :style="{ background: MOOD_COLORS[track.mood]?.dot ?? '#9CA3AF' }" />
                            {{ track.mood }}
                        </span>
                    </div>

                    <div class="tl-col-date tl-date">{{ track.created_at?.slice(0, 10) }}</div>

                    <div class="tl-col-dur tl-dur">
                        {{ track.duration ? `${Math.floor(track.duration / 60)}:${String(track.duration % 60).padStart(2, '0')}` : '—' }}
                    </div>

                    <div class="tl-col-actions tl-actions">
                        <button class="tl-act edit" @click="openEdit(track)" title="Sửa thông tin">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px">
                                <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
                            </svg>
                        </button>
                        <button class="tl-act delete" @click="deleteTarget = track" title="Xóa bài nhạc">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px">
                                <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </TransitionGroup>

            <div v-if="filteredTracks.length === 0" class="tl-empty">
                <div class="tl-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg>
                </div>
                <p class="tl-empty-title">{{ search || moodFilter ? 'Không tìm thấy bài nào' : 'Chưa có bài nhạc nào' }}</p>
                <p class="tl-empty-sub">{{ search || moodFilter ? 'Thử từ khoá khác hoặc bỏ bộ lọc' : 'Nhấn "Upload nhạc" để thêm bài đầu tiên' }}</p>
            </div>
        </div>

        <!-- Bottom bar spacer (prevents content hiding behind player) -->
        <div v-if="currentTrack" class="sp-bar-spacer" aria-hidden="true" />

        <!-- ── Upload modal ────────────────────────────────────── -->
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh bìa (tuỳ chọn)</label>
                                <input type="file" accept="image/*" @change="e => form.cover_image = e.target.files[0] ?? null"
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

        <!-- ── Toast ──────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="toast-slide">
                <div v-if="toast" class="toast-wrap" :class="toast.type">
                    <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="toast-icon">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="toast-icon">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ toast.message }}</span>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Spotify bottom player bar ──────────────────────── -->
        <Teleport to="body">
            <Transition name="player-slide">
                <div v-if="currentTrack" class="sp-bar" :class="{ 'is-playing': isPlaying }">

                    <!-- Left: cover + track info -->
                    <div class="sp-left">
                        <Transition name="cover-swap" mode="out-in">
                            <div class="sp-thumb" :key="'thumb-' + currentTrack.id">
                                <img v-if="currentTrack.cover_image" :src="route('admin.music.cover', currentTrack.id)" class="sp-thumb-img" />
                                <div v-else class="sp-thumb-empty">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg>
                                </div>
                            </div>
                        </Transition>
                        <Transition name="track-switch" mode="out-in">
                            <div class="sp-info" :key="'info-' + currentTrack.id">
                                <span class="sp-title">{{ currentTrack.title }}</span>
                                <span class="sp-artist">{{ currentTrack.artist ?? '—' }}</span>
                            </div>
                        </Transition>
                        <span class="sp-mood-badge"
                            :style="{ background: MOOD_COLORS[currentTrack.mood]?.bg, color: MOOD_COLORS[currentTrack.mood]?.text }">
                            {{ currentTrack.mood }}
                        </span>
                    </div>

                    <!-- Center: controls + seek bar -->
                    <div class="sp-center">
                        <div class="sp-controls">
                            <button class="sp-skip" @click="playPrev" aria-label="Trước">
                                <svg width="16" height="16" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="2" height="10" rx="1" fill="currentColor"/><path d="M12 3.5L5.5 7L12 10.5V3.5Z" fill="currentColor"/></svg>
                            </button>
                            <button class="sp-play" @click="playerToggle" :aria-label="isPlaying ? 'Pause' : 'Play'">
                                <svg v-if="!isPlaying" viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                <svg v-else viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z" /></svg>
                            </button>
                            <button class="sp-skip" @click="playNext" aria-label="Tiếp">
                                <svg width="16" height="16" viewBox="0 0 14 14" fill="none"><rect x="11" y="2" width="2" height="10" rx="1" fill="currentColor"/><path d="M2 3.5L8.5 7L2 10.5V3.5Z" fill="currentColor"/></svg>
                            </button>
                        </div>
                        <div class="sp-seek-row">
                            <span class="sp-time">{{ fmtTime(audioProgress * audioDuration) }}</span>
                            <div class="sp-seek" @click="scrubSeek">
                                <div class="sp-seek-track">
                                    <div class="sp-seek-fill" :style="{ width: (audioProgress * 100) + '%' }" />
                                </div>
                            </div>
                            <span class="sp-time">{{ fmtTime(audioDuration) }}</span>
                        </div>
                    </div>

                    <!-- Right: volume + close -->
                    <div class="sp-right">
                        <button class="sp-vol-btn" @click="toggleMute" :aria-label="isMuted ? 'Unmute' : 'Mute'">
                            <svg v-if="isMuted || volume === 0" width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="m11.5 5.5-3 3M8.5 5.5l3 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <svg v-else-if="volume < 0.5" width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="M10 5.5a3 3 0 0 1 0 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"/></svg>
                            <svg v-else width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="M10 5.5a3 3 0 0 1 0 4M11.5 3.5a6 6 0 0 1 0 8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"/></svg>
                        </button>
                        <input type="range" class="sp-vol" min="0" max="1" step="0.01"
                            :value="isMuted ? 0 : volume"
                            @input="e => { volume = parseFloat(e.target.value); isMuted = false }"
                            aria-label="Volume" />
                        <button class="sp-close" @click="closePlayer" aria-label="Đóng">
                            <svg viewBox="0 0 12 12" fill="none" style="width:10px;height:10px"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Edit modal ──────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="editTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="editTarget = null">
                    <div class="absolute inset-0 bg-black/50" @click="editTarget = null" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">Chỉnh sửa thông tin</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tên bài</label>
                                <input v-model="editForm.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nghệ sĩ</label>
                                <input v-model="editForm.artist" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mood</label>
                                <select v-model="editForm.mood" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                    <option v-for="m in MOODS" :key="m" :value="m">{{ m }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh bìa</label>
                                <div v-if="editTarget?.cover_image" class="mb-2">
                                    <img :src="route('admin.music.cover', editTarget.id)" class="h-16 w-16 rounded-lg object-cover border border-gray-200" />
                                </div>
                                <input type="file" accept="image/*" @change="e => editForm.cover_image = e.target.files[0] ?? null"
                                    class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700" />
                                <p class="text-xs text-gray-400 mt-1">JPG/PNG/WebP, tối đa 2MB. Bỏ trống để giữ ảnh cũ.</p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="editTarget = null" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitEdit" :disabled="editSaving" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ editSaving ? 'Đang lưu...' : 'Lưu' }}
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
/* ═══════════════════════════════════════════════════════════
   PAGE LAYOUT — HERO
═══════════════════════════════════════════════════════════ */
.lib-hero {
    position: relative;
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 28px 28px 24px;
    margin-bottom: 20px;
    background: linear-gradient(145deg, #FFF1F4 0%, #FFF8FA 55%, #FFFFFF 100%);
    border: 1px solid #F3E4E8;
    border-radius: 18px;
    overflow: hidden;
}
.lib-hero-deco {
    position: absolute; right: -16px; top: -16px;
    width: 140px; height: 140px; color: #C9637A; opacity: 0.07; pointer-events: none;
}
.lib-hero-deco svg { width: 100%; height: 100%; }
.lib-hero-icon {
    width: 68px; height: 68px; flex-shrink: 0; border-radius: 16px;
    background: linear-gradient(140deg, #C9637A 0%, #9D4B5E 100%);
    display: flex; align-items: center; justify-content: center; color: white;
    box-shadow: 0 8px 24px rgba(201,99,122,0.32);
}
.lib-hero-icon svg { width: 34px; height: 34px; }
.lib-hero-text { flex: 1; min-width: 0; }
.lib-hero-eyebrow {
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: #C9637A; margin-bottom: 3px;
}
.lib-hero-title {
    font-family: Georgia, 'Times New Roman', serif; font-style: italic;
    font-size: 34px; line-height: 1.1; color: #1A1523; margin-bottom: 6px;
}
.lib-hero-stats { font-size: 13px; color: #7A6D92; display: flex; align-items: center; gap: 6px; }
.lib-stat-dot { color: #D4B8BF; }
.lib-hero-btn {
    display: flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: #C9637A; color: white; border: none; border-radius: 24px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    box-shadow: 0 4px 14px rgba(201,99,122,0.35);
    transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
    flex-shrink: 0; white-space: nowrap;
}
.lib-hero-btn:hover { background: #B5566B; box-shadow: 0 6px 18px rgba(201,99,122,0.42); }
.lib-hero-btn:active { transform: scale(0.97); }

/* ═══════════════════════════════════════════════════════════
   FILTERS
═══════════════════════════════════════════════════════════ */
.lib-filters {
    display: flex; align-items: center; gap: 14px;
    padding-bottom: 14px; border-bottom: 1px solid #F0ECF3; flex-wrap: wrap;
}
.lib-search-wrap { position: relative; flex: 1; min-width: 160px; max-width: 280px; }
.lib-search-icon {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    width: 14px; height: 14px; color: #B0A8B8; pointer-events: none;
}
.lib-search-input {
    width: 100%; padding: 8px 12px 8px 33px; background: #F7F5F9;
    border: 1px solid #EDE9F0; border-radius: 20px; font-size: 13px;
    color: #1A1523; outline: none; transition: border-color 0.15s, background 0.15s;
}
.lib-search-input::placeholder { color: #B0A8B8; }
.lib-search-input:focus { background: white; border-color: #C9637A; box-shadow: 0 0 0 3px rgba(201,99,122,0.1); }
.lib-mood-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.lib-mood-btn {
    padding: 5px 13px; border: 1px solid #E5E0EA; border-radius: 20px;
    background: white; font-size: 12px; font-weight: 500; color: #7A6D92;
    cursor: pointer; transition: all 0.15s; text-transform: capitalize;
}
.lib-mood-btn:hover { border-color: #C9637A; color: #C9637A; }
.lib-mood-btn.active { background: #FFF1F4; border-color: #C9637A; color: #9D4B5E; }

/* ═══════════════════════════════════════════════════════════
   TRACK LIST
═══════════════════════════════════════════════════════════ */
.lib-tracklist {
    background: white; border: 1px solid #EDE9F0; border-radius: 16px;
    overflow: hidden; margin-top: 18px;
}
.tl-header, .tl-row {
    display: grid;
    grid-template-columns: 48px 1fr 128px 92px 56px 76px;
    align-items: center;
}
.tl-header { border-bottom: 1px solid #F3F0F5; padding: 0 8px; }
.tl-header > div {
    padding: 10px 8px; font-size: 10.5px; font-weight: 700;
    letter-spacing: 0.09em; text-transform: uppercase; color: #B0A8B8;
}
.tl-body { position: relative; }
.tl-row { padding: 0 8px; border-bottom: 1px solid #FAF8FB; transition: background 0.1s; }
.tl-row:last-child { border-bottom: none; }
.tl-row:hover { background: #FAFAFA; }
.tl-row.is-current { background: #FFF8F9; }
.tl-row.is-current:hover { background: #FFF3F5; }

.tl-col-num { padding: 8px; }
.tl-num-cell {
    position: relative; display: flex; align-items: center;
    justify-content: center; height: 40px;
}
.row-idx {
    position: absolute; font-size: 13px; color: #9C94A8;
    transition: opacity 0.12s; font-variant-numeric: tabular-nums; line-height: 1;
}
.row-idx.is-rose { color: #C9637A; font-weight: 600; }
.row-play {
    position: absolute; opacity: 0; pointer-events: none;
    width: 28px; height: 28px; border-radius: 50%; border: none; background: none;
    display: flex; align-items: center; justify-content: center;
    color: #1A1523; cursor: pointer; transition: opacity 0.12s; padding: 0;
}
.tl-row:hover .row-idx        { opacity: 0; }
.tl-row:hover .playing-bars   { opacity: 0; }
.tl-row:hover .row-play       { opacity: 1; pointer-events: auto; }
.tl-row.is-current .row-play  { color: #C9637A; }

.playing-bars {
    position: absolute; display: flex; align-items: flex-end;
    gap: 2px; height: 14px; transition: opacity 0.12s;
}
.playing-bars i {
    display: block; width: 3px; border-radius: 2px; background: #C9637A;
    height: 4px; animation: bar-bounce 0.9s ease-in-out infinite;
}
.playing-bars i:nth-child(2) { animation-delay: 0.18s; }
.playing-bars i:nth-child(3) { animation-delay: 0.36s; }
@keyframes bar-bounce {
    0%, 100% { height: 4px; }
    50%       { height: 14px; }
}

.tl-col-title { padding: 8px; }
.tl-title-cell { display: flex; align-items: center; gap: 12px; min-width: 0; }
.tl-thumb {
    width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0;
    overflow: hidden; background: #F3F0F5; display: flex; align-items: center; justify-content: center;
}
.tl-thumb-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tl-thumb-empty { color: #C4BDD0; }
.tl-name-wrap { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.tl-name {
    font-size: 14px; font-weight: 500; color: #1A1523;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.15s;
}
.tl-name.is-rose { color: #C9637A; }
.tl-artist { font-size: 12px; color: #9C94A8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.tl-col-mood { padding: 8px; }
.tl-mood-tag {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px 3px 7px; border-radius: 20px;
    font-size: 11.5px; font-weight: 500; text-transform: capitalize; white-space: nowrap;
}
.tl-mood-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.tl-col-date { padding: 8px; }
.tl-col-dur  { padding: 8px; display: flex; align-items: center; }
.tl-date, .tl-dur { font-size: 12.5px; color: #9C94A8; font-variant-numeric: tabular-nums; }
.tl-col-actions { padding: 8px; }
.tl-actions { display: flex; align-items: center; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.tl-row:hover .tl-actions { opacity: 1; }
.tl-act {
    width: 30px; height: 30px; border-radius: 8px; border: none; background: none;
    display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.12s, color 0.12s;
}
.tl-act.edit  { color: #7A6D92; }
.tl-act.edit:hover  { background: #EDE9F0; color: #4C3F73; }
.tl-act.delete { color: #B0A8B8; }
.tl-act.delete:hover { background: #FFF1F1; color: #C0392B; }

.tl-empty { display: flex; flex-direction: column; align-items: center; padding: 56px 24px; gap: 10px; }
.tl-empty-icon {
    width: 56px; height: 56px; border-radius: 50%; background: #F7F5F9;
    display: flex; align-items: center; justify-content: center; color: #C4BDD0; margin-bottom: 4px;
}
.tl-empty-icon svg { width: 26px; height: 26px; }
.tl-empty-title { font-size: 15px; font-weight: 600; color: #4C3F73; }
.tl-empty-sub   { font-size: 13px; color: #9C94A8; }

.tl-item-enter-active { transition: opacity 0.2s, transform 0.2s; }
.tl-item-leave-active { transition: opacity 0.15s; }
.tl-item-enter-from   { opacity: 0; transform: translateY(6px); }
.tl-item-leave-to     { opacity: 0; }

/* ═══════════════════════════════════════════════════════════
   TOAST
═══════════════════════════════════════════════════════════ */
.toast-wrap {
    position: fixed; top: 20px; right: 24px; z-index: 9999;
    display: flex; align-items: center; gap: 8px; padding: 10px 16px;
    border-radius: 10px; font-size: 13px; font-weight: 500;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15); pointer-events: none;
}
.toast-wrap.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.toast-wrap.error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.toast-icon { width: 16px; height: 16px; flex-shrink: 0; }
.toast-slide-enter-active { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s; }
.toast-slide-leave-active { transition: transform 0.2s ease, opacity 0.2s; }
.toast-slide-enter-from   { transform: translateY(-10px) scale(0.95); opacity: 0; }
.toast-slide-leave-to     { transform: translateY(-6px); opacity: 0; }

/* Modal backdrop */
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ═══════════════════════════════════════════════════════════
   SPOTIFY BOTTOM PLAYER BAR
═══════════════════════════════════════════════════════════ */

/* Slide up from bottom */
.player-slide-enter-active { transition: transform 0.38s cubic-bezier(0.34, 1.3, 0.64, 1), opacity 0.25s ease; }
.player-slide-leave-active { transition: transform 0.22s cubic-bezier(0.4, 0, 1, 1), opacity 0.18s ease; }
.player-slide-enter-from   { transform: translateY(100%); opacity: 0; }
.player-slide-leave-to     { transform: translateY(100%); opacity: 0; }

.track-switch-enter-active { transition: transform 0.25s cubic-bezier(0.34, 1.3, 0.64, 1), opacity 0.2s; }
.track-switch-leave-active { transition: transform 0.15s ease, opacity 0.12s; }
.track-switch-enter-from   { transform: translateY(8px); opacity: 0; }
.track-switch-leave-to     { transform: translateY(-6px); opacity: 0; }

.cover-swap-enter-active, .cover-swap-leave-active { transition: opacity 0.25s; }
.cover-swap-enter-from, .cover-swap-leave-to { opacity: 0; }

/* Content spacer so tracks don't hide under bar */
.sp-bar-spacer { height: 80px; }

/* Bar shell */
.sp-bar {
    position: fixed;
    bottom: 0; left: var(--admin-sidebar-w, 0px); right: 0;
    height: 80px;
    z-index: 200;
    display: grid;
    grid-template-columns: minmax(180px, 30%) 1fr minmax(160px, 26%);
    align-items: center;
    gap: 16px;
    padding: 0 24px;
    padding-bottom: env(safe-area-inset-bottom, 0px);
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-top: 1px solid #EDE9F0;
    box-shadow: 0 -2px 24px rgba(0, 0, 0, 0.06);
}

/* Rose shimmer line on top when playing */
.sp-bar::before {
    content: '';
    position: absolute; top: -1px; left: 0; right: 0; height: 2px;
    background: transparent; transition: background 0.4s;
    pointer-events: none;
}
.sp-bar.is-playing::before {
    background: linear-gradient(90deg, transparent 0%, #C9637A 20%, #E88FA0 50%, #C9637A 80%, transparent 100%);
}

/* ── Left ── */
.sp-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.sp-thumb {
    width: 52px; height: 52px; border-radius: 10px; flex-shrink: 0;
    overflow: hidden; background: #F0ECF3;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.12);
}
.sp-thumb-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sp-thumb-empty { color: #C4BDD0; }
.sp-info { min-width: 0; flex: 1; }
.sp-title {
    display: block; font-size: 13px; font-weight: 600; color: #1A1523;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sp-artist {
    display: block; font-size: 11.5px; color: #9C94A8; margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sp-mood-badge {
    padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 600;
    text-transform: capitalize; white-space: nowrap; flex-shrink: 0;
}

/* ── Center ── */
.sp-center { display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0; }
.sp-controls { display: flex; align-items: center; gap: 6px; }
.sp-skip {
    width: 32px; height: 32px; border-radius: 50%; border: none; background: none;
    display: flex; align-items: center; justify-content: center;
    color: #7A6D92; cursor: pointer; transition: color 0.15s, background 0.15s;
}
.sp-skip:hover { color: #1A1523; background: #F0ECF3; }
.sp-play {
    width: 38px; height: 38px; border-radius: 50%; border: none;
    background: #1A1523; color: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background 0.15s, transform 0.1s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.18);
}
.sp-play:hover { background: #2D2740; }
.sp-play:active { transform: scale(0.93); }
.sp-seek-row { display: flex; align-items: center; gap: 8px; width: 100%; }
.sp-time {
    font-family: 'Courier New', 'SF Mono', monospace; font-size: 10.5px;
    color: #9C94A8; font-variant-numeric: tabular-nums; white-space: nowrap; flex-shrink: 0;
}
.sp-seek {
    flex: 1; height: 18px; display: flex; align-items: center; cursor: pointer;
}
.sp-seek-track {
    width: 100%; height: 4px; background: #EDE9F0; border-radius: 2px;
    position: relative; overflow: hidden; transition: height 0.15s;
}
.sp-seek:hover .sp-seek-track { height: 5px; }
.sp-seek-fill {
    height: 100%; background: #C9637A; border-radius: inherit; transition: width 0.1s linear;
}

/* ── Right ── */
.sp-right { display: flex; align-items: center; gap: 8px; justify-content: flex-end; min-width: 0; }
.sp-vol-btn {
    width: 28px; height: 28px; border: none; background: none; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    color: #9C94A8; cursor: pointer; transition: color 0.15s;
}
.sp-vol-btn:hover { color: #1A1523; }
.sp-vol {
    width: 90px; flex-shrink: 0;
    -webkit-appearance: none; appearance: none;
    height: 3px; border-radius: 2px; background: #EDE9F0; outline: none; cursor: pointer;
    background-image: linear-gradient(#C9637A, #C9637A);
    background-size: v-bind('`${(isMuted ? 0 : volume) * 100}% 100%`');
    background-repeat: no-repeat;
}
.sp-vol::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; border-radius: 50%; background: #C9637A; cursor: pointer; box-shadow: 0 0 3px rgba(201,99,122,0.4); }
.sp-vol::-moz-range-thumb { width: 12px; height: 12px; border: none; border-radius: 50%; background: #C9637A; cursor: pointer; }
.sp-close {
    width: 28px; height: 28px; border-radius: 50%; border: none; background: #F0ECF3;
    display: flex; align-items: center; justify-content: center;
    color: #9C94A8; cursor: pointer; flex-shrink: 0; transition: background 0.15s, color 0.15s;
}
.sp-close:hover { background: #EDE9F0; color: #1A1523; }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════ */
@media (max-width: 1023px) {
    .sp-bar { left: 0; }
}

@media (max-width: 639px) {
    .lib-hero { padding: 20px 18px 18px; gap: 14px; }
    .lib-hero-icon { width: 52px; height: 52px; border-radius: 13px; }
    .lib-hero-icon svg { width: 26px; height: 26px; }
    .lib-hero-title { font-size: 26px; }
    .lib-hero-btn { padding: 9px 16px; font-size: 12px; }
    .tl-header, .tl-row { grid-template-columns: 40px 1fr 72px; }
    .tl-col-mood, .tl-col-date, .tl-col-dur { display: none; }

    .sp-bar {
        height: 64px;
        grid-template-columns: 1fr auto auto;
        gap: 8px;
        padding: 0 14px;
    }
    .sp-bar-spacer { height: 64px; }
    .sp-center { flex-direction: row; gap: 2px; }
    .sp-seek-row { display: none; }
    .sp-mood-badge { display: none; }
    .sp-vol, .sp-vol-btn { display: none; }
    .sp-thumb { width: 42px; height: 42px; }
}

@media (max-width: 767px) and (min-width: 640px) {
    .sp-bar { grid-template-columns: minmax(160px, 35%) 1fr auto; }
    .sp-vol { width: 70px; }
    .sp-mood-badge { display: none; }
}

@media (prefers-reduced-motion: reduce) {
    .player-slide-enter-active, .player-slide-leave-active,
    .track-switch-enter-active, .track-switch-leave-active { transition: opacity 0.15s; }
    .player-slide-enter-from, .player-slide-leave-to,
    .track-switch-enter-from,  .track-switch-leave-to { transform: none; }
    .sp-bar.is-playing::before { animation: none; }
    .playing-bars i { animation: none; height: 8px; }
}
</style>
