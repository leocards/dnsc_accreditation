<template>
    <span class="text-xs" v-if="isDateOnly">
        {{new Date(time).toLocaleString('en-US', {year: "numeric",month: "short",day: "numeric"})}}
    </span>

    <span class="text-xs" v-else>
        {{new Date(time).toLocaleString('en-US', {year: "numeric",month: "short",day: "numeric", hour: "numeric", minute: "2-digit"})}}
    </span>
</template>

<script setup>

const props = defineProps({
    time: String,
    isDateOnly: Boolean,
})

</script>