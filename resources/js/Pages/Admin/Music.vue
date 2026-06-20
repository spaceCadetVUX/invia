<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    tracks:      { type: Array, required: true },
    collections: { type: Array, default: () => [] },
})

// ── Tabs ─────────────────────────────────────────────────────
const activeTab = ref('library')

// ── Upload form ──────────────────────────────────────────────
const showForm  = ref(false)
const uploading = ref(false)
const form      = ref({ title: '', artist: '', mood: 'romantic', file: null, cover_image: null })
const fileInput = ref(null)

function selectFile(e) { form.value.file = e.target.files[0] ?? null }

function submitUpload() {
    if (!form.value.file) return
    uploading.value = true
    router.post(route('admin.music.store'), {
        file: form.value.file, title: form.value.title,
        artist: form.value.artist, mood: form.value.mood, cover_image: form.value.cover_image,
    }, {
        forceFormData: true, preserveScroll: true, preserveState: true,
        onSuccess: () => {
            showForm.value = false; uploading.value = false
            form.value = { title: '', artist: '', mood: 'romantic', file: null, cover_image: null }
            router.reload({ only: ['tracks'] })
            showToast('Upload thành công!')
        },
        onError: () => { uploading.value = false },
    })
}

// ── Edit / Delete tracks ─────────────────────────────────────
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
    router.post(route('admin.music.update', editTarget.value.id), { ...editForm.value, _method: 'PATCH' }, {
        forceFormData: true, preserveScroll: true, preserveState: true,
        onSuccess: () => {
            editTarget.value = null; editSaving.value = false
            router.reload({ only: ['tracks'] }); showToast('Đã lưu thay đổi.')
        },
        onError: () => { editSaving.value = false },
    })
}

function deleteTrack() {
    const id = deleteTarget.value.id
    router.delete(route('admin.music.destroy', id), {
        preserveScroll: true, preserveState: true,
        onSuccess: () => {
            if (playingId.value === id) closePlayer()
            deleteTarget.value = null
            router.reload({ only: ['tracks'] }); showToast('Đã xóa bài nhạc.', 'error')
        },
    })
}

// ── Audio player ─────────────────────────────────────────────
const playingId     = ref(null)
const isPlaying     = ref(false)
const audioProgress = ref(0)
const audioDuration = ref(0)
const volume        = ref(parseFloat(localStorage.getItem('player_volume') ?? '0.8'))
const isMuted       = ref(localStorage.getItem('player_muted') === '1')
const playContext   = ref(null) // null = library, array = collection

let audioEl = null, rafId = null

const currentTrack = computed(() => props.tracks.find(t => t.id === playingId.value) ?? null)
const trackList    = computed(() => playContext.value ?? props.tracks)
const currentIdx   = computed(() => trackList.value.findIndex(t => t.id === playingId.value))

function fmtTime(secs) {
    if (!secs || isNaN(secs)) return '0:00'
    const s = Math.floor(secs)
    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`
}

function animLoop() {
    if (!isPlaying.value) return
    if (audioEl && audioDuration.value > 0) audioProgress.value = audioEl.currentTime / audioDuration.value
    rafId = requestAnimationFrame(animLoop)
}

function stopAudio() {
    cancelAnimationFrame(rafId)
    if (audioEl) { audioEl.pause(); audioEl = null }
    isPlaying.value = false
}

function playTrack(track) {
    stopAudio()
    playingId.value = track.id; isPlaying.value = true
    audioProgress.value = 0; audioDuration.value = track.duration ?? 0
    audioEl = new Audio(route('admin.music.stream', track.id))
    audioEl.volume = isMuted.value ? 0 : volume.value
    audioEl.addEventListener('loadedmetadata', () => {
        if (audioEl?.duration && isFinite(audioEl.duration)) audioDuration.value = audioEl.duration
    })
    audioEl.play().catch(() => {})
    audioEl.addEventListener('ended', () => {
        isPlaying.value = false; audioProgress.value = 1; cancelAnimationFrame(rafId)
        const next = trackList.value[currentIdx.value + 1]
        if (next) playTrack(next)
    })
    rafId = requestAnimationFrame(animLoop)
}

function togglePlay(track) {
    if (playingId.value === track.id) {
        if (isPlaying.value) { audioEl?.pause(); isPlaying.value = false; cancelAnimationFrame(rafId) }
        else { audioEl?.play().catch(() => {}); isPlaying.value = true; rafId = requestAnimationFrame(animLoop) }
        return
    }
    playContext.value = null
    playTrack(track)
}

function playCollectionTrack(track) {
    if (playingId.value === track.id) {
        if (isPlaying.value) { audioEl?.pause(); isPlaying.value = false; cancelAnimationFrame(rafId) }
        else { audioEl?.play().catch(() => {}); isPlaying.value = true; rafId = requestAnimationFrame(animLoop) }
        return
    }
    playContext.value = [...localTracks.value]
    playTrack(track)
}

function playerToggle() {
    if (!currentTrack.value) return
    if (isPlaying.value) { audioEl?.pause(); isPlaying.value = false; cancelAnimationFrame(rafId) }
    else { audioEl?.play().catch(() => {}); isPlaying.value = true; rafId = requestAnimationFrame(animLoop) }
}

function playNext() { const n = trackList.value[currentIdx.value + 1]; if (n) playTrack(n) }
function playPrev() {
    if (audioEl && audioEl.currentTime > 3) { audioEl.currentTime = 0; audioProgress.value = 0; return }
    const p = trackList.value[currentIdx.value - 1]; if (p) playTrack(p)
}

function scrubSeek(e) {
    const ratio = Math.max(0, Math.min(1, (e.clientX - e.currentTarget.getBoundingClientRect().left) / e.currentTarget.getBoundingClientRect().width))
    audioProgress.value = ratio
    if (audioEl && audioDuration.value > 0) audioEl.currentTime = ratio * audioDuration.value
}

function toggleMute() {
    isMuted.value = !isMuted.value
    if (audioEl) audioEl.volume = isMuted.value ? 0 : volume.value
}

watch(volume, v => {
    if (audioEl) audioEl.volume = isMuted.value ? 0 : v
    if (isMuted.value && v > 0) isMuted.value = false
    localStorage.setItem('player_volume', v)
})
watch(isMuted, v => localStorage.setItem('player_muted', v ? '1' : '0'))

function closePlayer() { stopAudio(); playingId.value = null; audioProgress.value = 0 }
onBeforeUnmount(() => stopAudio())

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
    romantic:    { bg: '#EAF5F8', text: '#005F7F', dot: '#0081A7' },
    classical:   { bg: '#EFF6FF', text: '#1E40AF', dot: '#3B82F6' },
    acoustic:    { bg: '#F0FDF4', text: '#166534', dot: '#22C55E' },
    traditional: { bg: '#FFFBEB', text: '#92400E', dot: '#F59E0B' },
}

// ── Collections ──────────────────────────────────────────────
const activeCollection   = ref(null)
const showColForm        = ref(false)
const editColTarget      = ref(null)
const colForm            = ref({ name: '', description: '', cover_image: null })
const colSaving          = ref(false)
const deleteColTarget    = ref(null)
const localTracks        = ref([])
const showAddModal       = ref(false)
const selectedAdd        = ref(new Set())
const addSearch          = ref('')

watch(() => props.collections, newCols => {
    if (!activeCollection.value) return
    const updated = newCols.find(c => c.id === activeCollection.value.id)
    if (updated) { activeCollection.value = updated; localTracks.value = [...updated.tracks] }
    else { activeCollection.value = null; localTracks.value = [] }
}, { deep: true })

function openCollection(col) {
    activeCollection.value = col
    localTracks.value = [...col.tracks]
}

function closeCollection() {
    activeCollection.value = null; localTracks.value = []
}

function colCoverUrl(col) {
    return col.cover_image ? route('admin.music.collections.cover', col.id) : null
}

function openColForm() { colForm.value = { name: '', description: '', cover_image: null }; showColForm.value = true }
function openEditCol(col) { editColTarget.value = col; colForm.value = { name: col.name, description: col.description ?? '', cover_image: null } }

function submitCollection() {
    colSaving.value = true
    router.post(route('admin.music.collections.store'), colForm.value, {
        forceFormData: true, preserveScroll: true, preserveState: true,
        onSuccess: () => {
            showColForm.value = false; colSaving.value = false
            colForm.value = { name: '', description: '', cover_image: null }
            router.reload({ only: ['collections'] }); showToast('Collection đã tạo!')
        },
        onError: () => { colSaving.value = false },
    })
}

function submitEditCol() {
    if (!editColTarget.value) return
    colSaving.value = true
    router.post(route('admin.music.collections.update', editColTarget.value.id), { ...colForm.value, _method: 'PATCH' }, {
        forceFormData: true, preserveScroll: true, preserveState: true,
        onSuccess: () => {
            editColTarget.value = null; colSaving.value = false
            router.reload({ only: ['collections'] }); showToast('Đã cập nhật collection.')
        },
        onError: () => { colSaving.value = false },
    })
}

function deleteCollection() {
    const id = deleteColTarget.value.id
    router.delete(route('admin.music.collections.destroy', id), {
        preserveScroll: true, preserveState: true,
        onSuccess: () => {
            if (activeCollection.value?.id === id) closeCollection()
            deleteColTarget.value = null
            router.reload({ only: ['collections'] }); showToast('Đã xóa collection.', 'error')
        },
    })
}

const availableToAdd = computed(() => {
    if (!activeCollection.value) return []
    const inCol = new Set(localTracks.value.map(t => t.id))
    const q = addSearch.value.toLowerCase().trim()
    return props.tracks.filter(t => !inCol.has(t.id) && (!q || t.title.toLowerCase().includes(q) || (t.artist ?? '').toLowerCase().includes(q)))
})

function toggleAdd(id) {
    const s = new Set(selectedAdd.value)
    s.has(id) ? s.delete(id) : s.add(id)
    selectedAdd.value = s
}

function confirmAdd() {
    if (!selectedAdd.value.size) return
    router.post(route('admin.music.collections.tracks.add', activeCollection.value.id), {
        track_ids: [...selectedAdd.value],
    }, {
        preserveScroll: true, preserveState: true,
        onSuccess: () => {
            showAddModal.value = false; selectedAdd.value = new Set(); addSearch.value = ''
            router.reload({ only: ['collections'] }); showToast('Đã thêm bài nhạc.')
        },
    })
}

function removeFromCollection(track) {
    router.delete(route('admin.music.collections.tracks.remove', { collection: activeCollection.value.id, track: track.id }), {
        preserveScroll: true, preserveState: true,
        onSuccess: () => {
            localTracks.value = localTracks.value.filter(t => t.id !== track.id)
            if (playingId.value === track.id) closePlayer()
            router.reload({ only: ['collections'] }); showToast('Đã xóa khỏi collection.')
        },
    })
}

// Drag & drop reorder
let dragSrcIdx = null, reorderTimer = null

function onDragStart(e, idx) {
    dragSrcIdx = idx; e.dataTransfer.effectAllowed = 'move'
}

function onDragOver(e, idx) {
    e.preventDefault()
    if (dragSrcIdx === null || dragSrcIdx === idx) return
    const arr = [...localTracks.value]
    const [moved] = arr.splice(dragSrcIdx, 1)
    arr.splice(idx, 0, moved)
    localTracks.value = arr; dragSrcIdx = idx
}

function onDrop() {
    clearTimeout(reorderTimer)
    reorderTimer = setTimeout(() => {
        router.post(route('admin.music.collections.reorder', activeCollection.value.id), {
            order: localTracks.value.map(t => t.id),
        }, { preserveScroll: true, preserveState: true })
    }, 500)
}

function onDragEnd() { dragSrcIdx = null }

function playCollection() {
    if (!localTracks.value.length) return
    playContext.value = [...localTracks.value]
    playTrack(localTracks.value[0])
}
</script>

<template>
    <Head title="Admin — Music" />
    <AdminLayout>

        <!-- ── Tab bar ────────────────────────────────────────── -->
        <div class="lib-tab-bar">
            <button class="lib-tab" :class="{ active: activeTab === 'library' }" @click="activeTab = 'library'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg>
                Thư viện
                <span class="lib-tab-count">{{ tracks.length }}</span>
            </button>
            <button class="lib-tab" :class="{ active: activeTab === 'collections' }" @click="activeTab = 'collections'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v2a2.25 2.25 0 0 1-2.25 2.25H4.25A2.25 2.25 0 0 1 2 6.25v-2ZM2 11.25A2.25 2.25 0 0 1 4.25 9h11.5A2.25 2.25 0 0 1 18 11.25v2a2.25 2.25 0 0 1-2.25 2.25H4.25A2.25 2.25 0 0 1 2 13.25v-2Z" /></svg>
                Collections
                <span class="lib-tab-count">{{ collections.length }}</span>
            </button>
        </div>

        <!-- ════════════ LIBRARY TAB ════════════ -->
        <div v-if="activeTab === 'library'">

            <!-- Hero -->
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

            <!-- Filters -->
            <div class="lib-filters">
                <div class="lib-search-wrap">
                    <svg class="lib-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                    <input v-model="search" class="lib-search-input" placeholder="Tìm bài nhạc, nghệ sĩ…" />
                </div>
                <div class="lib-mood-pills">
                    <button class="lib-mood-btn" :class="{ active: !moodFilter }" @click="moodFilter = ''">Tất cả</button>
                    <button v-for="m in MOODS" :key="m" class="lib-mood-btn" :class="{ active: moodFilter === m }"
                        :style="moodFilter === m ? { background: MOOD_COLORS[m]?.bg, color: MOOD_COLORS[m]?.text, borderColor: MOOD_COLORS[m]?.dot } : {}"
                        @click="moodFilter = m">{{ m }}</button>
                </div>
            </div>

            <!-- Track list -->
            <div class="lib-tracklist">
                <div class="tl-header">
                    <div class="tl-col-num">#</div>
                    <div class="tl-col-title">Title</div>
                    <div class="tl-col-mood">Mood</div>
                    <div class="tl-col-date">Added</div>
                    <div class="tl-col-dur">Duration</div>
                    <div class="tl-col-actions" />
                </div>
                <TransitionGroup name="tl-item" tag="div" class="tl-body">
                    <div v-for="(track, idx) in filteredTracks" :key="track.id" class="tl-row" :class="{ 'is-current': playingId === track.id }">
                        <div class="tl-col-num tl-num-cell">
                            <span v-if="playingId === track.id && isPlaying" class="playing-bars" aria-hidden="true"><i /><i /><i /></span>
                            <span v-else class="row-idx" :class="{ 'is-rose': playingId === track.id }">{{ idx + 1 }}</span>
                            <button class="row-play" @click="togglePlay(track)">
                                <svg v-if="!(playingId === track.id && isPlaying)" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                <svg v-else viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z" /></svg>
                            </button>
                        </div>
                        <div class="tl-col-title tl-title-cell">
                            <div class="tl-thumb">
                                <img v-if="track.cover_image" :src="route('admin.music.cover', track.id)" class="tl-thumb-img" />
                                <div v-else class="tl-thumb-empty"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg></div>
                            </div>
                            <div class="tl-name-wrap">
                                <span class="tl-name" :class="{ 'is-rose': playingId === track.id }">{{ track.title }}</span>
                                <span class="tl-artist">{{ track.artist ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="tl-col-mood">
                            <span class="tl-mood-tag" :style="{ background: MOOD_COLORS[track.mood]?.bg ?? '#F3F4F6', color: MOOD_COLORS[track.mood]?.text ?? '#374151' }">
                                <span class="tl-mood-dot" :style="{ background: MOOD_COLORS[track.mood]?.dot ?? '#9CA3AF' }" />
                                {{ track.mood }}
                            </span>
                        </div>
                        <div class="tl-col-date tl-date">{{ track.created_at?.slice(0, 10) }}</div>
                        <div class="tl-col-dur tl-dur">{{ (playingId === track.id && audioDuration > 0) ? fmtTime(audioDuration) : (track.duration > 0 ? fmtTime(track.duration) : '—') }}</div>
                        <div class="tl-col-actions tl-actions">
                            <button class="tl-act edit" @click="openEdit(track)" title="Sửa thông tin">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" /></svg>
                            </button>
                            <button class="tl-act delete" @click="deleteTarget = track" title="Xóa bài nhạc">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                </TransitionGroup>
                <div v-if="filteredTracks.length === 0" class="tl-empty">
                    <div class="tl-empty-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg></div>
                    <p class="tl-empty-title">{{ search || moodFilter ? 'Không tìm thấy bài nào' : 'Chưa có bài nhạc nào' }}</p>
                    <p class="tl-empty-sub">{{ search || moodFilter ? 'Thử từ khoá khác hoặc bỏ bộ lọc' : 'Nhấn "Upload nhạc" để thêm bài đầu tiên' }}</p>
                </div>
            </div>
        </div>

        <!-- ════════════ COLLECTIONS TAB ════════════ -->
        <div v-else>

            <!-- Collections grid -->
            <div v-if="!activeCollection">
                <div class="col-page-hd">
                    <div>
                        <h2 class="col-page-title">Collections</h2>
                        <p class="col-page-sub">{{ collections.length }} bộ sưu tập · Nhóm nhạc theo event hoặc mood</p>
                    </div>
                    <button class="lib-hero-btn" @click="openColForm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;flex-shrink:0"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                        New Collection
                    </button>
                </div>

                <div v-if="collections.length === 0" class="col-empty">
                    <div class="col-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v2a2.25 2.25 0 0 1-2.25 2.25H4.25A2.25 2.25 0 0 1 2 6.25v-2ZM2 11.25A2.25 2.25 0 0 1 4.25 9h11.5A2.25 2.25 0 0 1 18 11.25v2a2.25 2.25 0 0 1-2.25 2.25H4.25A2.25 2.25 0 0 1 2 13.25v-2Z" /></svg>
                    </div>
                    <p class="tl-empty-title">Chưa có collection nào</p>
                    <p class="tl-empty-sub">Tạo collection để nhóm nhạc theo event hoặc mood</p>
                    <button class="lib-hero-btn" style="margin-top:12px" @click="openColForm">+ Tạo collection đầu tiên</button>
                </div>

                <div v-else class="col-grid">
                    <div v-for="col in collections" :key="col.id" class="col-card" @click="openCollection(col)">
                        <div class="col-card-art">
                            <img v-if="col.cover_image" :src="colCoverUrl(col)" class="col-card-img" />
                            <div v-else class="col-card-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:40px;height:40px;color:rgba(255,255,255,0.7)"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg>
                            </div>
                            <div class="col-card-overlay">
                                <button class="col-card-play-btn" @click.stop="openCollection(col); playCollection()">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:22px;height:22px;margin-left:2px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                </button>
                            </div>
                            <div class="col-card-menu">
                                <button class="col-card-menu-btn" @click.stop="openEditCol(col)" title="Sửa">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" /></svg>
                                </button>
                                <button class="col-card-menu-btn danger" @click.stop="deleteColTarget = col" title="Xóa">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4Z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        </div>
                        <p class="col-card-name">{{ col.name }}</p>
                        <p class="col-card-meta">{{ col.tracks_count }} bài{{ col.description ? ' · ' + col.description : '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Collection detail -->
            <div v-else>
                <button class="col-back-btn" @click="closeCollection">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" /></svg>
                    Collections
                </button>

                <!-- Detail hero -->
                <div class="col-detail-hero">
                    <div class="col-detail-cover">
                        <img v-if="activeCollection.cover_image" :src="colCoverUrl(activeCollection)" class="col-detail-cover-img" />
                        <div v-else class="col-detail-cover-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:64px;height:64px;color:rgba(255,255,255,0.75)"><path d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" /></svg>
                        </div>
                    </div>
                    <div class="col-detail-info">
                        <span class="col-detail-label">Collection</span>
                        <h1 class="col-detail-title">{{ activeCollection.name }}</h1>
                        <p v-if="activeCollection.description" class="col-detail-desc">{{ activeCollection.description }}</p>
                        <p class="col-detail-meta">{{ localTracks.length }} bài nhạc</p>
                        <div class="col-detail-actions">
                            <button class="col-play-all-btn" :disabled="!localTracks.length" @click="playCollection">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;margin-left:2px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                Play All
                            </button>
                            <button class="col-action-btn" @click="showAddModal = true; selectedAdd = new Set(); addSearch = ''">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                                Add Tracks
                            </button>
                            <button class="col-action-btn" @click="openEditCol(activeCollection)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" /></svg>
                                Edit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tracks in collection -->
                <div class="col-tl">
                    <div class="col-tl-header">
                        <div class="col-tl-drag" />
                        <div class="col-tl-num">#</div>
                        <div class="col-tl-title">Title</div>
                        <div class="col-tl-dur"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" /></svg></div>
                        <div class="col-tl-rm" />
                    </div>

                    <div v-if="localTracks.length === 0" class="tl-empty" style="padding:40px 24px">
                        <p class="tl-empty-title">Collection trống</p>
                        <p class="tl-empty-sub">Nhấn "Add Tracks" để thêm bài nhạc</p>
                    </div>

                    <div v-for="(track, idx) in localTracks" :key="track.id"
                        class="col-tl-row" :class="{ 'is-current': playingId === track.id }"
                        draggable="true"
                        @dragstart="onDragStart($event, idx)"
                        @dragover="onDragOver($event, idx)"
                        @drop="onDrop"
                        @dragend="onDragEnd">

                        <div class="col-tl-drag col-drag-handle" title="Kéo để sắp xếp">
                            <svg viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;color:#9CA3AF"><circle cx="5" cy="4" r="1.2"/><circle cx="5" cy="8" r="1.2"/><circle cx="5" cy="12" r="1.2"/><circle cx="11" cy="4" r="1.2"/><circle cx="11" cy="8" r="1.2"/><circle cx="11" cy="12" r="1.2"/></svg>
                        </div>

                        <div class="col-tl-num col-tl-num-cell">
                            <span v-if="playingId === track.id && isPlaying" class="playing-bars"><i /><i /><i /></span>
                            <span v-else class="row-idx" :class="{ 'is-rose': playingId === track.id }">{{ idx + 1 }}</span>
                            <button class="row-play" @click="playCollectionTrack(track)">
                                <svg v-if="!(playingId === track.id && isPlaying)" viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                <svg v-else viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px"><path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z" /></svg>
                            </button>
                        </div>

                        <div class="col-tl-title col-tl-title-cell">
                            <div class="tl-thumb" style="width:36px;height:36px">
                                <img v-if="track.cover_image" :src="route('admin.music.cover', track.id)" class="tl-thumb-img" />
                                <div v-else class="tl-thumb-empty"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg></div>
                            </div>
                            <div class="tl-name-wrap">
                                <span class="tl-name" :class="{ 'is-rose': playingId === track.id }">{{ track.title }}</span>
                                <span class="tl-artist">{{ track.artist ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="col-tl-dur tl-dur">{{ track.duration ? fmtTime(track.duration) : '—' }}</div>

                        <div class="col-tl-rm">
                            <button class="col-rm-btn" @click="removeFromCollection(track)" title="Xóa khỏi collection">
                                <svg viewBox="0 0 12 12" fill="none" style="width:10px;height:10px"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spacer for player bar -->
        <div v-if="currentTrack" class="sp-bar-spacer" aria-hidden="true" />

        <!-- ══ Upload modal ══ -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
                    <div class="absolute inset-0 bg-black/50" @click="showForm = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">Upload nhạc</h3>
                        <div class="space-y-3">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">File MP3 (tối đa 15MB)</label>
                                <input ref="fileInput" type="file" accept=".mp3" @change="selectFile" class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ảnh bìa (tuỳ chọn)</label>
                                <input type="file" accept="image/*" @change="e => form.cover_image = e.target.files[0] ?? null" class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tên bài</label>
                                <input v-model="form.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nghệ sĩ</label>
                                <input v-model="form.artist" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Mood</label>
                                <select v-model="form.mood" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]">
                                    <option v-for="m in MOODS" :key="m" :value="m">{{ m }}</option>
                                </select></div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="showForm = false" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitUpload" :disabled="uploading" class="px-4 py-2 text-sm bg-[#0081A7] text-white rounded-md hover:bg-[#006A8E] disabled:opacity-50">{{ uploading ? 'Đang upload...' : 'Upload' }}</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ Toast ══ -->
        <Teleport to="body">
            <Transition name="toast-slide">
                <div v-if="toast" class="toast-wrap" :class="toast.type">
                    <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="toast-icon"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="toast-icon"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                    <span>{{ toast.message }}</span>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ Collection form modal ══ -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showColForm || editColTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showColForm = false; editColTarget = null">
                    <div class="absolute inset-0 bg-black/50" @click="showColForm = false; editColTarget = null" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">{{ editColTarget ? 'Sửa collection' : 'New Collection' }}</h3>
                        <div class="space-y-3">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tên collection *</label>
                                <input v-model="colForm.name" placeholder="VD: Đám cưới Minh-Lan, Gala 2025…" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Mô tả (tuỳ chọn)</label>
                                <textarea v-model="colForm.description" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7] resize-none" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ảnh bìa (tuỳ chọn)</label>
                                <div v-if="editColTarget?.cover_image" class="mb-2"><img :src="colCoverUrl(editColTarget)" class="h-16 w-16 rounded-lg object-cover border border-gray-200" /></div>
                                <input type="file" accept="image/*" @change="e => colForm.cover_image = e.target.files[0] ?? null" class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#EAF5F8] file:text-[#005F7F]" /></div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="showColForm = false; editColTarget = null" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="editColTarget ? submitEditCol() : submitCollection()" :disabled="colSaving || !colForm.name.trim()" class="px-4 py-2 text-sm bg-[#0081A7] text-white rounded-md hover:bg-[#006A8E] disabled:opacity-50">
                                {{ colSaving ? 'Đang lưu...' : (editColTarget ? 'Lưu' : 'Tạo') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ Add tracks modal ══ -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showAddModal = false">
                    <div class="absolute inset-0 bg-black/50" @click="showAddModal = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg flex flex-col" style="max-height:80vh">
                        <div class="p-5 border-b border-gray-100">
                            <h3 class="text-lg font-semibold mb-3">Thêm bài nhạc</h3>
                            <div class="relative">
                                <svg class="lib-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" /></svg>
                                <input v-model="addSearch" class="lib-search-input w-full" placeholder="Tìm bài nhạc…" />
                            </div>
                        </div>
                        <div class="overflow-y-auto flex-1 p-2">
                            <div v-if="availableToAdd.length === 0" class="text-center py-8 text-sm text-gray-400">
                                {{ addSearch ? 'Không tìm thấy' : 'Tất cả bài nhạc đã có trong collection' }}
                            </div>
                            <label v-for="track in availableToAdd" :key="track.id" class="add-track-row" :class="{ selected: selectedAdd.has(track.id) }">
                                <input type="checkbox" class="add-track-check" :checked="selectedAdd.has(track.id)" @change="toggleAdd(track.id)" />
                                <div class="tl-thumb" style="width:36px;height:36px;flex-shrink:0">
                                    <img v-if="track.cover_image" :src="route('admin.music.cover', track.id)" class="tl-thumb-img" />
                                    <div v-else class="tl-thumb-empty"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg></div>
                                </div>
                                <div class="tl-name-wrap flex-1 min-w-0">
                                    <span class="tl-name">{{ track.title }}</span>
                                    <span class="tl-artist">{{ track.artist ?? '—' }}</span>
                                </div>
                                <span class="tl-dur text-xs text-gray-400">{{ track.duration ? fmtTime(track.duration) : '—' }}</span>
                            </label>
                        </div>
                        <div class="p-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ selectedAdd.size }} bài đã chọn</span>
                            <div class="flex gap-3">
                                <button @click="showAddModal = false" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                                <button @click="confirmAdd" :disabled="!selectedAdd.size" class="px-4 py-2 text-sm bg-[#0081A7] text-white rounded-md hover:bg-[#006A8E] disabled:opacity-50">Thêm {{ selectedAdd.size || '' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ Player bar ══ -->
        <Teleport to="body">
            <Transition name="player-slide">
                <div v-if="currentTrack" class="sp-bar" :class="{ 'is-playing': isPlaying }">
                    <div class="sp-left">
                        <Transition name="cover-swap" mode="out-in">
                            <div class="sp-thumb" :key="'thumb-' + currentTrack.id">
                                <img v-if="currentTrack.cover_image" :src="route('admin.music.cover', currentTrack.id)" class="sp-thumb-img" />
                                <div v-else class="sp-thumb-empty"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M18 3a1 1 0 0 0-1.196-.98l-10 2A1 1 0 0 0 6 5v9.114A4.369 4.369 0 0 0 5 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0 0 15 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3Z" /></svg></div>
                            </div>
                        </Transition>
                        <Transition name="track-switch" mode="out-in">
                            <div class="sp-info" :key="'info-' + currentTrack.id">
                                <span class="sp-title">{{ currentTrack.title }}</span>
                                <span class="sp-artist">{{ currentTrack.artist ?? '—' }}</span>
                            </div>
                        </Transition>
                        <span class="sp-mood-badge" :style="{ background: MOOD_COLORS[currentTrack.mood]?.bg, color: MOOD_COLORS[currentTrack.mood]?.text }">{{ currentTrack.mood }}</span>
                    </div>
                    <div class="sp-center">
                        <div class="sp-controls">
                            <button class="sp-skip" @click="playPrev" aria-label="Trước"><svg width="16" height="16" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="2" height="10" rx="1" fill="currentColor"/><path d="M12 3.5L5.5 7L12 10.5V3.5Z" fill="currentColor"/></svg></button>
                            <button class="sp-play" @click="playerToggle" :aria-label="isPlaying ? 'Pause' : 'Play'">
                                <svg v-if="!isPlaying" viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.84Z" /></svg>
                                <svg v-else viewBox="0 0 20 20" fill="currentColor" style="width:20px;height:20px"><path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z" /></svg>
                            </button>
                            <button class="sp-skip" @click="playNext" aria-label="Tiếp"><svg width="16" height="16" viewBox="0 0 14 14" fill="none"><rect x="11" y="2" width="2" height="10" rx="1" fill="currentColor"/><path d="M2 3.5L8.5 7L2 10.5V3.5Z" fill="currentColor"/></svg></button>
                        </div>
                        <div class="sp-seek-row">
                            <span class="sp-time">{{ fmtTime(audioProgress * audioDuration) }}</span>
                            <div class="sp-seek" @click="scrubSeek"><div class="sp-seek-track"><div class="sp-seek-fill" :style="{ width: (audioProgress * 100) + '%' }" /></div></div>
                            <span class="sp-time">{{ fmtTime(audioDuration) }}</span>
                        </div>
                    </div>
                    <div class="sp-right">
                        <button class="sp-vol-btn" @click="toggleMute">
                            <svg v-if="isMuted || volume === 0" width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="m11.5 5.5-3 3M8.5 5.5l3 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                            <svg v-else-if="volume < 0.5" width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="M10 5.5a3 3 0 0 1 0 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"/></svg>
                            <svg v-else width="16" height="16" viewBox="0 0 15 15" fill="currentColor"><path d="M8 2.5a.5.5 0 0 0-.854-.354L4.293 5H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.293l2.853 2.854A.5.5 0 0 0 8 12.5v-10Z"/><path d="M10 5.5a3 3 0 0 1 0 4M11.5 3.5a6 6 0 0 1 0 8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none"/></svg>
                        </button>
                        <input type="range" class="sp-vol" min="0" max="1" step="0.01" :value="isMuted ? 0 : volume" @input="e => { volume = parseFloat(e.target.value); isMuted = false }" />
                        <button class="sp-close" @click="closePlayer"><svg viewBox="0 0 12 12" fill="none" style="width:10px;height:10px"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ Edit track modal ══ -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="editTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="editTarget = null">
                    <div class="absolute inset-0 bg-black/50" @click="editTarget = null" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-semibold">Chỉnh sửa thông tin</h3>
                        <div class="space-y-3">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tên bài</label><input v-model="editForm.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Nghệ sĩ</label><input v-model="editForm.artist" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]" /></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Mood</label>
                                <select v-model="editForm.mood" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0081A7]"><option v-for="m in MOODS" :key="m" :value="m">{{ m }}</option></select></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ảnh bìa</label>
                                <div v-if="editTarget?.cover_image" class="mb-2"><img :src="route('admin.music.cover', editTarget.id)" class="h-16 w-16 rounded-lg object-cover border border-gray-200" /></div>
                                <input type="file" accept="image/*" @change="e => editForm.cover_image = e.target.files[0] ?? null" class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#EAF5F8] file:text-[#005F7F]" />
                                <p class="text-xs text-gray-400 mt-1">JPG/PNG/WebP, tối đa 2MB. Bỏ trống để giữ ảnh cũ.</p></div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="editTarget = null" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitEdit" :disabled="editSaving" class="px-4 py-2 text-sm bg-[#0081A7] text-white rounded-md hover:bg-[#006A8E] disabled:opacity-50">{{ editSaving ? 'Đang lưu...' : 'Lưu' }}</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Xóa bài nhạc"
            :description="`Xóa bài '${deleteTarget?.title}'? File nhạc sẽ bị xóa vĩnh viễn.`"
            danger confirm-label="Xóa"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="deleteTrack" />

        <ConfirmDialog
            :model-value="!!deleteColTarget"
            title="Xóa collection"
            :description="`Xóa collection '${deleteColTarget?.name}'? Bài nhạc bên trong không bị xóa.`"
            danger confirm-label="Xóa"
            @update:model-value="val => { if (!val) deleteColTarget = null }"
            @confirm="deleteCollection" />

    </AdminLayout>
</template>

<style scoped>
/* ── Tabs ── */
.lib-tab-bar {
    display: flex; gap: 4px; margin-bottom: 24px;
    border-bottom: 1px solid #EDE9F0; padding-bottom: 0;
}
.lib-tab {
    display: flex; align-items: center; gap: 7px; padding: 10px 16px;
    font-size: 13px; font-weight: 500; color: #7A6D92; border: none; background: none;
    cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
}
.lib-tab:hover { color: #1A1523; }
.lib-tab.active { color: #0081A7; border-bottom-color: #0081A7; }
.lib-tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 18px; padding: 0 5px;
    background: #F0F8FA; color: #0081A7; font-size: 10.5px; font-weight: 700;
    border-radius: 20px;
}
.lib-tab.active .lib-tab-count { background: #0081A7; color: white; }

/* ── Hero ── */
.lib-hero {
    position: relative; display: flex; align-items: center; gap: 20px;
    padding: 28px 28px 24px; margin-bottom: 20px;
    background: linear-gradient(145deg, #EAF5F8 0%, #F2FBFC 55%, #FFFFFF 100%);
    border: 1px solid #C5E6EF; border-radius: 18px; overflow: hidden;
}
.lib-hero-deco { position: absolute; right: -16px; top: -16px; width: 140px; height: 140px; color: #0081A7; opacity: 0.07; pointer-events: none; }
.lib-hero-deco svg { width: 100%; height: 100%; }
.lib-hero-icon {
    width: 68px; height: 68px; flex-shrink: 0; border-radius: 16px;
    background: linear-gradient(140deg, #0081A7 0%, #00AFB9 100%);
    display: flex; align-items: center; justify-content: center; color: white;
    box-shadow: 0 8px 24px rgba(0,129,167,0.32);
}
.lib-hero-icon svg { width: 34px; height: 34px; }
.lib-hero-text { flex: 1; min-width: 0; }
.lib-hero-eyebrow { font-size: 10.5px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #0081A7; margin-bottom: 3px; }
.lib-hero-title { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 34px; line-height: 1.1; color: #1A1523; margin-bottom: 6px; }
.lib-hero-stats { font-size: 13px; color: #7A6D92; display: flex; align-items: center; gap: 6px; }
.lib-stat-dot { color: #99CDD8; }
.lib-hero-btn {
    display: flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: #0081A7; color: white; border: none; border-radius: 24px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,129,167,0.35);
    transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
    flex-shrink: 0; white-space: nowrap;
}
.lib-hero-btn:hover { background: #006A8E; box-shadow: 0 6px 18px rgba(0,129,167,0.42); }
.lib-hero-btn:active { transform: scale(0.97); }

/* ── Filters ── */
.lib-filters { display: flex; align-items: center; gap: 14px; padding-bottom: 14px; border-bottom: 1px solid #F0ECF3; flex-wrap: wrap; }
.lib-search-wrap { position: relative; flex: 1; min-width: 160px; max-width: 280px; }
.lib-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #B0A8B8; pointer-events: none; }
.lib-search-input { width: 100%; padding: 8px 12px 8px 33px; background: #F7F5F9; border: 1px solid #EDE9F0; border-radius: 20px; font-size: 13px; color: #1A1523; outline: none; transition: border-color 0.15s, background 0.15s; }
.lib-search-input::placeholder { color: #B0A8B8; }
.lib-search-input:focus { background: white; border-color: #0081A7; box-shadow: 0 0 0 3px rgba(0,129,167,0.1); }
.lib-mood-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.lib-mood-btn { padding: 5px 13px; border: 1px solid #E5E0EA; border-radius: 20px; background: white; font-size: 12px; font-weight: 500; color: #7A6D92; cursor: pointer; transition: all 0.15s; text-transform: capitalize; }
.lib-mood-btn:hover { border-color: #0081A7; color: #0081A7; }
.lib-mood-btn.active { background: #EAF5F8; border-color: #0081A7; color: #005F7F; }

/* ── Track list ── */
.lib-tracklist { background: white; border: 1px solid #EDE9F0; border-radius: 16px; overflow: hidden; margin-top: 18px; }
.tl-header, .tl-row { display: grid; grid-template-columns: 48px 1fr 164px 128px 88px 80px; align-items: center; }
.tl-header { border-bottom: 1px solid #F3F0F5; padding: 0 8px; }
.tl-header > div { padding: 10px 8px; font-size: 10.5px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; color: #B0A8B8; }
.tl-body { position: relative; }
.tl-row { padding: 0 8px; border-bottom: 1px solid #FAF8FB; transition: background 0.1s; }
.tl-row:last-child { border-bottom: none; }
.tl-row:hover { background: #FAFAFA; }
.tl-row.is-current { background: #F0F8FA; }
.tl-row.is-current:hover { background: #E6F5F8; }
.tl-col-num { padding: 8px; }
.tl-num-cell { position: relative; display: flex; align-items: center; justify-content: center; height: 40px; }
.row-idx { position: absolute; font-size: 13px; color: #9C94A8; transition: opacity 0.12s; font-variant-numeric: tabular-nums; line-height: 1; }
.row-idx.is-rose { color: #0081A7; font-weight: 600; }
.row-play { position: absolute; opacity: 0; pointer-events: none; width: 28px; height: 28px; border-radius: 50%; border: none; background: none; display: flex; align-items: center; justify-content: center; color: #1A1523; cursor: pointer; transition: opacity 0.12s; padding: 0; }
.tl-row:hover .row-idx, .tl-row:hover .playing-bars { opacity: 0; }
.tl-row:hover .row-play { opacity: 1; pointer-events: auto; }
.tl-row.is-current .row-play { color: #0081A7; }
.playing-bars { position: absolute; display: flex; align-items: flex-end; gap: 2px; height: 14px; transition: opacity 0.12s; }
.playing-bars i { display: block; width: 3px; border-radius: 2px; background: #0081A7; height: 4px; animation: bar-bounce 0.9s ease-in-out infinite; }
.playing-bars i:nth-child(2) { animation-delay: 0.18s; }
.playing-bars i:nth-child(3) { animation-delay: 0.36s; }
@keyframes bar-bounce { 0%, 100% { height: 4px; } 50% { height: 14px; } }
.tl-col-title { padding: 8px; }
.tl-title-cell { display: flex; align-items: center; gap: 12px; min-width: 0; }
.tl-thumb { width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0; overflow: hidden; background: #F3F0F5; display: flex; align-items: center; justify-content: center; }
.tl-thumb-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tl-thumb-empty { color: #C4BDD0; }
.tl-name-wrap { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.tl-name { font-size: 14px; font-weight: 500; color: #1A1523; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.15s; }
.tl-name.is-rose { color: #0081A7; }
.tl-artist { font-size: 12px; color: #9C94A8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tl-col-mood { padding: 8px; }
.tl-mood-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px 3px 7px; border-radius: 20px; font-size: 11.5px; font-weight: 500; text-transform: capitalize; white-space: nowrap; }
.tl-mood-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.tl-col-date { padding: 8px; }
.tl-col-dur { padding: 8px; display: flex; align-items: center; }
.tl-date, .tl-dur { font-size: 12.5px; color: #9C94A8; font-variant-numeric: tabular-nums; }
.tl-col-actions { padding: 8px; }
.tl-actions { display: flex; align-items: center; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.tl-row:hover .tl-actions { opacity: 1; }
.tl-act { width: 30px; height: 30px; border-radius: 8px; border: none; background: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.12s, color 0.12s; }
.tl-act.edit { color: #7A6D92; }
.tl-act.edit:hover { background: #EDE9F0; color: #4C3F73; }
.tl-act.delete { color: #B0A8B8; }
.tl-act.delete:hover { background: #FFF1F1; color: #C0392B; }
.tl-empty { display: flex; flex-direction: column; align-items: center; padding: 56px 24px; gap: 10px; }
.tl-empty-icon { width: 56px; height: 56px; border-radius: 50%; background: #F0F8FA; display: flex; align-items: center; justify-content: center; color: #99CDD8; margin-bottom: 4px; }
.tl-empty-icon svg { width: 26px; height: 26px; }
.tl-empty-title { font-size: 15px; font-weight: 600; color: #1A1523; }
.tl-empty-sub { font-size: 13px; color: #9C94A8; }
.tl-item-enter-active { transition: opacity 0.2s, transform 0.2s; }
.tl-item-leave-active { transition: opacity 0.15s; }
.tl-item-enter-from { opacity: 0; transform: translateY(6px); }
.tl-item-leave-to { opacity: 0; }

/* ── Collections page ── */
.col-page-hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.col-page-title { font-size: 26px; font-weight: 700; color: #1A1523; margin-bottom: 4px; }
.col-page-sub { font-size: 13px; color: #7A6D92; }
.col-empty { display: flex; flex-direction: column; align-items: center; padding: 80px 24px; gap: 10px; }
.col-empty-icon { width: 72px; height: 72px; border-radius: 50%; background: #F0F8FA; display: flex; align-items: center; justify-content: center; color: #99CDD8; margin-bottom: 8px; }
.col-empty-icon svg { width: 32px; height: 32px; }

/* ── Collection grid ── */
.col-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
.col-card { cursor: pointer; }
.col-card-art {
    position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden;
    background: linear-gradient(135deg, #0081A7 0%, #00AFB9 100%);
    margin-bottom: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transition: box-shadow 0.2s, transform 0.2s;
}
.col-card:hover .col-card-art { box-shadow: 0 8px 28px rgba(0,0,0,0.18); transform: translateY(-2px); }
.col-card-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.col-card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.col-card-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0); transition: background 0.2s; display: flex; align-items: flex-end; justify-content: flex-end; padding: 10px; }
.col-card:hover .col-card-overlay { background: rgba(0,0,0,0.25); }
.col-card-play-btn {
    width: 44px; height: 44px; border-radius: 50%; border: none;
    background: #0081A7; color: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; opacity: 0; transform: translateY(6px);
    transition: opacity 0.2s, transform 0.2s, background 0.15s;
    box-shadow: 0 4px 14px rgba(0,0,0,0.3);
}
.col-card:hover .col-card-play-btn { opacity: 1; transform: translateY(0); }
.col-card-play-btn:hover { background: #006A8E; }
.col-card-menu { position: absolute; top: 8px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.col-card:hover .col-card-menu { opacity: 1; }
.col-card-menu-btn {
    width: 26px; height: 26px; border-radius: 6px; border: none;
    background: rgba(255,255,255,0.9); color: #374151; backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    transition: background 0.12s, color 0.12s;
}
.col-card-menu-btn:hover { background: white; }
.col-card-menu-btn.danger:hover { background: #FFF1F1; color: #C0392B; }
.col-card-name { font-size: 13.5px; font-weight: 600; color: #1A1523; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.col-card-meta { font-size: 11.5px; color: #9C94A8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Collection detail ── */
.col-back-btn {
    display: inline-flex; align-items: center; gap: 7px; margin-bottom: 20px;
    font-size: 13px; font-weight: 500; color: #7A6D92; border: none; background: none;
    cursor: pointer; padding: 6px 0; transition: color 0.15s;
}
.col-back-btn:hover { color: #0081A7; }
.col-detail-hero {
    display: flex; gap: 28px; align-items: flex-end;
    padding: 28px; border-radius: 18px; margin-bottom: 24px;
    background: linear-gradient(145deg, #EAF5F8 0%, #F2FBFC 70%, #FFFFFF 100%);
    border: 1px solid #C5E6EF;
}
.col-detail-cover {
    width: 200px; height: 200px; flex-shrink: 0; border-radius: 14px; overflow: hidden;
    background: linear-gradient(135deg, #0081A7 0%, #00AFB9 100%);
    box-shadow: 0 8px 32px rgba(0,129,167,0.28);
    display: flex; align-items: center; justify-content: center;
}
.col-detail-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.col-detail-cover-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.col-detail-info { flex: 1; min-width: 0; }
.col-detail-label { font-size: 10.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #0081A7; display: block; margin-bottom: 8px; }
.col-detail-title { font-size: 32px; font-weight: 800; color: #1A1523; line-height: 1.1; margin-bottom: 8px; }
.col-detail-desc { font-size: 13.5px; color: #7A6D92; margin-bottom: 8px; }
.col-detail-meta { font-size: 13px; color: #9C94A8; margin-bottom: 20px; }
.col-detail-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.col-play-all-btn {
    display: flex; align-items: center; gap: 8px; padding: 11px 24px;
    background: #0081A7; color: white; border: none; border-radius: 50px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    box-shadow: 0 4px 14px rgba(0,129,167,0.35); transition: background 0.15s, transform 0.1s;
}
.col-play-all-btn:hover:not(:disabled) { background: #006A8E; }
.col-play-all-btn:active:not(:disabled) { transform: scale(0.97); }
.col-play-all-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.col-action-btn {
    display: flex; align-items: center; gap: 6px; padding: 9px 16px;
    border: 1.5px solid #C5E6EF; border-radius: 50px; background: white;
    font-size: 13px; font-weight: 500; color: #0081A7; cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
}
.col-action-btn:hover { background: #EAF5F8; border-color: #0081A7; }

/* ── Collection track list ── */
.col-tl { background: white; border: 1px solid #EDE9F0; border-radius: 16px; overflow: hidden; }
.col-tl-header {
    display: grid; grid-template-columns: 32px 48px 1fr 64px 40px;
    border-bottom: 1px solid #F3F0F5; padding: 0 8px;
}
.col-tl-header > div { padding: 10px 8px; font-size: 10.5px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; color: #B0A8B8; }
.col-tl-row {
    display: grid; grid-template-columns: 32px 48px 1fr 64px 40px;
    align-items: center; padding: 0 8px; border-bottom: 1px solid #FAF8FB;
    transition: background 0.1s; cursor: default;
}
.col-tl-row:last-child { border-bottom: none; }
.col-tl-row:hover { background: #FAFAFA; }
.col-tl-row.is-current { background: #F0F8FA; }
.col-tl-row.is-current:hover { background: #E6F5F8; }
.col-tl-drag { display: flex; align-items: center; justify-content: center; padding: 8px; }
.col-drag-handle { cursor: grab; opacity: 0; transition: opacity 0.12s; }
.col-tl-row:hover .col-drag-handle { opacity: 1; }
.col-drag-handle:active { cursor: grabbing; }
.col-tl-num { padding: 8px; }
.col-tl-num-cell { position: relative; display: flex; align-items: center; justify-content: center; height: 40px; }
.col-tl-title { padding: 8px; }
.col-tl-title-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
.col-tl-dur { padding: 8px; font-size: 12.5px; color: #9C94A8; font-variant-numeric: tabular-nums; }
.col-tl-rm { padding: 8px; display: flex; align-items: center; justify-content: center; }
.col-rm-btn {
    width: 26px; height: 26px; border-radius: 50%; border: none; background: none;
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    color: #9C94A8; opacity: 0; transition: opacity 0.15s, background 0.12s, color 0.12s;
}
.col-tl-row:hover .col-rm-btn { opacity: 1; }
.col-rm-btn:hover { background: #FFF1F1; color: #C0392B; }

/* ── Add tracks modal ── */
.add-track-row {
    display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px;
    cursor: pointer; transition: background 0.1s;
}
.add-track-row:hover { background: #F7F5F9; }
.add-track-row.selected { background: #EAF5F8; }
.add-track-check { width: 16px; height: 16px; flex-shrink: 0; accent-color: #0081A7; cursor: pointer; }

/* ── Toast ── */
.toast-wrap { position: fixed; top: 20px; right: 24px; z-index: 9999; display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; box-shadow: 0 4px 20px rgba(0,0,0,0.15); pointer-events: none; }
.toast-wrap.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.toast-wrap.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.toast-icon { width: 16px; height: 16px; flex-shrink: 0; }
.toast-slide-enter-active { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s; }
.toast-slide-leave-active { transition: transform 0.2s ease, opacity 0.2s; }
.toast-slide-enter-from { transform: translateY(-10px) scale(0.95); opacity: 0; }
.toast-slide-leave-to { transform: translateY(-6px); opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Player bar ── */
.player-slide-enter-active { transition: transform 0.38s cubic-bezier(0.34, 1.3, 0.64, 1), opacity 0.25s ease; }
.player-slide-leave-active { transition: transform 0.22s cubic-bezier(0.4, 0, 1, 1), opacity 0.18s ease; }
.player-slide-enter-from, .player-slide-leave-to { transform: translateY(100%); opacity: 0; }
.track-switch-enter-active { transition: transform 0.25s cubic-bezier(0.34, 1.3, 0.64, 1), opacity 0.2s; }
.track-switch-leave-active { transition: transform 0.15s ease, opacity 0.12s; }
.track-switch-enter-from { transform: translateY(8px); opacity: 0; }
.track-switch-leave-to { transform: translateY(-6px); opacity: 0; }
.cover-swap-enter-active, .cover-swap-leave-active { transition: opacity 0.25s; }
.cover-swap-enter-from, .cover-swap-leave-to { opacity: 0; }
.sp-bar-spacer { height: 80px; }
.sp-bar {
    position: fixed; bottom: 0; left: var(--admin-sidebar-w, 0px); right: 0;
    height: 80px; z-index: 200;
    display: grid; grid-template-columns: minmax(180px, 30%) 1fr minmax(160px, 26%);
    align-items: center; gap: 16px; padding: 0 24px;
    padding-bottom: env(safe-area-inset-bottom, 0px);
    background: rgba(255,255,255,0.96); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
    border-top: 1px solid #EDE9F0; box-shadow: 0 -2px 24px rgba(0,0,0,0.06);
}
.sp-bar::before { content: ''; position: absolute; top: -1px; left: 0; right: 0; height: 2px; background: transparent; transition: background 0.4s; pointer-events: none; }
.sp-bar.is-playing::before { background: linear-gradient(90deg, transparent 0%, #0081A7 20%, #00AFB9 50%, #0081A7 80%, transparent 100%); }
.sp-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.sp-thumb { width: 52px; height: 52px; border-radius: 10px; flex-shrink: 0; overflow: hidden; background: #F0ECF3; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(0,0,0,0.12); }
.sp-thumb-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sp-thumb-empty { color: #C4BDD0; }
.sp-info { min-width: 0; flex: 1; }
.sp-title { display: block; font-size: 13px; font-weight: 600; color: #1A1523; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sp-artist { display: block; font-size: 11.5px; color: #9C94A8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sp-mood-badge { padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 600; text-transform: capitalize; white-space: nowrap; flex-shrink: 0; }
.sp-center { display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0; }
.sp-controls { display: flex; align-items: center; gap: 6px; }
.sp-skip { width: 32px; height: 32px; border-radius: 50%; border: none; background: none; display: flex; align-items: center; justify-content: center; color: #7A6D92; cursor: pointer; transition: color 0.15s, background 0.15s; }
.sp-skip:hover { color: #1A1523; background: #F0ECF3; }
.sp-play { width: 38px; height: 38px; border-radius: 50%; border: none; background: #1A1523; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s, transform 0.1s; box-shadow: 0 2px 10px rgba(0,0,0,0.18); }
.sp-play:hover { background: #2D2740; }
.sp-play:active { transform: scale(0.93); }
.sp-seek-row { display: flex; align-items: center; gap: 8px; width: 100%; }
.sp-time { font-family: 'Courier New', 'SF Mono', monospace; font-size: 10.5px; color: #9C94A8; font-variant-numeric: tabular-nums; white-space: nowrap; flex-shrink: 0; }
.sp-seek { flex: 1; height: 18px; display: flex; align-items: center; cursor: pointer; }
.sp-seek-track { width: 100%; height: 4px; background: #EDE9F0; border-radius: 2px; position: relative; overflow: hidden; transition: height 0.15s; }
.sp-seek:hover .sp-seek-track { height: 5px; }
.sp-seek-fill { height: 100%; background: #0081A7; border-radius: inherit; transition: width 0.1s linear; }
.sp-right { display: flex; align-items: center; gap: 8px; justify-content: flex-end; min-width: 0; }
.sp-vol-btn { width: 28px; height: 28px; border: none; background: none; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #9C94A8; cursor: pointer; transition: color 0.15s; }
.sp-vol-btn:hover { color: #1A1523; }
.sp-vol {
    width: 90px; flex-shrink: 0; -webkit-appearance: none; appearance: none;
    height: 3px; border-radius: 2px; background: #EDE9F0; outline: none; cursor: pointer;
    background-image: linear-gradient(#0081A7, #0081A7);
    background-size: v-bind('`${(isMuted ? 0 : volume) * 100}% 100%`');
    background-repeat: no-repeat;
}
.sp-vol::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; border-radius: 50%; background: #0081A7; cursor: pointer; box-shadow: 0 0 3px rgba(0,129,167,0.4); }
.sp-vol::-moz-range-thumb { width: 12px; height: 12px; border: none; border-radius: 50%; background: #0081A7; cursor: pointer; }
.sp-close { width: 28px; height: 28px; border-radius: 50%; border: none; background: #F0ECF3; display: flex; align-items: center; justify-content: center; color: #9C94A8; cursor: pointer; flex-shrink: 0; transition: background 0.15s, color 0.15s; }
.sp-close:hover { background: #EDE9F0; color: #1A1523; }

/* ── Responsive ── */
@media (max-width: 1023px) { .sp-bar { left: 0; } }
@media (max-width: 639px) {
    .lib-hero { padding: 20px 18px 18px; gap: 14px; }
    .lib-hero-icon { width: 52px; height: 52px; }
    .lib-hero-title { font-size: 26px; }
    .lib-hero-btn { padding: 9px 16px; font-size: 12px; }
    .tl-header, .tl-row { grid-template-columns: 40px 1fr 72px; }
    .tl-col-mood, .tl-col-date, .tl-col-dur { display: none; }
    .col-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .col-detail-hero { flex-direction: column; align-items: flex-start; gap: 16px; }
    .col-detail-cover { width: 140px; height: 140px; }
    .col-detail-title { font-size: 22px; }
    .sp-bar { height: 64px; grid-template-columns: 1fr auto auto; gap: 8px; padding: 0 14px; }
    .sp-bar-spacer { height: 64px; }
    .sp-center { flex-direction: row; gap: 2px; }
    .sp-seek-row, .sp-mood-badge, .sp-vol, .sp-vol-btn { display: none; }
    .sp-thumb { width: 42px; height: 42px; }
    .col-tl-header, .col-tl-row { grid-template-columns: 32px 40px 1fr 40px; }
    .col-tl-dur { display: none; }
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
    .track-switch-enter-from, .track-switch-leave-to { transform: none; }
    .playing-bars i { animation: none; height: 8px; }
}
</style>
