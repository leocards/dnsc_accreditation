<template>
    <InstCard>
        <div class="w-full h-full p-2.5 px-3 select-none flex items-center" @click.self="$emit('handleClick')" :id="`instrument${instrument.id}`"
        :class="[selected === instrument.id?'card-select':'card-hover']">
            <div class="pointer-events-none grow">
                <div>{{instrument.title}}</div>
                <div class="text-xs inter-font" v-show="instrument.description">{{instrument.description}}</div>
            </div>

            <RightButton title="Open" @click="$emit('handleRoute')" />
        </div>
    </InstCard>
</template>
<script setup>
import InstCard from '../../Card.vue'
import RightButton from '../../Buttons/RightButton.vue';

const props = defineProps({
    selected: Number,
    instrument: Object, 
})

defineEmits(['handleClick', 'handleRoute'])
</script>