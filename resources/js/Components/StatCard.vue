<script setup>
defineProps({
    label:   { type: String, required: true },
    value:   { type: [String, Number], required: true },
    color:   { type: String, default: 'gray' },
    tooltip: { type: String, default: null },
})
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-1" :title="tooltip ?? undefined">
        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ label }}</p>
        <p class="text-2xl font-semibold"
           :class="{
               'text-green-600': color === 'green',
               'text-red-600':   color === 'red',
               'text-gray-900':  color === 'gray',
           }">
            {{ value }}
        </p>
    </div>
</template>
