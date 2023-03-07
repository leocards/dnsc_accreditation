<template>
    <Card>
        <div class="w-full h-full p-2.5 px-3 select-none flex items-center" :id="`program${program.id}`"
        :class="[selected == program.id ?'card-select':'card-hover']">
            <div class="grow h-full">
                <div class="font-semibold text-lg inter-font pointer-events-none">{{program.abbreviation}}</div>
                <div class="w-fit"></div>
            </div>
            <RightButton title="Open" @click="$emit('handleRoute')" />
        </div>
    </Card>
</template>

<script setup>
import Card from '../Card.vue'
import RightButton from '../Buttons/RightButton.vue'

const props = defineProps({
    program: Object,
    selected: Number
})
const emits = defineEmits([
    'handleRoute'
])

</script>