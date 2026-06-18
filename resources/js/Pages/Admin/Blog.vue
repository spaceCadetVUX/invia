<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

defineProps({
    posts: { type: Object, required: true },
})

const showForm   = ref(false)
const editTarget = ref(null)
const form       = ref({ title: '', excerpt: '', content: '', is_published: false })

function openCreate() {
    editTarget.value = null
    form.value = { title: '', excerpt: '', content: '', is_published: false }
    showForm.value = true
}

function openEdit(post) {
    editTarget.value = post
    form.value = {
        title:        post.title,
        excerpt:      post.excerpt ?? '',
        content:      post.content,
        is_published: post.is_published,
    }
    showForm.value = true
}

function submitForm() {
    if (editTarget.value) {
        router.patch(route('admin.blog.update', editTarget.value.id), form.value, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    } else {
        router.post(route('admin.blog.store'), form.value, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false },
        })
    }
}

const deleteTarget = ref(null)

function deletePost() {
    router.delete(route('admin.blog.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<template>
    <Head title="Admin — Blog" />
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold">Blog Posts</h1>
            <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 transition">
                + Bài viết mới
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b bg-gray-50">
                        <th class="px-4 py-3 font-medium">Tiêu đề</th>
                        <th class="px-4 py-3 font-medium">Tác giả</th>
                        <th class="px-4 py-3 font-medium">Trạng thái</th>
                        <th class="px-4 py-3 font-medium">Đăng lúc</th>
                        <th class="px-4 py-3 font-medium">Tạo lúc</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="post in posts.data" :key="post.id" class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium max-w-[300px] truncate">{{ post.title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ post.author?.name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  :class="post.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                {{ post.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ post.published_at?.slice(0, 10) ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ post.created_at?.slice(0, 10) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                <button @click="openEdit(post)" class="text-xs text-indigo-600 hover:underline">Sửa</button>
                                <button @click="deleteTarget = post" class="text-xs text-red-600 hover:underline">Xóa</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!posts.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Chưa có bài viết nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="posts.last_page > 1" class="mt-4 flex gap-2">
            <a v-for="link in posts.links" :key="link.label"
               v-html="link.label"
               :href="link.url ?? '#'"
               @click.prevent="link.url && router.get(link.url)"
               class="px-3 py-1.5 text-sm rounded border"
               :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'" />
        </div>

        <!-- Form modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showForm = false">
                    <div class="absolute inset-0 bg-black/50" @click="showForm = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                        <h3 class="text-lg font-semibold">{{ editTarget ? 'Sửa bài viết' : 'Bài viết mới' }}</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                                <input v-model="form.title" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tóm tắt</label>
                                <textarea v-model="form.excerpt" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung (Markdown)</label>
                                <textarea v-model="form.content" rows="12" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                            </div>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_published" type="checkbox" class="rounded" />
                                Publish ngay
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button @click="showForm = false" class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Huỷ</button>
                            <button @click="submitForm" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Lưu</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmDialog
            :model-value="!!deleteTarget"
            title="Xóa bài viết"
            :description="`Xóa bài '${deleteTarget?.title}'?`"
            danger
            confirm-label="Xóa"
            @update:model-value="val => { if (!val) deleteTarget = null }"
            @confirm="deletePost"
        />
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
