<template>
    <Card>
        <div class="w-full h-full p-2.5 px-3 select-none flex items-center" :id="`programlvl${instrument.id}`"
        :class="[selected == instrument.id ?'card-select':'card-hover']">
            <div class="pointer-events-none w-full">
                <div class="inter-font pointer-events-none">{{instrument.title}}</div>
                <div class="w-fit text-xs inter-font Oneline" v-if="instrument.description">{{instrument.description}}</div>
            </div>
            <RightButton title="Open" @click="$emit('handleRoute')" />
        </div>
    </Card>
</template>

<script setup>
import Card from '../../Card.vue'
import RightButton from '../../Buttons/RightButton.vue';

const props = defineProps({
    instrument: Object,
    selected: Number
})
const emits = defineEmits([
    'handleRoute'
])

</script>