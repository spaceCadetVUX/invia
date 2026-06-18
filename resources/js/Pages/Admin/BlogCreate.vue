<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BlogPostForm from '@/Components/BlogPostForm.vue'

const props = defineProps({
    authors: { type: Array, default: () => [] },
})

const form = useForm({
    author_id:        null,
    title:            '',
    excerpt:          '',
    content:          '',
    is_published:     false,
    cover_image:      null,
    meta_title:       '',
    meta_description: '',
    faq:              [],
})

function submit(asDraft = false) {
    if (asDraft) form.is_published = false
    form.post(route('admin.blog.store'), { forceFormData: true })
}
</script>

<template>
    <Head title="Admin — New Post" />
    <AdminLayout>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.blog.index')"
                    class="p-2 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-[#1E1E2D] tracking-tight">New Post</h1>
                    <p class="text-xs text-gray-400 mt-0.5">Create a new blog post</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="submit(true)"
                    :disabled="form.processing"
                    class="px-4 py-2.5 border border-gray-200 text-sm font-medium rounded-xl hover:bg-gray-50 disabled:opacity-50 transition-colors text-gray-600">
                    Save Draft
                </button>
                <button @click="submit(false)"
                    :disabled="form.processing"
                    class="flex items-center gap-2 px-5 py-2.5 bg-[#1E1E2D] text-white text-sm font-medium rounded-xl hover:bg-[#2a2a3d] disabled:opacity-50 transition-colors">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    {{ form.processing ? 'Saving...' : 'Publish' }}
                </button>
            </div>
        </div>

        <BlogPostForm :form="form" :authors="authors" />

    </AdminLayout>
</template>
