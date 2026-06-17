<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props  = defineProps({ rsvp: Object, event: Object })
const editing  = ref(false)
const tableVal = ref(props.rsvp.guest?.table_no ?? '')
const saving   = ref(false)

async function save() {
    if (saving.value) return
    saving.value = true
    try {
        await axios.patch(
            route('dashboard.events.rsvp.table', [props.event.slug, props.rsvp.id]),
            { table_no: tableVal.value || null }
        )
    } finally {
        saving.value  = false
        editing.value = false
    }
}

function startEdit() { editing.value = true }
function cancel()    { tableVal.value = props.rsvp.guest?.table_no ?? ''; editing.value = false }
</script>

<template>
    <span v-if="!editing"
          @click="startEdit"
          class="cursor-pointer text-sm text-gray-600 hover:text-indigo-600 min-w-[40px] inline-block"
          title="Click để chỉnh bàn">
        {{ tableVal || '—' }}
    </span>
    <input v-else
           v-model="tableVal"
           @blur="save"
           @keyup.enter="save"
           @keyup.esc="cancel"
           :disabled="saving"
           autofocus
           class="border rounded px-1.5 py-0.5 text-sm w-16 focus:outline-none focus:ring-1 focus:ring-indigo-400 disabled:opacity-50">
</template>
