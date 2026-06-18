<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import axios from 'axios'

const props = defineProps({
    authors: { type: Array, default: () => [] },
})

const list = ref([...props.authors])

// ── Create ────────────────────────────────────────────────────────────────────
const showCreate = ref(false)
const creating   = ref(false)
const createErr  = ref('')
const newAuthor  = ref({ name: '', email: '' })

async function submitCreate() {
    createErr.value = ''
    creating.value  = true
    try {
        const { data } = await axios.post(route('admin.authors.store'), newAuthor.value)
        list.value.unshift(data)
        newAuthor.value  = { name: '', email: '' }
        showCreate.value = false
    } catch (e) {
        const errs = e.response?.data?.errors
        createErr.value = errs
            ? Object.values(errs)[0][0]
            : (e.response?.data?.message ?? 'Error')
    } finally {
        creating.value = false
    }
}

// ── Edit ──────────────────────────────────────────────────────────────────────
const editingId = ref(null)
const editData  = reactive({
    name: '', bio: '', job_title: '',
    website: '', twitter_url: '', linkedin_url: '', facebook_url: '',
})
const editBusy = ref(false)
const editErr  = ref('')

function startEdit(a) {
    editingId.value       = a.id
    editData.name         = a.name         ?? ''
    editData.bio          = a.bio          ?? ''
    editData.job_title    = a.job_title    ?? ''
    editData.website      = a.website      ?? ''
    editData.twitter_url  = a.twitter_url  ?? ''
    editData.linkedin_url = a.linkedin_url ?? ''
    editData.facebook_url = a.facebook_url ?? ''
    editErr.value         = ''
}

function cancelEdit() {
    editingId.value = null
    editErr.value   = ''
}

async function saveEdit(a) {
    editErr.value  = ''
    editBusy.value = true
    try {
        const { data } = await axios.patch(route('admin.authors.update', a.id), { ...editData })
        const idx = list.value.findIndex(x => x.id === a.id)
        if (idx !== -1) Object.assign(list.value[idx], data)
        editingId.value = null
    } catch (e) {
        const errs = e.response?.data?.errors
        editErr.value = errs ? Object.values(errs)[0][0] : 'Error saving'
    } finally {
        editBusy.value = false
    }
}

// ── Remove ────────────────────────────────────────────────────────────────────
const removeTarget = ref(null)
const removing     = ref(false)

async function confirmRemove() {
    removing.value = true
    try {
        await axios.delete(route('admin.authors.destroy', removeTarget.value.id))
        list.value         = list.value.filter(a => a.id !== removeTarget.value.id)
        removeTarget.value = null
    } finally {
        removing.value = false
    }
}
</script>

<template>
    <Head title="Admin — Authors" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#1E1E2D] tracking-tight">Authors</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ list.length }} authors</p>
            </div>
            <button @click="showCreate = !showCreate; createErr = ''"
                class="shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                :class="showCreate
                    ? 'border border-red-200 text-red-500 hover:bg-red-50'
                    : 'bg-[#1E1E2D] text-white hover:bg-[#2a2a3d]'">
                {{ showCreate ? '✕ Cancel' : '+ New Author' }}
            </button>
        </div>

        <!-- Create form -->
        <Transition name="slide">
            <div v-if="showCreate"
                class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm mb-5">
                <h3 class="text-sm font-semibold text-[#1E1E2D] mb-4">New Author</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Name</label>
                        <input v-model="newAuthor.name"
                            placeholder="Full name..."
                            @keyup.enter="submitCreate"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                        <input v-model="newAuthor.email"
                            type="email"
                            placeholder="email@example.com"
                            @keyup.enter="submitCreate"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition" />
                    </div>
                </div>
                <p v-if="createErr" class="text-xs text-red-500 mb-3">{{ createErr }}</p>
                <p class="text-xs text-gray-400 mb-4">
                    A random password will be set. The author can reset it via "Forgot Password".
                </p>
                <div class="flex gap-3">
                    <button @click="submitCreate"
                        :disabled="creating || !newAuthor.name || !newAuthor.email"
                        class="px-5 py-2 text-sm bg-[#1E1E2D] text-white rounded-xl hover:bg-[#2a2a3d] disabled:opacity-40 transition-colors">
                        {{ creating ? 'Creating...' : 'Create Author' }}
                    </button>
                    <button @click="showCreate = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Authors table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Author</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Email</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Posts</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Joined</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="a in list" :key="a.id">

                            <!-- Row -->
                            <tr class="border-t border-gray-50 hover:bg-gray-50/60 transition-colors"
                                :class="{ 'bg-[#EEF4FB]/30': editingId === a.id }">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#EEF4FB] flex items-center justify-center text-[#5B9FD6] text-xs font-bold shrink-0">
                                            {{ a.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-[#1E1E2D]">{{ a.name }}</div>
                                            <div v-if="a.job_title" class="text-xs text-gray-400 mt-0.5">{{ a.job_title }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-500 text-xs">{{ a.email }}</td>

                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                                        {{ a.blog_posts_count ?? 0 }} posts
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-gray-400 text-xs whitespace-nowrap">
                                    {{ a.created_at?.slice(0, 10) ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editingId === a.id ? cancelEdit() : startEdit(a)"
                                            class="p-1.5 rounded-lg transition-colors"
                                            :class="editingId === a.id
                                                ? 'text-[#5B9FD6] bg-[#EEF4FB]'
                                                : 'text-gray-400 hover:text-[#5B9FD6] hover:bg-[#EEF4FB]'">
                                            <svg v-if="editingId !== a.id" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                            <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button @click="removeTarget = a"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit panel (expands below row) -->
                            <Transition name="expand">
                                <tr v-if="editingId === a.id" class="border-t border-[#EEF4FB]">
                                    <td colspan="5" class="px-6 py-5 bg-[#F8FBFF]">
                                        <div class="max-w-3xl">
                                            <p class="text-xs font-semibold text-[#5B9FD6] uppercase tracking-wide mb-4">Edit Author — SEO Profile</p>

                                            <!-- Row 1: Name + Job Title -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Name <span class="text-red-400">*</span></label>
                                                    <input v-model="editData.name"
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Job Title</label>
                                                    <input v-model="editData.job_title"
                                                        placeholder="e.g. Content Writer"
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                            </div>

                                            <!-- Bio -->
                                            <div class="mb-4">
                                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Bio <span class="text-gray-400 font-normal">(used in JSON-LD author description)</span></label>
                                                <textarea v-model="editData.bio"
                                                    rows="3"
                                                    placeholder="Short biography..."
                                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white resize-none" />
                                            </div>

                                            <!-- Row 2: Website + Social -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Website</label>
                                                    <input v-model="editData.website"
                                                        type="url"
                                                        placeholder="https://example.com"
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Twitter / X</label>
                                                    <input v-model="editData.twitter_url"
                                                        type="url"
                                                        placeholder="https://twitter.com/..."
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">LinkedIn</label>
                                                    <input v-model="editData.linkedin_url"
                                                        type="url"
                                                        placeholder="https://linkedin.com/in/..."
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Facebook</label>
                                                    <input v-model="editData.facebook_url"
                                                        type="url"
                                                        placeholder="https://facebook.com/..."
                                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#5B9FD6]/30 focus:border-[#5B9FD6] transition bg-white" />
                                                </div>
                                            </div>

                                            <p v-if="editErr" class="text-xs text-red-500 mb-3">{{ editErr }}</p>

                                            <div class="flex gap-3">
                                                <button @click="saveEdit(a)" :disabled="editBusy || !editData.name"
                                                    class="px-5 py-2 text-sm bg-[#1E1E2D] text-white rounded-xl hover:bg-[#2a2a3d] disabled:opacity-40 transition-colors">
                                                    {{ editBusy ? 'Saving...' : 'Save' }}
                                                </button>
                                                <button @click="cancelEdit"
                                                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </Transition>

                        </template>

                        <tr v-if="!list.length">
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                                No authors yet. Create one above.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confirm remove -->
        <ConfirmDialog
            :model-value="!!removeTarget"
            title="Remove author"
            :description="`Remove '${removeTarget?.name}' from the authors list? Their posts will remain but they will lose author access.`"
            danger
            confirm-label="Remove"
            @update:model-value="val => { if (!val) removeTarget = null }"
            @confirm="confirmRemove"
        />

    </AdminLayout>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.2s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }

.expand-enter-active, .expand-leave-active { transition: all 0.2s ease; }
.expand-enter-from, .expand-leave-to { opacity: 0; }
</style>
