<script setup lang="ts">
import { ref, nextTick } from 'vue'

const props = defineProps<{
    modelValue: number | null
    min?: number
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void
}>()

const isEditing = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)
const inputValue = ref<string>('')

function startEditing() {
    isEditing.value = true
    inputValue.value = props.modelValue?.toString() ?? ''
    nextTick(() => {
        inputRef.value?.focus()
    })
}

function save() {
    isEditing.value = false
    if (inputValue.value === '') {
        if (props.modelValue !== null) {
            emit('update:modelValue', null)
        }
        return
    }

    const parsed = parseInt(inputValue.value)
    if (!isNaN(parsed)) {
        if (props.min !== undefined && parsed < props.min) {
            // If less than min, dont update ?
            return
        }
        if (parsed !== props.modelValue) {
            emit('update:modelValue', parsed)
        }
    }
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        save()
    } else if (e.key === 'Escape') {
        isEditing.value = false
    }
}
</script>

<template>
    <div
        v-if="!isEditing"
        @click="startEditing"
        class="cursor-pointer h-8 w-16 mx-auto flex items-center justify-center hover:bg-muted/50 rounded border border-transparent hover:border-border transition-colors"
        :class="{ 'text-muted-foreground': modelValue === null }"
    >
        {{ modelValue ?? '-' }}
    </div>
    <div v-else class="flex justify-center h-8 items-center">
        <input
            ref="inputRef"
            type="number"
            :min="min"
            v-model="inputValue"
            class="w-16 px-2 py-1 text-sm border rounded text-center focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-background"
            @blur="save"
            @keydown="onKeydown"
        />
    </div>
</template>
