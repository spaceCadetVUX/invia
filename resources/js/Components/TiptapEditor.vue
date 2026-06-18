<script setup>
import { ref, watch, computed } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'

// Extend Image để hỗ trợ width attribute
const ImageWithWidth = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: el => el.style.width || el.getAttribute('width') || null,
                renderHTML: attrs => {
                    if (!attrs.width) return {}
                    return { style: `width:${attrs.width};max-width:100%;height:auto;` }
                },
            },
        }
    },
})
import TextAlign from '@tiptap/extension-text-align'
import axios from 'axios'

const props = defineProps({
    modelValue: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

// ── Image modal state ─────────────────────────────────────────────────────────
const showImgModal  = ref(false)
const imgTab        = ref('upload')   // 'upload' | 'url'
const imgUploading  = ref(false)
const imgFile       = ref(null)
const imgFilePreview = ref(null)
const imgUrl        = ref('')
const imgWidth      = ref('100%')
const imgFileInput  = ref(null)

const IMG_WIDTHS = [
    { label: 'Tự động', value: '100%' },
    { label: 'Nhỏ (25%)', value: '25%' },
    { label: 'Vừa (50%)', value: '50%' },
    { label: 'Lớn (75%)', value: '75%' },
    { label: 'Đầy đủ', value: '100%' },
]

function openImgModal() {
    imgTab.value         = 'upload'
    imgFile.value        = null
    imgFilePreview.value = null
    imgUrl.value         = ''
    imgWidth.value       = '100%'
    showImgModal.value   = true
}

function onImgFileChange(e) {
    const file = e.target.files[0]
    if (!file) return
    imgFile.value        = file
    imgFilePreview.value = URL.createObjectURL(file)
}

function clearFile() {
    imgFile.value        = null
    imgFilePreview.value = null
    if (imgFileInput.value) imgFileInput.value.value = ''
}

const canInsert = computed(() => {
    if (imgTab.value === 'upload') return !!imgFile.value
    return imgUrl.value.trim().startsWith('http')
})

async function insertImg() {
    let src = ''
    if (imgTab.value === 'upload') {
        imgUploading.value = true
        try {
            const fd = new FormData()
            fd.append('image', imgFile.value)
            const { data } = await axios.post('/admin/upload/image', fd)
            src = data.url
        } finally {
            imgUploading.value = false
        }
    } else {
        src = imgUrl.value.trim()
    }
    if (!src) return
    editor.value.chain().focus().setImage({
        src,
        width: imgWidth.value,
    }).run()
    showImgModal.value = false
}

// ── Drag & drop / paste ───────────────────────────────────────────────────────
async function uploadAndInsert(file) {
    if (!file?.type.startsWith('image/')) return
    const fd = new FormData()
    fd.append('image', file)
    const { data } = await axios.post('/admin/upload/image', fd)
    editor.value.chain().focus().setImage({
        src: data.url,
        width: '100%',
    }).run()
}

// ── Editor ────────────────────────────────────────────────────────────────────
const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Underline,
        Link.configure({
            openOnClick: false,
            HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' },
        }),
        ImageWithWidth.configure({
            inline: false,
            allowBase64: false,
        }),
        Placeholder.configure({ placeholder: 'Viết nội dung bài viết...' }),
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
    ],
    editorProps: {
        handleDrop(view, event) {
            const file = event.dataTransfer?.files?.[0]
            if (!file?.type.startsWith('image/')) return false
            event.preventDefault()
            uploadAndInsert(file)
            return true
        },
        handlePaste(view, event) {
            const items = event.clipboardData?.items
            if (!items) return false
            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    event.preventDefault()
                    uploadAndInsert(item.getAsFile())
                    return true
                }
            }
            return false
        },
    },
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (value) => {
    if (!editor.value) return
    if (editor.value.getHTML() !== value) {
        editor.value.commands.setContent(value ?? '', false)
    }
})

function setLink() {
    const prev = editor.value.getAttributes('link').href
    const url = window.prompt('Nhập URL:', prev ?? 'https://')
    if (url === null) return
    if (url === '') editor.value.chain().focus().unsetLink().run()
    else editor.value.chain().focus().setLink({ href: url }).run()
}

const btn = (active) =>
    active ? 'bg-[#1E1E2D] text-white' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'
</script>

<template>
    <div class="border border-gray-200 rounded-xl overflow-visible focus-within:ring-2 focus-within:ring-[#5B9FD6]/30 focus-within:border-[#5B9FD6] transition">

        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 px-2 py-1.5 border-b border-gray-100 bg-gray-50/80 rounded-t-xl sticky top-0 z-10">

            <!-- Undo / Redo -->
            <div class="flex">
                <button type="button" title="Undo" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" class="p-1.5 rounded-lg transition-colors disabled:opacity-30" :class="btn(false)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                </button>
                <button type="button" title="Redo" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" class="p-1.5 rounded-lg transition-colors disabled:opacity-30" :class="btn(false)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Headings -->
            <div class="flex">
                <button type="button" @click="editor.chain().focus().setParagraph().run()" class="px-2 py-1 rounded-lg text-xs font-medium transition-colors" :class="btn(editor.isActive('paragraph'))">P</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" class="px-2 py-1 rounded-lg text-xs font-bold transition-colors" :class="btn(editor.isActive('heading', { level: 1 }))">H1</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" class="px-2 py-1 rounded-lg text-xs font-bold transition-colors" :class="btn(editor.isActive('heading', { level: 2 }))">H2</button>
                <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" class="px-2 py-1 rounded-lg text-xs font-bold transition-colors" :class="btn(editor.isActive('heading', { level: 3 }))">H3</button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Inline -->
            <div class="flex">
                <button type="button" title="Bold" @click="editor.chain().focus().toggleBold().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('bold'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h8a4 4 0 0 0 0-8H6v8Zm0 0h9a4 4 0 0 1 0 8H6v-8Z"/></svg>
                </button>
                <button type="button" title="Italic" @click="editor.chain().focus().toggleItalic().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('italic'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 4h-9M14 20H5M14.7 4.7 9.3 19.3"/></svg>
                </button>
                <button type="button" title="Underline" @click="editor.chain().focus().toggleUnderline().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('underline'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4v6a6 6 0 0 0 12 0V4M4 20h16"/></svg>
                </button>
                <button type="button" title="Strike" @click="editor.chain().focus().toggleStrike().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('strike'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 4H9a3 3 0 0 0-2.83 4M14 12a4 4 0 0 1 0 8H6M4 12h16"/></svg>
                </button>
                <button type="button" title="Code" @click="editor.chain().focus().toggleCode().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('code'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16 18 6-6-6-6M8 6l-6 6 6 6"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Align -->
            <div class="flex">
                <button type="button" title="Căn trái" @click="editor.chain().focus().setTextAlign('left').run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive({ textAlign: 'left' }))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18M3 12h12M3 18h15"/></svg>
                </button>
                <button type="button" title="Căn giữa" @click="editor.chain().focus().setTextAlign('center').run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive({ textAlign: 'center' }))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18M6 12h12M4.5 18h15"/></svg>
                </button>
                <button type="button" title="Căn phải" @click="editor.chain().focus().setTextAlign('right').run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive({ textAlign: 'right' }))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 6h18M9 12h12M6 18h15"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Lists -->
            <div class="flex">
                <button type="button" title="Bullet list" @click="editor.chain().focus().toggleBulletList().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('bulletList'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                </button>
                <button type="button" title="Ordered list" @click="editor.chain().focus().toggleOrderedList().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('orderedList'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-2-2-2"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Blocks -->
            <div class="flex">
                <button type="button" title="Blockquote" @click="editor.chain().focus().toggleBlockquote().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('blockquote'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Z"/></svg>
                </button>
                <button type="button" title="Code block" @click="editor.chain().focus().toggleCodeBlock().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('codeBlock'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="m8 21 4-4 4 4M8 13l-2-2 2-2M16 11l2 2-2 2"/></svg>
                </button>
                <button type="button" title="Divider" @click="editor.chain().focus().setHorizontalRule().run()" class="p-1.5 rounded-lg transition-colors" :class="btn(false)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 12h18"/></svg>
                </button>
            </div>

            <div class="w-px h-5 bg-gray-200 mx-1" />

            <!-- Link & Image -->
            <div class="flex">
                <button type="button" title="Chèn link" @click="setLink" class="p-1.5 rounded-lg transition-colors" :class="btn(editor.isActive('link'))">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 0 0-5.656 0l-4 4a4 4 0 1 0 5.656 5.656l1.102-1.101m-.758-4.899a4 4 0 0 0 5.656 0l4-4a4 4 0 0 0-5.656-5.656l-1.1 1.1"/></svg>
                </button>
                <button type="button" title="Chèn ảnh" @click="openImgModal" class="p-1.5 rounded-lg transition-colors" :class="btn(false)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/></svg>
                </button>
            </div>
        </div>

        <!-- Editor area -->
        <EditorContent :editor="editor" class="min-h-[320px]" />
    </div>

    <!-- ── Image Modal ─────────────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition name="img-fade">
            <div v-if="showImgModal"
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                 @click.self="showImgModal = false">
                <div class="absolute inset-0 bg-black/50" @click="showImgModal = false" />

                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-[#1E1E2D]">Chèn ảnh</h3>
                        <button @click="showImgModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-100 px-5">
                        <button type="button" @click="imgTab = 'upload'; clearFile()"
                            class="pb-3 pt-3 mr-5 text-sm font-medium border-b-2 transition-colors"
                            :class="imgTab === 'upload' ? 'border-[#1E1E2D] text-[#1E1E2D]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Tải ảnh lên
                        </button>
                        <button type="button" @click="imgTab = 'url'"
                            class="pb-3 pt-3 text-sm font-medium border-b-2 transition-colors"
                            :class="imgTab === 'url' ? 'border-[#1E1E2D] text-[#1E1E2D]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Từ URL
                        </button>
                    </div>

                    <div class="p-5 space-y-4">

                        <!-- Tab: Upload -->
                        <template v-if="imgTab === 'upload'">
                            <!-- Drop zone -->
                            <label class="block cursor-pointer">
                                <div class="border-2 border-dashed rounded-xl transition-colors"
                                     :class="imgFilePreview ? 'border-transparent p-0' : 'border-gray-200 hover:border-[#5B9FD6] p-8'">
                                    <template v-if="imgFilePreview">
                                        <div class="relative group">
                                            <img :src="imgFilePreview" class="w-full max-h-56 object-contain rounded-xl bg-gray-50" alt="Preview">
                                            <button type="button"
                                                @click.prevent="clearFile"
                                                class="absolute top-2 right-2 p-1 rounded-full bg-black/50 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="flex flex-col items-center gap-2 text-center">
                                            <svg class="w-10 h-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/></svg>
                                            <p class="text-sm text-gray-500">Kéo thả hoặc <span class="text-[#5B9FD6] font-medium">chọn ảnh</span></p>
                                            <p class="text-xs text-gray-400">JPG, PNG, WebP — tối đa 4MB</p>
                                        </div>
                                    </template>
                                </div>
                                <input ref="imgFileInput" type="file" accept="image/*" class="hidden" @change="onImgFileChange">
                            </label>
                        </template>

                        <!-- Tab: URL -->
                        <template v-else>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">URL ảnh</label>
                                <input v-model="imgUrl"
                                    type="url"
                                    placeholder="https://example.com/image.jpg"
                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                            </div>
                            <!-- URL preview -->
                            <div v-if="imgUrl.startsWith('http')"
                                 class="rounded-xl border border-gray-100 overflow-hidden bg-gray-50 flex items-center justify-center" style="min-height:120px">
                                <img :src="imgUrl" class="max-h-48 max-w-full object-contain"
                                     alt="Preview"
                                     @error="e => e.target.style.display='none'">
                            </div>
                        </template>

                        <!-- Size selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kích thước</label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="w in IMG_WIDTHS" :key="w.value" type="button"
                                    @click="imgWidth = w.value"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                    :class="imgWidth === w.value
                                        ? 'bg-[#1E1E2D] text-white border-[#1E1E2D]'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'">
                                    {{ w.label }}
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100">
                        <button type="button" @click="showImgModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                            Huỷ
                        </button>
                        <button type="button"
                            @click="insertImg"
                            :disabled="!canInsert || imgUploading"
                            class="px-5 py-2 text-sm bg-[#1E1E2D] text-white rounded-xl hover:bg-[#2a2a3d] disabled:opacity-40 transition-colors flex items-center gap-2">
                            <svg v-if="imgUploading" class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            {{ imgUploading ? 'Đang tải...' : 'Chèn ảnh' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
.ProseMirror {
    padding: 1rem 1.25rem;
    outline: none;
    min-height: 320px;
    font-size: 0.9375rem;
    line-height: 1.75;
    color: #1f2937;
}
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #9ca3af;
    pointer-events: none;
    height: 0;
}
.ProseMirror h1 { font-size: 1.75rem; font-weight: 700; margin: 1.25rem 0 .5rem; line-height: 1.2; }
.ProseMirror h2 { font-size: 1.375rem; font-weight: 600; margin: 1.1rem 0 .4rem; line-height: 1.3; }
.ProseMirror h3 { font-size: 1.125rem; font-weight: 600; margin: 1rem 0 .35rem; }
.ProseMirror p  { margin: .5rem 0; }
.ProseMirror strong { font-weight: 600; }
.ProseMirror em { font-style: italic; }
.ProseMirror u  { text-decoration: underline; text-underline-offset: 2px; }
.ProseMirror s  { text-decoration: line-through; }
.ProseMirror code { background: #f3f4f6; padding: .15rem .4rem; border-radius: 4px; font-size: .85em; font-family: monospace; color: #be123c; }
.ProseMirror pre  { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 8px; overflow-x: auto; margin: .75rem 0; }
.ProseMirror pre code { background: transparent; padding: 0; color: inherit; font-size: .875rem; }
.ProseMirror blockquote { border-left: 3px solid #e5e7eb; padding-left: 1rem; color: #6b7280; margin: .75rem 0; font-style: italic; }
.ProseMirror ul { list-style: disc; padding-left: 1.5rem; margin: .5rem 0; }
.ProseMirror ol { list-style: decimal; padding-left: 1.5rem; margin: .5rem 0; }
.ProseMirror li { margin: .2rem 0; }
.ProseMirror hr { border: 0; border-top: 1px solid #e5e7eb; margin: 1.25rem 0; }
.ProseMirror a  { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; cursor: pointer; }
.ProseMirror img {
    display: block;
    border-radius: 8px;
    margin: .75rem 0;
    max-width: 100%;
    height: auto;
}
.ProseMirror img.ProseMirror-selectednode {
    outline: 2px solid #5B9FD6;
    border-radius: 8px;
}
</style>

<style scoped>
.img-fade-enter-active, .img-fade-leave-active { transition: opacity 0.15s; }
.img-fade-enter-from, .img-fade-leave-to { opacity: 0; }
</style>
