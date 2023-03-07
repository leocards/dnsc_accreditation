<template>
    <button type="button" class="accordionButton actionBtn">
        <DownIcon />
    </button>
</template>

<script setup>
import DownIcon from '../Icons/documentIcon.vue'

const props = defineProps({
    active: Boolean,
    style: String
})
</script>