<template>
    <Card>
        <div class="w-full h-full p-2.5 px-3 select-none" :id="`institute${institute.id}`"
        :class="[selected == institute.id ?'card-select':'card-hover']">
            <div class="font-semibold text-lg inter-font pointer-events-none">{{institute.abbreviation}}</div>
            <div class="text-xs Oneline pointer-events-none">{{institute.institute_name}}</div>
        </div>
    </Card>
</template>

<script setup>
import Card from '../Card.vue'

const props = defineProps({
    selected: Number,
    institute: Object,
})
</script>