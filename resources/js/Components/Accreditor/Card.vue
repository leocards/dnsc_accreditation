<template>
    <Card>
        <div class="w-full h-full p-2.5 px-3 select-none card-hover">
            <div>{{program.program.abbreviation}}</div>
            <div class="Oneline">{{program.level.title}}</div>
        </div>
    </Card>
</template>

<script setup>
import Card from '../Card.vue'

const props = defineProps({
    program: Object
})

</script>