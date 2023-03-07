<template>
    <div class="flex items-center border-b border-slate-300/80 dark:border-primaryDarkBorder p-1 actionBtn">
        <div class="grow flex justify-center gap-3">
            <button @click="$emit('handlePrevious')">Prev</button>
            <div class="rounded px-2 py-1.5 bg-black/20 dark:bg-white/20 w-fit">{{(current + 1)}} / {{documentlength}}</div>
            <button @click="$emit('handleNext')">Next</button>
        </div>

        <Close @click="$emit('handleClose')" />
    </div>
</template>

<script setup>
import { ref } from '@vue/reactivity'
import Close from '../../Buttons/XButton.vue'

const props = defineProps({
    current: Number,
    documentlength: Number,
})

const emits = defineEmits(['handleClose', 'handlePrevious', 'handleNext'])


</script>