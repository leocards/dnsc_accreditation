<template>
    <div class="mb-3 flex items-center gap-3">
        <span>Task</span>
        <DownButton 
            :style="(isOpenTask?'bg-dnscGreen/90 hover:bg-dnscGreen':'hover:bg-white/10')"
            @click="isOpenTask = !isOpenTask"
        />
    </div>

    <div class="w-full" v-if="isOpenTask">
        <div class="mt-2" v-for="task in assigned">
            <Accordion 
                :area="area"
                :inst="task"
            />
        </div>
    </div>
</template>

<script setup>
import Accordion from './Accordion.vue'
import DownButton from '../../Buttons/DownButton.vue'
import { ref } from '@vue/reactivity'

const props = defineProps({
    area: Number,
    assigned: Object
})
const emits = defineEmits([
    'handleSelect', 
])

const isOpenTask = ref(false)

</script>