<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const page       = usePage()
const expanded   = ref(false)
const mobileOpen = ref(false)

const nav = [
    {
        label: 'Dashboard',
        path: '/admin',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>`,
    },
    {
        label: 'Users',
        path: '/admin/users',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>`,
    },
    {
        label: 'Templates',
        path: '/admin/templates',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>`,
    },
    {
        label: 'Music',
        path: '/admin/music',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z"/></svg>`,
    },
    {
        label: 'Events',
        path: '/admin/events',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>`,
    },
    {
        label: 'Coupons',
        path: '/admin/coupons',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>`,
    },
    {
        label: 'Blog',
        path: '/admin/blog',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>`,
    },
    {
        label: 'Announcements',
        path: '/admin/announcements',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46"/></svg>`,
    },
]

function isActive(path) {
    if (path === '/admin') return page.url === '/admin' || page.url === '/admin/'
    return page.url.startsWith(path)
}

const showLabel = (forMobile = false) => forMobile ? true : expanded.value
</script>

<template>
    <div class="flex min-h-screen bg-[#F5F6FA]">

        <!-- Mobile backdrop -->
        <Transition name="fade-backdrop">
            <div
                v-if="mobileOpen"
                class="fixed inset-0 bg-black/40 z-20 lg:hidden"
                @click="mobileOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 h-screen bg-[#1E1E2D] flex flex-col py-5 z-30 transition-all duration-300 ease-in-out overflow-hidden"
            :class="[
                mobileOpen ? 'translate-x-0 w-52' : '-translate-x-full lg:translate-x-0',
                !mobileOpen ? (expanded ? 'lg:w-52' : 'lg:w-[68px]') : ''
            ]"
        >
            <!-- Logo + desktop toggle -->
            <div class="flex items-center px-3.5 mb-8"
                 :class="(expanded || mobileOpen) ? 'justify-between' : 'justify-center'">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-5 h-5">
                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                        </svg>
                    </div>
                    <Transition name="fade-label">
                        <span v-if="expanded || mobileOpen" class="text-white font-semibold text-sm tracking-wide whitespace-nowrap">Invia Admin</span>
                    </Transition>
                </div>
                <!-- Desktop collapse button -->
                <button
                    v-if="expanded && !mobileOpen"
                    @click="expanded = false"
                    class="hidden lg:block text-white/40 hover:text-white transition-colors ml-2 shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </button>
                <!-- Mobile close button -->
                <button
                    v-if="mobileOpen"
                    @click="mobileOpen = false"
                    class="lg:hidden text-white/40 hover:text-white transition-colors ml-2 shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Nav items -->
            <nav class="flex flex-col gap-1 flex-1 px-3">
                <Link
                    v-for="item in nav"
                    :key="item.path"
                    :href="item.path"
                    :title="(!expanded && !mobileOpen) ? item.label : undefined"
                    class="group relative flex items-center gap-3 h-10 rounded-xl px-2.5 transition-colors duration-150"
                    :class="isActive(item.path)
                        ? 'bg-white/15 text-white'
                        : 'text-white/40 hover:bg-white/8 hover:text-white/80'"
                    @click="mobileOpen = false"
                >
                    <!-- Active line -->
                    <span v-if="isActive(item.path)" class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-white rounded-full" />

                    <!-- Icon -->
                    <span class="shrink-0" v-html="item.icon" />

                    <!-- Label -->
                    <Transition name="fade-label">
                        <span v-if="expanded || mobileOpen" class="text-sm whitespace-nowrap overflow-hidden">{{ item.label }}</span>
                    </Transition>

                    <!-- Tooltip (desktop collapsed only) -->
                    <span
                        v-if="!expanded && !mobileOpen"
                        class="pointer-events-none absolute left-full ml-3 px-2.5 py-1 rounded-md bg-[#1E1E2D] text-white text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 shadow-lg border border-white/10"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </nav>

            <!-- Bottom: desktop expand toggle + avatar -->
            <div class="px-3 flex flex-col gap-3">
                <!-- Expand button (desktop collapsed only) -->
                <button
                    v-if="!expanded && !mobileOpen"
                    @click="expanded = true"
                    class="hidden lg:flex w-full h-10 items-center justify-center text-white/40 hover:text-white hover:bg-white/8 rounded-xl transition-colors"
                    title="Mở rộng"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>

                <!-- User -->
                <div class="flex items-center gap-3 px-0.5">
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold shrink-0 cursor-pointer"
                        :title="$page.props.auth.user?.name"
                    >
                        {{ $page.props.auth.user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <Transition name="fade-label">
                        <div v-if="expanded || mobileOpen" class="min-w-0">
                            <p class="text-white text-xs font-medium truncate">{{ $page.props.auth.user?.name }}</p>
                            <p class="text-white/40 text-xs truncate">{{ $page.props.auth.user?.email }}</p>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div
            class="flex-1 min-h-screen flex flex-col transition-all duration-300 ease-in-out ml-0"
            :class="expanded ? 'lg:ml-52' : 'lg:ml-[68px]'"
        >
            <!-- Mobile top bar -->
            <div class="lg:hidden flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                <button
                    @click="mobileOpen = true"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-[#1E1E2D] rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-3.5 h-3.5">
                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-[#1E1E2D]">Invia Admin</span>
                </div>
            </div>

            <main class="flex-1 p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-label-enter-active { transition: opacity 0.15s ease 0.1s, transform 0.15s ease 0.1s; }
.fade-label-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.fade-label-enter-from, .fade-label-leave-to { opacity: 0; transform: translateX(-6px); }

.fade-backdrop-enter-active { transition: opacity 0.2s ease; }
.fade-backdrop-leave-active { transition: opacity 0.15s ease; }
.fade-backdrop-enter-from, .fade-backdrop-leave-to { opacity: 0; }
</style>
