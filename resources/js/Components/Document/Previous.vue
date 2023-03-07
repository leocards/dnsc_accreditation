<template>
    <button type="button" class="absolute top-0 left-0 w-16 h-full bg-gradient-to-r hover:from-white/10 group flex items-center justify-center">
        <button type="button" class="actionBtn w-10 h-10 rounded-full shrink-0 group-hover:bg-white/30 group-hover:text-white/90 group-hover:opacity-100 opacity-40 transition_300
        dark:hover:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] flex items-center justify-center bg-white/10">
            <LeftIcon />
        </button>
    </button>
</template>
<script setup>
import LeftIcon from '../Icons/backIcon.vue'

</script>