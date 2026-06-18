<script setup>
import { Head, router, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    posts: { type: Object, required: true },
})

const deleteTarget = ref(null)

function deletePost() {
    router.delete(route('admin.blog.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
    })
}

const blogUrl = (slug) => `/blog/${slug}`
</script>

<template>
    <Head title="Admin — Blog" />
    <AdminLayout>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#1E1E2D] tracking-tight">Blog</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ posts.total }} posts</p>
            </div>
            <Link :href="route('admin.blog.create')"
                class="shrink-0 px-4 py-2 rounded-xl bg-[#1E1E2D] text-white text-sm font-medium hover:bg-[#2a2a3d] transition-colors text-center">
                + New Post
            </Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Title</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Slug / URL</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Author</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide">Published</th>
                            <th class="px-6 py-3.5 text-xs font-medium text-gray-400 uppercase tracking-wide text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="post in posts.data" :key="post.id"
                            class="hover:bg-gray-50/60 transition-colors">

                            <td class="px-6 py-4 font-medium text-[#1E1E2D] max-w-[220px] truncate">
                                {{ post.title }}
                            </td>

                            <td class="px-6 py-4">
                                <a :href="blogUrl(post.slug)" target="_blank"
                                   class="text-xs text-[#5B9FD6] hover:underline font-mono truncate max-w-[180px] block"
                                   :title="blogUrl(post.slug)">
                                    /blog/{{ post.slug }}
                                </a>
                            </td>

                            <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                {{ post.author?.name ?? '—' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium"
                                      :class="post.is_published
                                          ? 'bg-emerald-100 text-emerald-700'
                                          : 'bg-amber-100 text-amber-700'">
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-400 text-xs whitespace-nowrap">
                                {{ post.published_at?.slice(0, 10) ?? '—' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.blog.edit', post.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-[#5B9FD6] hover:bg-[#EEF4FB] transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                        </svg>
                                    </Link>
                                    <button @click="deleteTarget = post"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!posts.data.length">
                            <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                                No posts yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="posts.last_page > 1" class="mt-5 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Showing {{ posts.from }}–{{ posts.to }} of {{ posts.total }} posts
            </p>
            <div class="flex gap-1">
                <button v-for="link in posts.links" :key="link.label"
                    v-html="link.label"
                    :disabled="!link.url"
                    @click="link.url && router.get(link.url)"
                    class="min-w-[36px] h-9 px-2.5 text-sm rounded-xl border transition-colors"
                    :class="link.active
                        ? 'bg-[#1E1E2D] text-white border-[#1E1E2D]'
                        : link.url
                            ? 'bg-white text-gray-600 border-gray-200 hover:border-[#5B9FD6] hover:text-[#5B9FD6]'
                            : 'bg-white text-gray-300 border-gray-100 cursor-not-allowed'" />
            </div>
        </div>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Delete post"
            :description="`Delete '${deleteTarget?.title}'? This action cannot be undone.`"
            danger
            confirm-label="Delete"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="deletePost"
        />
    </AdminLayout>
</template>
