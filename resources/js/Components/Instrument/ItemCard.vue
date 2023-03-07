<template>
    <div class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard">
        <div>
            <ProgramIcon />
        </div>
        <div class="Oneline max-w-[24rem]">
            {{label}}
        </div>
    </div>
</template>

<script setup>
import ProgramIcon from '../Icons/programIcon.vue'

defineProps({ label: String })
</script>