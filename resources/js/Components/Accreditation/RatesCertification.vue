<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Ratings
        </template>
        
        <div class="w-[33rem] py-2" v-if="accredProgramAreas">
            <div class="w-full flex justify-between my-5 mb-7 px-2">
                <div>{{accred.title}}</div> <div>{{calculate_score(accredProgramAreas)}}</div>
            </div>

            <div class="pl-2 mb-2 dark:text-white/40">Area rates</div>

            <ul>
                <li class="p-1 mb-" v-for="area in accredProgramAreas">
                    <RateSubs :insts="area" :accred="accred" @reCalculate="getRatingsArea()"/>
                </li>
            </ul>

            <div class="flex justify-end mt-5">
                <SubmitButton @click="confirmRates">
                    Confirm
                </SubmitButton>
            </div>
        </div>

        <div class="flex w-full py-2.5 justify-center" v-else>
            <Loading />
        </div>
    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Loading from '../Loading.vue'
import RateSubs from './RateSubs.vue'
import SubmitButton from '../Buttons/Submit.vue'
import DownButton from '../Buttons/DownButton.vue'
import { ref } from '@vue/reactivity';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { onBeforeMount } from 'vue'

const props = defineProps({
    accred: Object,
})
const emits = defineEmits([
    'handleClose'
])

const disable = ref(false)
const accredProgramAreas = ref(null)

const calculate_score = areas => {
    let rates = areas.map(area => {
        return isNaN(area.rate) || !area.rate ? 0 : parseFloat(area.rate)
    })

    return (rates.reduce((accumulator, currentValue) => accumulator + currentValue, 0) / areas.length).toFixed(1)
}

const checkAreasRate = areas => {
    return areas.find(area => area.rate == null)
}

const confirmRates = () => {
    Inertia.post('/accreditation/confirmVerify', {
        id: props.accred.id
    }, {
        onSuccess: page => {
            emits('handleClose')
        }
    })
}

function getRatingsArea() {
    try {

    axios.post('/accreditation/areas_certification',{
        id: props.accred.id,
        parent: ''
    })
    .then(res => {
        accredProgramAreas.value = res.data
        disable.value = checkAreasRate(res.data) ? true : false
    })


    } catch (e) {

    }
}

onBeforeMount(()=>{
    getRatingsArea()
})

</script>