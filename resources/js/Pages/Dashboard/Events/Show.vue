<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    event: Object,
})
</script>

<template>
    <Head :title="event.title" />
    <DashboardLayout>
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <h1 class="text-2xl font-semibold text-gray-900">{{ event.title }}</h1>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 uppercase tracking-wide">
                    {{ event.status }}
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-4 text-sm text-gray-700">
                <p><span class="font-medium">Slug:</span> /thiep/{{ event.slug }}</p>
                <p><span class="font-medium">Ngày:</span> {{ event.event_date }}</p>
                <p v-if="event.venue_name"><span class="font-medium">Địa điểm:</span> {{ event.venue_name }}</p>
                <p><span class="font-medium">Loại:</span> {{ event.event_type }}</p>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                Dashboard đầy đủ sẽ được build ở Phase 2.
            </p>
        </div>
    </DashboardLayout>
</template>
