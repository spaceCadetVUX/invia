<script setup>
import { Link } from '@inertiajs/vue3'

const nav = [
    { label: 'Dashboard',     href: route('admin.dashboard') },
    { label: 'Users',         href: route('admin.users.index') },
    { label: 'Templates',     href: route('admin.templates.index') },
    { label: 'Music',         href: route('admin.music.index') },
    { label: 'Events',        href: route('admin.events.index') },
    { label: 'Coupons',       href: route('admin.coupons.index') },
    { label: 'Blog',          href: route('admin.blog.index') },
    { label: 'Announcements', href: route('admin.announcements.index') },
]
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <aside class="w-56 bg-gray-900 text-gray-200 flex flex-col py-6 shrink-0">
            <div class="px-5 mb-8 font-bold text-white text-lg">Invia Admin</div>
            <nav class="flex-1">
                <Link
                    v-for="item in nav" :key="item.href"
                    :href="item.href"
                    class="flex items-center px-5 py-2.5 text-sm hover:bg-gray-800 transition"
                    :class="$page.url.startsWith(item.href.replace(window.location.origin, '')) ? 'bg-gray-800 text-white' : 'text-gray-400'"
                >
                    {{ item.label }}
                </Link>
            </nav>
            <div class="px-5 text-xs text-gray-600">
                {{ $page.props.auth.user?.name }}
            </div>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <slot />
        </main>
    </div>
</template>
