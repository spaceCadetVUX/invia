<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'

const page       = usePage()
const mobileOpen = ref(false)

const expanded = ref(localStorage.getItem('sidebar-expanded') === 'true')

function syncSidebarVar(val) {
    document.documentElement.style.setProperty('--admin-sidebar-w', val ? '240px' : '68px')
}

watch(expanded, (val) => {
    localStorage.setItem('sidebar-expanded', String(val))
    syncSidebarVar(val)
})

onMounted(() => syncSidebarVar(expanded.value))

const ICON = {
    dashboard:     `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>`,
    users:         `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>`,
    templates:     `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>`,
    blog:          `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>`,
    authors:       `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>`,
    events:        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>`,
    music:         `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z"/></svg>`,
    coupons:       `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>`,
    announcements: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-[18px] h-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46"/></svg>`,
}

const navSections = [
    {
        label: null,
        items: [
            { label: 'Dashboard',  path: '/admin',           icon: ICON.dashboard  },
            { label: 'Users',      path: '/admin/users',     icon: ICON.users      },
            { label: 'Templates',  path: '/admin/templates', icon: ICON.templates  },
        ],
    },
    {
        label: 'Content',
        items: [
            { label: 'Blog',           path: '/admin/blog',          icon: ICON.blog          },
            { label: 'Authors',        path: '/admin/authors',       icon: ICON.authors       },
            { label: 'Events',         path: '/admin/events',        icon: ICON.events        },
            { label: 'Music',          path: '/admin/music',         icon: ICON.music         },
            { label: 'Announcements',  path: '/admin/announcements', icon: ICON.announcements },
        ],
    },
    {
        label: 'Manage',
        items: [
            { label: 'Coupons', path: '/admin/coupons', icon: ICON.coupons },
        ],
    },
]

function isActive(path) {
    if (path === '/admin') return page.url === '/admin' || page.url === '/admin/'
    return page.url.startsWith(path)
}

function getItemOffset(si, ii) {
    let offset = 0
    for (let i = 0; i < si; i++) offset += navSections[i].items.length
    return offset + ii
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-[#F4F5F7]">

        <!-- Mobile backdrop -->
        <Transition name="fade-backdrop">
            <div v-if="mobileOpen"
                class="fixed inset-0 bg-black/30 backdrop-blur-sm z-20 lg:hidden"
                @click="mobileOpen = false" />
        </Transition>

        <!-- ── Sidebar ─────────────────────────────────────────────────────── -->
        <aside
            class="fixed top-0 left-0 h-full z-30 flex-shrink-0 sidebar-transition
                   lg:relative lg:top-auto lg:left-auto lg:translate-x-0"
            :class="[
                mobileOpen ? 'translate-x-0 w-60' : '-translate-x-full lg:translate-x-0',
                !mobileOpen ? (expanded ? 'lg:w-60' : 'lg:w-[68px]') : '',
            ]"
        >
            <!-- Inner scroll+clip wrapper -->
            <div class="h-full w-full bg-white border-r border-gray-100 flex flex-col overflow-hidden">

            <!-- ── Logo ── -->
            <div class="flex items-center px-3.5 pt-5 pb-4"
                 :class="(expanded || mobileOpen) ? 'justify-between' : ''">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center
                                bg-gradient-to-br from-[#0081A7] to-[#00AFB9]
                                shadow-[0_4px_14px_rgba(0,129,167,0.45)]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-4 h-4">
                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                        </svg>
                    </div>
                    <Transition name="fade-label">
                        <span v-if="expanded || mobileOpen" class="whitespace-nowrap">
                            <span class="text-gray-900 font-bold text-sm">Invia</span>
                            <span class="text-gray-400 font-normal text-sm"> Admin</span>
                        </span>
                    </Transition>
                </div>
                <!-- Mobile close only -->
                <button v-if="mobileOpen" @click="mobileOpen = false"
                    class="lg:hidden flex items-center justify-center w-6 h-6 rounded-lg
                           text-gray-300 hover:text-gray-500 transition-all ml-2 shrink-0">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- ── Workspace selector ── -->
            <div class="px-3.5 mb-4">
                <button
                    class="w-full flex items-center gap-2.5 h-10 overflow-hidden rounded-xl transition-all duration-[320ms]"
                    :class="(expanded || mobileOpen)
                        ? 'px-3 bg-gray-50 border border-gray-100 hover:bg-gray-100'
                        : 'px-2 hover:bg-gray-50'"
                >
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-[#0081A7] to-[#00AFB9]
                                flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                        I
                    </div>
                    <Transition name="fade-label">
                        <div v-if="expanded || mobileOpen"
                            :style="{ '--stagger': '0.07s' }"
                            class="flex-1 min-w-0 text-left">
                            <p class="text-xs font-semibold text-gray-700 truncate">Invia.vn</p>
                            <p class="text-[10px] text-gray-400 truncate">Workspace</p>
                        </div>
                    </Transition>
                    <Transition name="fade-label">
                        <svg v-if="expanded || mobileOpen"
                            :style="{ '--stagger': '0.09s' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                            class="w-3 h-3 text-gray-300 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </Transition>
                </button>
            </div>

            <!-- ── Nav sections ── -->
            <nav class="flex-1 overflow-y-auto px-3 space-y-4 scrollbar-none">
                <div v-for="(section, si) in navSections" :key="si">

                    <!-- Section label -->
                    <Transition name="fade-label">
                        <p v-if="section.label && (expanded || mobileOpen)"
                            :style="{ '--stagger': '0.1s' }"
                            class="px-2 mb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">
                            {{ section.label }}
                        </p>
                    </Transition>

                    <!-- Nav items -->
                    <div class="space-y-0.5">
                        <Link
                            v-for="(item, ii) in section.items" :key="item.path"
                            :href="item.path"
                            class="group relative flex items-center gap-3 h-9 rounded-xl px-3 transition-all duration-200"
                            :class="[
                                isActive(item.path)
                                    ? 'bg-gradient-to-r from-[#0081A7] to-[#00AFB9] text-white'
                                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
                            ]"
                            @click="mobileOpen = false"
                        >
                            <span class="shrink-0" v-html="item.icon" />

                            <Transition name="fade-label">
                                <span v-if="expanded || mobileOpen"
                                    :style="{ '--stagger': `${0.12 + getItemOffset(si, ii) * 0.022}s` }"
                                    class="text-[13px] font-medium whitespace-nowrap overflow-hidden">
                                    {{ item.label }}
                                </span>
                            </Transition>

                            <!-- Tooltip when collapsed -->
                            <span v-if="!expanded && !mobileOpen"
                                class="nav-tooltip pointer-events-none absolute left-full ml-3
                                       px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-medium
                                       whitespace-nowrap opacity-0 group-hover:opacity-100
                                       transition-opacity duration-150 shadow-xl">
                                {{ item.label }}
                            </span>
                        </Link>
                    </div>

                </div>
            </nav>

            <!-- ── Bottom ── -->
            <div class="px-3.5 pb-5 pt-3 space-y-3">

                <!-- Upgrade card (expanded only) -->
                <Transition name="fade-label">
                    <div v-if="expanded || mobileOpen"
                        :style="{ '--stagger': '0.11s' }"
                        class="rounded-2xl bg-gradient-to-br from-[#0081A7]/8 to-[#00AFB9]/8
                               border border-[#00AFB9]/20 p-3.5">
                        <p class="text-xs font-semibold text-gray-700 mb-0.5">Invia Platform</p>
                        <p class="text-[11px] text-gray-400 mb-3 leading-relaxed">
                            Wedding invitation builder — manage templates & content
                        </p>
                        <div class="flex items-center justify-between text-[10px] text-gray-400 mb-1.5">
                            <span>Templates used</span><span class="font-medium text-gray-600">3 / 10</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full w-[30%] bg-gradient-to-r from-[#0081A7] to-[#00AFB9] rounded-full" />
                        </div>
                    </div>
                </Transition>

                <!-- Divider -->
                <div class="h-px bg-gray-100" />

                <!-- User -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#F07167] to-[#FED9B7]
                                ring-2 ring-white ring-offset-1 ring-offset-gray-50
                                flex items-center justify-center text-white text-xs font-bold shrink-0 cursor-pointer"
                        :title="$page.props.auth.user?.name">
                        {{ $page.props.auth.user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <Transition name="fade-label">
                        <div v-if="expanded || mobileOpen"
                            :style="{ '--stagger': '0.08s' }"
                            class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-gray-700 truncate">{{ $page.props.auth.user?.name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $page.props.auth.user?.email }}</p>
                        </div>
                    </Transition>
                </div>

            </div>

            </div><!-- end inner wrapper -->

            <!-- ── Desktop toggle — 1 nút, luôn cùng vị trí ── -->
            <button
                @click="expanded = !expanded"
                class="hidden lg:flex absolute -right-3 top-7 z-10
                       w-6 h-6 rounded-full bg-white border border-gray-200 shadow-md
                       items-center justify-center text-gray-400 hover:text-gray-700
                       hover:border-gray-300 hover:shadow-lg transition-all duration-200"
            >
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                    class="w-2.5 h-2.5"
                    style="transition: transform 320ms cubic-bezier(0.65, 0, 0.35, 1);"
                    :class="expanded ? '' : 'rotate-180'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
            </button>

        </aside>

        <!-- ── Main ───────────────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 flex flex-col overflow-y-auto">

            <!-- Mobile top bar -->
            <div class="lg:hidden flex items-center gap-3 px-4 py-3
                        bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm">
                <button @click="mobileOpen = true"
                    class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-[#0081A7] to-[#00AFB9]
                                flex items-center justify-center shadow-[0_2px_8px_rgba(91,159,214,0.4)]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-3.5 h-3.5">
                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-900">
                        <span class="font-bold">Invia</span>
                        <span class="font-normal text-gray-400"> Admin</span>
                    </span>
                </div>
            </div>

            <Transition name="page" mode="out-in">
                <main class="flex-1 p-4 lg:py-8 lg:px-6" :key="page.url">
                    <slot />
                </main>
            </Transition>
        </div>

    </div>
</template>

<style scoped>
/* Sidebar: Apple-style smooth deceleration */
.sidebar-transition {
    will-change: width;
    transition: width 320ms cubic-bezier(0.65, 0, 0.35, 1),
                transform 320ms cubic-bezier(0.65, 0, 0.35, 1);
}
/* Label fade — stagger delay via CSS custom property --stagger */
.fade-label-enter-active {
    will-change: transform, opacity;
    transition: opacity 0.22s ease var(--stagger, 0.13s),
                transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1) var(--stagger, 0.13s);
}
.fade-label-leave-active {
    transition: opacity 0.09s ease, transform 0.09s ease;
}
.fade-label-enter-from,
.fade-label-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.fade-backdrop-enter-active { transition: opacity 0.24s ease; }
.fade-backdrop-leave-active  { transition: opacity 0.16s ease; }
.fade-backdrop-enter-from,
.fade-backdrop-leave-to      { opacity: 0; }

/* Hide scrollbar on nav */
.scrollbar-none { scrollbar-width: none; }
.scrollbar-none::-webkit-scrollbar { display: none; }

/* Page transition */
.page-enter-active {
    transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.34, 1.3, 0.64, 1);
}
.page-leave-active {
    transition: opacity 0.12s ease, transform 0.12s ease;
}
.page-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.page-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
