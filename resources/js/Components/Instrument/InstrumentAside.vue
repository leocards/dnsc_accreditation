<template>
    <Aside @handleClose="$emit('handleClose')">
        <template #head>
            Instrument
        </template>

        <div class="flex p-1.5 pb-0 gap-0.5">
            <button 
                type="button" 
                class="grow text-center transition_300 select-none border-b hover: py-2 rounded-t-md actionBtn"
                :class="[isDetails? 'bg-dnscGreen/20 border-dnscGreen text-dnscGreen dark:bg-dnscGreen/30 dark:text-white/80' : 
                'hover:bg-black/10 hover:border-black/40 dark:hover:bg-white/5 dark:hover:border-white/30 border-transparent']"
                @click="getDetails"
            >Details</button>

            <button type="button" class="grow text-center transition_300 select-none border-b py-2 rounded-t-md actionBtn"
            :class="[isActivity? 'bg-dnscGreen/20 border-dnscGreen text-dnscGreen dark:bg-dnscGreen/30 dark:text-white/80' : 
            'hover:bg-black/10 hover:border-black/40 dark:hover:bg-white/5 dark:hover:border-white/30 border-transparent']"
                @click="getActivity"
            >Activity</button>
        </div>

        <div class="p-1.5 grow ring-inset overflow-y-auto">
            <Info v-if="isDetails" :instrument="instrument" />
            <Activity v-if="isActivity" v-for="activity in activities" :activity="activity" />
        </div>

    </Aside>
    <input type="hidden" :value="getUserLogs()">
</template>
<script setup>
import Aside from '../Aside.vue'
import Info from './InfoAside.vue'
import Activity from './ActivityAside.vue'
import { ref } from '@vue/reactivity'

const props = defineProps({
    accred: Number,
    instrument:Object,
})
const emits = defineEmits([
    'handleClose'
])

const isDetails = ref(true)
const isActivity = ref(false)
const activities = ref(null)

const getDetails = () => {
    isDetails.value = true
    isActivity.value  = false
}
const getActivity = () => {
    isDetails.value = false
    isActivity.value  = true
}

const getUserLogs = async () => {
    try {
        let res = await axios.get(`/userlogs/${props.accred}/${props.instrument.id}`)
        let data = await res.data

        activities.value = data
    } catch (e) {
        console.log(e)
    }
}

</script>