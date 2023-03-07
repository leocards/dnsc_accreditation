<template>
    <button type="button" class="rounded-full slate-600 flex items-center justify-center transition_300 shrink-0 actionBtn
    "
    :class="[newBg??'hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] hover:bg-black/10 hover:dark:bg-white/10 w-9 h-9']"
    :disabled="disabled">
        <XIcon />
    </button>
</template>
<script setup>
import XIcon from '../Icons/closeIcon.vue'
defineProps({disabled: Boolean, newBg: String})
</script>