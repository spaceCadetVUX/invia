<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AnnouncementBanner from '@/Components/AnnouncementBanner.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const flash = computed(() => page.props.flash)
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link href="/dashboard" class="text-xl font-semibold text-gray-900 tracking-tight">
                    Invia
                </Link>
                <div class="flex items-center gap-4">
                    <Link href="/dashboard/events/create"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                        + Tạo thiệp mới
                    </Link>
                    <Link href="/profile" class="text-sm text-gray-600 hover:text-gray-900">
                        {{ user?.name }}
                    </Link>
                </div>
            </div>
        </nav>

        <!-- System announcement -->
        <AnnouncementBanner />

        <!-- Flash messages -->
        <div v-if="flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md text-sm">
                {{ flash.success }}
            </div>
        </div>

        <!-- Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>
