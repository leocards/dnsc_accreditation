<template>
    <div class="min-w-[1rem] absolute rounded bg-red-400 flex items-center justify-center pointer-events-none text-white"
    :class="[textClass??'text-xs h-4'], [position??'-top-1.5 left-2.5']">
        {{counter}}
    </div>
</template>

<script setup>

const props = defineProps({
    counter: Number,
    textClass: String,
    position: String
})
</script>