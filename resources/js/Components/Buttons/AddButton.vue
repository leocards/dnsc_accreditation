<template>
    <button type="button" class="accordionButton actionBtn" :class="style">
        <AddIcon />
        <slot />
    </button>
</template>

<script setup>
import AddIcon from '../Icons/addIcon.vue'

const props = defineProps({
    active: Boolean,
    style: String
})

</script>