<template>
    <button type="button" class="accordionButton actionBtn" :class="[complete?'text-dnscGreen':'']">
        <CompleteFillIcon v-if="complete"/>
        <CompleteIcon v-else/>
    </button>
</template>

<script setup>
import CompleteIcon from '../Icons/markComplete.vue'
import CompleteFillIcon from '../Icons/completeFill.vue'

const props = defineProps({
    complete: Number,
    style: String
})
//:class="[active ? 'hover:bg-dnscGreen/20' : (style?style:'hover:bg-black/10')]"
</script>