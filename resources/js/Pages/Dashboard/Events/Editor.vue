<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { useDraggable, useElementSize, useDebounceFn } from '@vueuse/core'
import axios from 'axios'

const props = defineProps({
    event:          Object,
    templateConfig: Object,
    templateMeta:   Object,
})

// ── State ──────────────────────────────────────────────
const canvasRef    = ref(null)
const { width: canvasW, height: canvasH } = useElementSize(canvasRef)
const saveStatus   = ref('idle')   // 'idle' | 'saving' | 'saved' | 'error'
const selectedSlot = ref(null)
const previewKey   = ref(0)

// Init settings: merge event.settings với defaults từ config.json
const settings = reactive(
    Object.fromEntries(
        Object.entries(props.templateConfig.slots).map(([key, def]) => [
            key,
            {
                x:     props.event.settings?.[key]?.x     ?? def.default_x,
                y:     props.event.settings?.[key]?.y     ?? def.default_y,
                font:  props.event.settings?.[key]?.font  ?? props.templateConfig.fonts[0],
                size:  props.event.settings?.[key]?.size  ?? 32,
                color: props.event.settings?.[key]?.color ?? '#000000',
                value: props.event.settings?.[key]?.value ?? null,
            },
        ])
    )
)

// ── Draggable slots ─────────────────────────────────────
const slotRefs = reactive({})

onMounted(async () => {
    await nextTick()
    Object.keys(props.templateConfig.slots).forEach(initSlotDrag)
})

function initSlotDrag(key) {
    if (!slotRefs[key]) return

    useDraggable(slotRefs[key], {
        initialValue: {
            x: (settings[key].x / 100) * canvasW.value,
            y: (settings[key].y / 100) * canvasH.value,
        },
        onEnd(pos) {
            settings[key].x = Math.round((pos.x / canvasW.value) * 1000) / 10
            settings[key].y = Math.round((pos.y / canvasH.value) * 1000) / 10
            // clamp 0–100
            settings[key].x = Math.min(100, Math.max(0, settings[key].x))
            settings[key].y = Math.min(100, Math.max(0, settings[key].y))
            triggerSave()
        },
    })
}

// ── Auto-save debounced ─────────────────────────────────
const triggerSave = useDebounceFn(async () => {
    saveStatus.value = 'saving'
    try {
        await axios.patch(route('dashboard.events.settings.save', props.event.slug), { settings })
        saveStatus.value = 'saved'
        previewKey.value++
    } catch {
        saveStatus.value = 'error'
    }
}, 1500)

watch(settings, triggerSave, { deep: true })

// ── Sidebar computed ────────────────────────────────────
const selectedConfig   = computed(() => selectedSlot.value ? props.templateConfig.slots[selectedSlot.value] : null)
const selectedSettings = computed(() => selectedSlot.value ? settings[selectedSlot.value] : null)
</script>

<template>
    <Head :title="`Editor — ${event.title}`" />

    <DashboardLayout>
        <!-- Toolbar -->
        <div class="flex items-center justify-between px-4 py-2 border-b bg-white">
            <a :href="route('dashboard.events.show', event.slug)"
               class="text-sm text-gray-600 hover:text-gray-900">← Quay lại</a>

            <span class="text-sm">
                <span v-if="saveStatus === 'saving'" class="text-gray-400">Đang lưu...</span>
                <span v-else-if="saveStatus === 'saved'" class="text-green-600">Đã lưu ✓</span>
                <span v-else-if="saveStatus === 'error'" class="text-red-500">Lỗi lưu</span>
            </span>

            <a :href="route('invitation.show', event.slug)" target="_blank"
               class="text-sm text-indigo-600 hover:underline">Xem thiệp ↗</a>
        </div>

        <!-- Editor body -->
        <div class="flex" style="height: calc(100vh - 56px);">

            <!-- Canvas -->
            <div class="flex-1 bg-gray-100 flex items-center justify-center overflow-hidden relative">
                <div
                    ref="canvasRef"
                    class="relative shadow-xl bg-white"
                    :style="{ width: templateConfig.canvas.width + 'px', height: templateConfig.canvas.height + 'px' }"
                >
                    <!-- Template background CSS -->
                    <link :href="`/templates/${templateMeta.blade_file}/style.css?v=${templateMeta.version}`" rel="stylesheet">
                    <div :class="`tmpl-${templateMeta.blade_file} w-full h-full pointer-events-none`"></div>

                    <!-- Draggable slot overlays -->
                    <div
                        v-for="(slotDef, key) in templateConfig.slots"
                        :key="key"
                        :ref="el => { if (el) slotRefs[key] = el }"
                        class="absolute cursor-move select-none"
                        :class="selectedSlot === key ? 'outline outline-2 outline-blue-500 outline-offset-2' : ''"
                        :style="{
                            left:       settings[key].x + '%',
                            top:        settings[key].y + '%',
                            transform:  'translate(-50%, -50%)',
                            fontFamily: settings[key].font,
                            fontSize:   settings[key].size + 'px',
                            color:      settings[key].color,
                            whiteSpace: 'nowrap',
                        }"
                        @click.stop="selectedSlot = key"
                    >
                        <template v-if="slotDef.type === 'text'">
                            {{ settings[key].value ?? slotDef.label }}
                        </template>
                        <template v-else-if="slotDef.type === 'image'">
                            <div class="w-24 h-24 border-2 border-dashed border-white/70 flex items-center justify-center text-white text-xs bg-black/10 rounded">
                                {{ slotDef.label }}
                            </div>
                        </template>
                        <template v-else-if="slotDef.type === 'video'">
                            <div class="w-32 h-20 border-2 border-dashed border-white/70 flex items-center justify-center text-white text-xs bg-black/10 rounded">
                                ▶ {{ slotDef.label }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="w-72 border-l bg-white flex flex-col overflow-y-auto">

                <!-- Slot list -->
                <div class="p-4 border-b">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2">Phần tử</h3>
                    <button
                        v-for="(slotDef, key) in templateConfig.slots"
                        :key="key"
                        class="w-full text-left px-3 py-2 rounded text-sm transition-colors"
                        :class="selectedSlot === key ? 'bg-blue-50 text-blue-700 font-medium' : 'hover:bg-gray-50 text-gray-700'"
                        @click="selectedSlot = key"
                    >
                        {{ slotDef.label }}
                        <span class="text-xs text-gray-400 ml-1">({{ slotDef.type }})</span>
                    </button>
                </div>

                <!-- Controls khi chọn text slot -->
                <div v-if="selectedSlot && selectedConfig?.type === 'text'" class="p-4 space-y-4 flex-1">
                    <h3 class="text-xs font-semibold uppercase text-gray-400">Tuỳ chỉnh</h3>

                    <div>
                        <label class="text-xs text-gray-600">Nội dung</label>
                        <input
                            type="text"
                            v-model="selectedSettings.value"
                            :placeholder="selectedConfig.label + ' (mặc định)'"
                            class="w-full border rounded px-2 py-1 text-sm mt-1 focus:outline-none focus:ring-1 focus:ring-blue-400"
                        >
                    </div>

                    <div>
                        <label class="text-xs text-gray-600">Font chữ</label>
                        <select v-model="selectedSettings.font"
                                class="w-full border rounded px-2 py-1 text-sm mt-1 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            <option v-for="f in templateConfig.fonts" :key="f" :value="f">{{ f }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-gray-600">Cỡ chữ</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="range" v-model.number="selectedSettings.size"
                                   min="8" max="120" class="flex-1 accent-indigo-600">
                            <span class="text-sm w-12 text-center text-gray-600">{{ selectedSettings.size }}px</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-600">Màu chữ</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="color" v-model="selectedSettings.color"
                                   class="w-8 h-8 rounded cursor-pointer border border-gray-200">
                            <input type="text" v-model="selectedSettings.color" maxlength="7"
                                   class="flex-1 border rounded px-2 py-1 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-600">X (%)</label>
                            <input type="number" v-model.number="selectedSettings.x"
                                   min="0" max="100"
                                   class="w-full border rounded px-2 py-1 text-sm mt-1 focus:outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Y (%)</label>
                            <input type="number" v-model.number="selectedSettings.y"
                                   min="0" max="100"
                                   class="w-full border rounded px-2 py-1 text-sm mt-1 focus:outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                    </div>
                </div>

                <!-- Placeholder khi chưa chọn slot -->
                <div v-else-if="!selectedSlot" class="p-4 flex-1 flex items-center justify-center text-sm text-gray-400 text-center">
                    Chọn một phần tử trên canvas để chỉnh sửa
                </div>

                <!-- Preview iframe -->
                <div class="p-4 border-t">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 mb-2">Preview</h3>
                    <iframe
                        :key="previewKey"
                        :src="route('dashboard.events.preview', event.slug)"
                        class="w-full rounded border bg-gray-50"
                        style="height: 240px;"
                        sandbox="allow-scripts allow-same-origin"
                    ></iframe>
                </div>
            </aside>
        </div>
    </DashboardLayout>
</template>
