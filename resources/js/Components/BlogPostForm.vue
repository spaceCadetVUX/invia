<script setup>
import { ref, computed } from 'vue'
import TiptapEditor from '@/Components/TiptapEditor.vue'
import axios from 'axios'

const props = defineProps({
    form:     { type: Object,  required: true },
    editPost: { type: Object,  default: null  },
    authors:  { type: Array,   default: () => [] },
})

const showJsonLd   = ref(false)
const sidebarTab   = ref('post')   // 'post' | 'author'
const coverPreview = ref(null)

// ── Author tab state ─────────────────────────────────────────────────────────
const localAuthors   = ref([...props.authors])
const showCreateForm = ref(false)
const editingId      = ref(null)
const newAuthor      = ref({ name: '', email: '' })
const editName       = ref('')
const authorBusy     = ref(false)
const authorError    = ref('')

async function createAuthor() {
    authorError.value = ''
    authorBusy.value  = true
    try {
        const res = await axios.post('/admin/authors', newAuthor.value)
        localAuthors.value.push(res.data)
        props.form.author_id = res.data.id
        newAuthor.value = { name: '', email: '' }
        showCreateForm.value = false
    } catch (e) {
        authorError.value = e.response?.data?.message
            ?? Object.values(e.response?.data?.errors ?? {})[0]?.[0]
            ?? 'Error creating author'
    } finally {
        authorBusy.value = false
    }
}

function startEdit(a) {
    editingId.value = a.id
    editName.value  = a.name
}

async function saveEdit(a) {
    authorError.value = ''
    authorBusy.value  = true
    try {
        const res = await axios.patch(`/admin/authors/${a.id}`, { name: editName.value })
        const idx = localAuthors.value.findIndex(x => x.id === a.id)
        if (idx !== -1) localAuthors.value[idx].name = res.data.name
        editingId.value = null
    } catch (e) {
        authorError.value = e.response?.data?.message ?? 'Error saving'
    } finally {
        authorBusy.value = false
    }
}

function onCoverChange(e) {
    const file = e.target.files[0]
    if (!file) return
    props.form.cover_image = file
    coverPreview.value = URL.createObjectURL(file)
}

function removeCover() {
    props.form.cover_image = null
    coverPreview.value = null
}

const coverSrc = computed(() => {
    if (coverPreview.value) return coverPreview.value
    if (props.editPost?.cover_image_path) return `/storage/${props.editPost.cover_image_path}`
    return null
})

function addFaq() { props.form.faq.push({ question: '', answer: '' }) }
function removeFaq(i) { props.form.faq.splice(i, 1) }

const metaTitleLen = computed(() => props.form.meta_title?.length ?? 0)
const metaDescLen  = computed(() => props.form.meta_description?.length ?? 0)

const slug = computed(() => props.editPost?.slug ?? 'your-post-slug')

// ── JSON-LD preview ──────────────────────────────────────────────────────────
const articleLd = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Article',
    'headline': props.form.title || '',
    'description': props.form.meta_description || props.form.excerpt || '',
    'url': `https://invia.vn/blog/${slug.value}`,
    'datePublished': props.editPost?.published_at ?? '',
    'author': { '@type': 'Person', 'name': props.editPost?.author?.name ?? 'Invia' },
    'publisher': { '@type': 'Organization', 'name': 'Invia.vn', 'url': 'https://invia.vn' },
}))

const faqLd = computed(() => {
    if (!props.form.faq?.length) return null
    return {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        'mainEntity': props.form.faq.map(f => ({
            '@type': 'Question',
            'name': f.question,
            'acceptedAnswer': { '@type': 'Answer', 'text': f.answer },
        })),
    }
})

const jsonLdText = computed(() => {
    const parts = [JSON.stringify(articleLd.value, null, 2)]
    if (faqLd.value) parts.push(JSON.stringify(faqLd.value, null, 2))
    return parts.join('\n\n')
})
</script>

<template>
    <div class="flex flex-col gap-6">

        <!-- ── Left: main content ─────────────────────────────────────────── -->
        <div class="space-y-5 min-w-0">

            <!-- Title -->
            <div>
                <input v-model="form.title"
                    placeholder="Post title..."
                    class="w-full text-2xl font-bold text-[#1E1E2D] placeholder:text-gray-300 border-0 border-b border-gray-200 pb-3 focus:outline-none focus:border-[#5B9FD6] transition bg-transparent" />
                <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Excerpt</label>
                <textarea v-model="form.excerpt" rows="2"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition resize-none"
                    placeholder="Short description shown in the post listing..." />
            </div>

            <!-- Content -->
            <div>
                <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Content</label>
                <TiptapEditor v-model="form.content" />
                <p v-if="form.errors.content" class="mt-1 text-xs text-red-500">{{ form.errors.content }}</p>
            </div>

        </div>

        <!-- ── Right: sidebar ─────────────────────────────────────────────── -->
        <div class="space-y-4">

            <!-- Sidebar tab bar -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex border-b border-gray-100">
                    <button type="button"
                        @click="sidebarTab = 'post'"
                        class="flex-1 py-2.5 text-xs font-semibold uppercase tracking-wide transition-colors"
                        :class="sidebarTab === 'post'
                            ? 'bg-[#1E1E2D] text-white'
                            : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50'">
                        Post
                    </button>
                    <button type="button"
                        @click="sidebarTab = 'author'"
                        class="flex-1 py-2.5 text-xs font-semibold uppercase tracking-wide transition-colors border-l border-gray-100 flex items-center justify-center gap-1.5"
                        :class="sidebarTab === 'author'
                            ? 'bg-[#1E1E2D] text-white'
                            : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50'">
                        Author
                        <span v-if="form.author_id"
                            class="w-1.5 h-1.5 rounded-full shrink-0"
                            :class="sidebarTab === 'author' ? 'bg-white' : 'bg-[#5B9FD6]'">
                        </span>
                    </button>
                </div>
            </div>

            <!-- ── TAB: Post ── -->
            <template v-if="sidebarTab === 'post'">

            <!-- Cover Image -->
            <div class="bg-violet-50 border border-violet-200 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-semibold text-violet-500 uppercase tracking-wide mb-3">Cover Image</p>

                <div class="relative group mb-3">
                    <template v-if="coverSrc">
                        <img :src="coverSrc"
                             class="w-full h-32 object-cover rounded-xl bg-violet-100"
                             alt="Cover preview">
                        <button type="button"
                            @click="removeCover"
                            class="absolute top-2 right-2 p-1.5 rounded-full bg-black/50 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </template>
                    <template v-else>
                        <label class="flex flex-col items-center justify-center w-full h-32 rounded-xl border-2 border-dashed border-violet-200 hover:border-violet-400 cursor-pointer transition-colors bg-violet-100/50">
                            <svg class="w-8 h-8 text-violet-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/></svg>
                            <span class="text-xs text-violet-400">Drag & drop or <span class="text-violet-600 font-medium">browse</span></span>
                            <span class="text-xs text-violet-300 mt-0.5">JPG, PNG, WebP — 2MB</span>
                            <input type="file" accept="image/*" class="hidden" @change="onCoverChange">
                        </label>
                    </template>
                </div>

                <label v-if="coverSrc"
                    class="flex items-center justify-center gap-2 w-full py-2 text-xs border border-violet-200 rounded-xl cursor-pointer hover:border-violet-400 hover:text-violet-600 text-violet-500 transition-colors">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    Change image
                    <input type="file" accept="image/*" class="hidden" @change="onCoverChange">
                </label>
            </div>

            <!-- Status -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-3">Status</p>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <div class="relative shrink-0">
                        <input v-model="form.is_published" type="checkbox" class="sr-only peer">
                        <div class="w-10 h-6 bg-emerald-200 rounded-full peer-checked:bg-emerald-500 transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <span class="text-sm font-medium text-emerald-700">Publish immediately</span>
                </label>
            </div>

            <!-- SEO -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 shadow-sm space-y-4">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">SEO</p>

                <!-- Google preview -->
                <div class="rounded-xl border border-amber-100 p-3 bg-white text-xs">
                    <div class="text-[#006621] truncate mb-0.5">invia.vn › blog › {{ slug }}</div>
                    <div class="text-[#1a0dab] font-medium leading-snug line-clamp-1 mb-0.5">
                        {{ form.meta_title || form.title || 'Post title' }}
                    </div>
                    <div class="text-[#545454] leading-snug line-clamp-2">
                        {{ form.meta_description || form.excerpt || 'Short description will appear here...' }}
                    </div>
                </div>

                <!-- Meta Title -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Meta Title</label>
                        <span class="text-xs" :class="metaTitleLen > 60 ? 'text-red-500' : 'text-gray-400'">{{ metaTitleLen }}/60</span>
                    </div>
                    <input v-model="form.meta_title"
                        maxlength="70"
                        placeholder="Leave blank to use post title"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition"
                        :class="metaTitleLen > 60 ? 'border-red-300' : ''" />
                </div>

                <!-- Meta Description -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Meta Description</label>
                        <span class="text-xs" :class="metaDescLen > 160 ? 'text-red-500' : 'text-gray-400'">{{ metaDescLen }}/160</span>
                    </div>
                    <textarea v-model="form.meta_description"
                        rows="3"
                        maxlength="320"
                        placeholder="Leave blank to use excerpt"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition"
                        :class="metaDescLen > 160 ? 'border-red-300' : ''" />
                </div>
            </div>

            <!-- JSON-LD preview -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <button type="button"
                    @click="showJsonLd = !showJsonLd"
                    class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-700/50 transition-colors">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-300 uppercase tracking-wide">JSON-LD</span>
                        <span class="text-xs bg-slate-600 text-emerald-400 px-1.5 py-0.5 rounded font-mono">ld+json</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 transition-transform"
                         :class="showJsonLd ? 'rotate-180' : ''"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div v-if="showJsonLd" class="border-t border-slate-700">
                    <pre class="p-4 text-xs text-emerald-300 bg-slate-900 overflow-x-auto leading-relaxed font-mono whitespace-pre-wrap break-words max-h-80">{{ jsonLdText }}</pre>
                </div>
            </div>

            <!-- FAQ -->
            <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs font-semibold text-teal-600 uppercase tracking-wide">FAQ</p>
                        <p class="text-xs text-teal-400 mt-0.5">Auto-generates FAQPage JSON-LD schema</p>
                    </div>
                    <button type="button" @click="addFaq"
                        class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg border border-teal-400 text-teal-600 hover:bg-teal-100 transition-colors">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                        Add
                    </button>
                </div>

                <div v-if="!form.faq?.length"
                    class="border border-dashed border-teal-200 rounded-xl py-5 text-center text-xs text-teal-400">
                    No FAQ items yet
                </div>

                <div class="space-y-3">
                    <div v-for="(item, i) in form.faq" :key="i"
                        class="border border-teal-200 rounded-xl p-3 space-y-2 bg-white/70">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-400">Question {{ i + 1 }}</span>
                            <button type="button" @click="removeFaq(i)"
                                class="p-1 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <input v-model="item.question"
                            placeholder="Question..."
                            class="w-full border border-gray-200 bg-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition font-medium" />
                        <textarea v-model="item.answer"
                            rows="2"
                            placeholder="Answer..."
                            class="w-full border border-gray-200 bg-white rounded-lg px-3 py-2 text-xs resize-none focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                    </div>
                </div>
            </div>

            </template>
            <!-- ── end TAB: Post ── -->

            <!-- ── TAB: Author ── -->
            <template v-if="sidebarTab === 'author'">
            <div class="bg-purple-50 border border-purple-200 rounded-2xl shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-purple-200">
                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide">Authors</p>
                    <button type="button"
                        @click="showCreateForm = !showCreateForm; authorError = ''"
                        class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg border transition-colors"
                        :class="showCreateForm
                            ? 'border-red-200 text-red-500 hover:bg-red-50'
                            : 'border-[#5B9FD6] text-[#5B9FD6] hover:bg-[#EEF4FB]'">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path v-if="!showCreateForm" stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        {{ showCreateForm ? 'Cancel' : 'New Author' }}
                    </button>
                </div>

                <!-- Inline create form -->
                <div v-if="showCreateForm" class="p-4 border-b border-purple-100 bg-purple-100/40 space-y-2.5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                        <input v-model="newAuthor.name"
                            placeholder="Full name..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input v-model="newAuthor.email"
                            type="email"
                            placeholder="email@example.com"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                    </div>
                    <p v-if="authorError" class="text-xs text-red-500">{{ authorError }}</p>
                    <button type="button"
                        @click="createAuthor"
                        :disabled="authorBusy || !newAuthor.name || !newAuthor.email"
                        class="w-full py-2 text-xs font-semibold bg-[#1E1E2D] text-white rounded-lg hover:bg-[#2a2a3d] disabled:opacity-40 transition-colors">
                        {{ authorBusy ? 'Creating...' : 'Create Author' }}
                    </button>
                </div>

                <!-- Author list -->
                <div class="p-3 space-y-1.5">
                    <template v-if="localAuthors.length">
                        <div v-for="a in localAuthors" :key="a.id">

                            <!-- Edit mode -->
                            <div v-if="editingId === a.id"
                                class="flex items-center gap-2 p-2 rounded-xl border border-[#5B9FD6] bg-[#EEF4FB]/60">
                                <input v-model="editName"
                                    @keyup.enter="saveEdit(a)"
                                    @keyup.escape="editingId = null"
                                    class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white min-w-0" />
                                <button type="button" @click="saveEdit(a)" :disabled="authorBusy"
                                    class="p-1.5 rounded-lg bg-[#1E1E2D] text-white hover:bg-[#2a2a3d] disabled:opacity-40 transition-colors shrink-0">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="editingId = null"
                                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-200 transition-colors shrink-0">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Select mode -->
                            <label v-else
                                class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-colors group"
                                :class="form.author_id === a.id
                                    ? 'border-[#1E1E2D] bg-[#1E1E2D]/5'
                                    : 'border-gray-100 hover:border-gray-300 hover:bg-gray-50'">
                                <input type="radio" :value="a.id" v-model="form.author_id" class="sr-only">
                                <div class="w-7 h-7 rounded-full bg-[#EEF4FB] flex items-center justify-center text-[#5B9FD6] text-xs font-bold shrink-0">
                                    {{ a.name.charAt(0).toUpperCase() }}
                                </div>
                                <span class="flex-1 text-sm font-medium text-[#1E1E2D] truncate min-w-0">{{ a.name }}</span>
                                <svg v-if="form.author_id === a.id"
                                     class="w-3.5 h-3.5 text-[#1E1E2D] shrink-0"
                                     viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
                                </svg>
                                <button v-else type="button"
                                    @click.prevent="startEdit(a)"
                                    class="p-1 rounded-lg text-gray-300 hover:text-gray-600 hover:bg-gray-200 opacity-0 group-hover:opacity-100 transition-all shrink-0">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                </button>
                            </label>

                        </div>
                    </template>
                    <p v-else class="text-xs text-gray-400 text-center py-4">No authors yet. Create one above.</p>
                </div>

            </div>
            </template>
            <!-- ── end TAB: Author ── -->

        </div>
    </div>
</template>
