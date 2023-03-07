<template>
    <div class="subnavBg min-h-[4rem] rounded overflow-hidden" :id="id">
        
        <div class="w-full h-full transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]
        flex flex-col justify-center p-1.5 group" :class="[selected == id ? 'card-select' : 'hover:bg-gray-300/80 dark:hover:bg-white/5']">

            <slot />

        </div>

    </div>
</template>
<script setup>
defineProps({
    id: Number,
    selected: Number
})
</script>