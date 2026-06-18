<script setup>
import { usePage } from '@inertiajs/vue3'

const { announcement } = usePage().props
</script>

<template>
    <div v-if="announcement"
         class="px-4 py-2 text-sm text-center"
         :class="{
             'bg-blue-50 text-blue-800':     announcement.type === 'info',
             'bg-yellow-50 text-yellow-800': announcement.type === 'warning',
             'bg-red-50 text-red-800':       announcement.type === 'error',
         }">
        <strong>{{ announcement.title }}</strong>
        <span v-if="announcement.body" class="ml-2">{{ announcement.body }}</span>
    </div>
</template>
