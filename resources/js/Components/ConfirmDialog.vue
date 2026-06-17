<script setup>
defineProps({
    modelValue:   { type: Boolean, default: false },
    title:        { type: String, default: 'Xác nhận' },
    description:  { type: String, default: '' },
    confirmLabel: { type: String, default: 'Xác nhận' },
    cancelLabel:  { type: String, default: 'Huỷ' },
    danger:       { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

function confirm() {
    emit('confirm')
    emit('update:modelValue', false)
}

function cancel() {
    emit('cancel')
    emit('update:modelValue', false)
}
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="cancel">
                <div class="absolute inset-0 bg-black/50" @click="cancel" />
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    <p class="text-sm text-gray-600">{{ description }}</p>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="cancel"
                            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                            {{ cancelLabel }}
                        </button>
                        <button type="button" @click="confirm"
                            :class="[
                                'px-4 py-2 text-sm text-white rounded-md transition',
                                danger ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700'
                            ]">
                            {{ confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
