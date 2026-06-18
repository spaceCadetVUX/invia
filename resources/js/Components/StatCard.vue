<script setup>
defineProps({
    label:   { type: String,  required: true },
    value:   { type: [String, Number], required: true },
    sub:     { type: String,  default: null },
    dark:    { type: Boolean, default: false },
    accent:  { type: String,  default: null }, // hex color for icon bg
    icon:    { type: String,  default: null }, // svg string
})
</script>

<template>
    <div
        class="rounded-2xl p-6 flex flex-col gap-3"
        :class="dark
            ? 'bg-[#1E1E2D] text-white'
            : 'bg-white text-[#1E1E2D] shadow-sm'"
    >
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium uppercase tracking-widest"
               :class="dark ? 'text-white/50' : 'text-gray-400'">
                {{ label }}
            </p>
            <div v-if="icon"
                 class="w-9 h-9 rounded-xl flex items-center justify-center"
                 :style="{ background: accent ?? (dark ? 'rgba(255,255,255,0.1)' : '#F0F4FF') }">
                <span v-html="icon" class="w-4 h-4"
                      :class="dark ? 'text-white' : 'text-[#5B9FD6]'" />
            </div>
        </div>
        <p class="text-3xl font-bold tracking-tight leading-none">{{ value }}</p>
        <p v-if="sub" class="text-xs" :class="dark ? 'text-white/40' : 'text-gray-400'">{{ sub }}</p>
    </div>
</template>
