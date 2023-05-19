<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            Edit Evidence<span v-if="(inst.attachment&&inst.attachment.length>1)">s</span> to attach
        </template>
        
        <div class="text-lg">{{ inst.title }} </div>

        <form @submit.prevent="submit" class="mt-3">
            <div class="mt-2 flex flex-col" v-for="(item, index) in indicatorForm.evidence_to_attach" :key="index">
                <span class="errorMessage" v-if="indicatorForm.errors.evidence_to_attach">{{indicatorForm.errors.evidence_to_attach}}</span>
                <textarea 
                    :class="[indicatorForm.errors.evidence_to_attach?'textareaForm_error':'textareaForm']" 
                    placeholder="Evidence to attach" 
                    v-model="item.evidence"
                ></textarea>
                <button 
                    type="button"
                    class="text-xs p-1.5 rounded bg-red-700/80 hover:bg-red-700 mt-0.5 text-white ml-auto actionBtn" 
                    @click="removeAddedEvidence(index)"
                    v-if="myMethod.ifArray(indicatorForm.evidence_to_attach, '>', 1)"
                >remove</button>
            </div>

            <div class="mt-2">
                <button type="button" class="text-xs p-1.5 rounded bg-dnscGreen/90 text-white actionBtn" @click="addEvidence">add evidence</button>
            </div>

            <div class="mt-7 mb-3">
                <Submit width="w-32 ml-auto">
                    <span>Update</span>
                </Submit>
            </div>
        </form>
    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Submit from '../Buttons/Submit.vue'
import myMethod from '../../Store/Methods'
import { useForm } from '@inertiajs/inertia-vue3'
import { useInstrumentStore } from '../../Store/storeInstrument'
import { ref } from '@vue/reactivity'

const storeInstrument = useInstrumentStore()

const props = defineProps({inst: Object})
const emits = defineEmits(['handleClose'])

const indicatorForm = useForm({
    id: props.inst.id,
    evidence_to_attach: [{evidence: null}]
})

const addEvidence = () => {
    indicatorForm.evidence_to_attach.push({evidence: null})
}

const removeAddedEvidence = index => {
    if(indicatorForm.evidence_to_attach.length > 1)
    {
        indicatorForm.evidence_to_attach.splice(index, 1)
    }
}

const submit = () => {
    indicatorForm.post('/accreditation/editEvidence', {
        preserveScroll: true,
        onSuccess: page => {
            storeInstrument.instruments = page.props.instruments
            emits('handleClose', page.props.instruments)
        },
        onError: err => {
        }
    })
}
//isEdit
if(props.inst.attachment || props.inst.attachment.length > 0)
{
    if(myMethod.isValidJson(props.inst.attachment))
    {
        indicatorForm.evidence_to_attach = myMethod.isValidJson(props.inst.attachment)
    }else{
        indicatorForm.evidence_to_attach[0].evidence = props.inst.attachment
    }
}

</script>