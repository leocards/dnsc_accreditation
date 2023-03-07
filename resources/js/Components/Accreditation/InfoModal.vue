<template>
    <Modal @handleClose="$emit('handleClose')">
        
        <div class="mt-6 mb-3">
            <div>
                <div class="dark:text-white/40">Program</div>
                <div>{{accred.program}}</div>
            </div>

            <div class="mt-3">
                <div class="dark:text-white/40">Level</div>
                <div>{{accred.title}} </div>
            </div>

            <div class="mt-3">
                <div class="dark:text-white/40">Self survey</div>
                <div><Date :isDateOnly="true" :time="accred.date_self_survey" /></div>
            </div>

            <div class="mt-3">
                <div class="dark:text-white/40">Actual survey</div>
                <div><Date :isDateOnly="true" :time="accred.date_actual_survey" /></div>
            </div>

            <div class="mt-3">
                <div class="dark:text-white/40">Status</div>
                <div class="relative flex">
                    <select v-model="status" class="text-[13px] w-[13rem] bg-transparent outline-none p-1.5 border-b dark:border-primaryDarkBorder">
                        <option class="dark:text-black" value="">--Select status--</option>
                        <option class="dark:text-black" value="re-accredited">Re-accredited</option>
                        <option class="dark:text-black" value="accredited">Accredited</option>
                    </select>

                    <button @click="saveStatus" v-if="isModified" :disabled="processing" class="text-xs bg-dnscGreen/90 hover:bg-dnscGreen rounded p-1 px-1.5 h-fit ml-2">
                        <span>save</span>
                    </button>
                </div>
            </div>
        </div>

    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Date from '../DateTime.vue'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    accred: Object
})
const emits = defineEmits([
    'handleClose'
])

const status = ref(props.accred.status?props.accred.status:'')
const isModified = ref(false)
const processing = ref(false)

const saveStatus = () => {
    processing.value = true
    Inertia.post('/accreditation/set_status', {
        status: status.value,
        id: props.accred.id
    }, {
        onSuccess: page => {
            processing.value = false

            if(page.props.flash.success)
                emits('handleClose')
        }
    })
}

watch(status, val => {
    if(val != (props.accred.status?props.accred.status:''))
        isModified.value = true
    else 
        isModified.value = false
})

console.log(props)

</script>