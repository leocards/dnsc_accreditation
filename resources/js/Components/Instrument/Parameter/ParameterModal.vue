<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Parameter
        </template>

        <form @submit.prevent="submit" class="mt-9">
            <div>
                <span class="errorMessage" v-if="parameterForm.errors.parameter">{{parameterForm.errors.parameter}}</span>
                <input type="text" :class="[parameterForm.errors.parameter?'formInput_error':'formInput']"  placeholder="Parameter" v-model="parameterForm.parameter">
            </div>

            <div class="mt-7">
                <span class="errorMessage" v-if="parameterForm.errors.parameter_label">{{parameterForm.errors.parameter_label}}</span>
                <input type="text" :class="[parameterForm.errors.parameter_label?'formInput_error':'formInput']"  placeholder="Parameter label" v-model="parameterForm.parameter_label">
            </div>

            <div class="mt-7 mb-3">
                <Submit width="w-32 ml-auto">
                    <span v-if="isEdit">Update</span>
                </Submit>
            </div>
        </form>

    </Modal>
</template>
<script setup>
import Modal from '../../Modal.vue'
import Submit from '../../Buttons/Submit.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { useInstrumentStore } from '../../../Store/storeInstrument'

const storeInstrument = useInstrumentStore()

const props = defineProps({isEdit: Boolean, parent: Object, levelInstrument: Object})
const emits = defineEmits(['handleClose', 'update'])

const parameterForm = useForm({
    createAs: 3,
    parent: !props.isEdit?props.parent.id:null,
    parameter: null,
    parameter_label: null,
    level: props.levelInstrument.id,
    indicators: [
        {
            id: 1,
            ind: 'S',
            title: "System - Inputs and Processes",
        },
        {
            id: 2,
            ind: 'I',
            title: "Implementation",
        },
        {
            id: 3,
            ind: 'O',
            title: "Outcome/s",
        }
    ]
})

const create = () => {
    parameterForm.post('/accreditation/instrument/create', {
        preserveScroll: true,
        onSuccess: page => {
            storeInstrument.instruments = page.props.instruments
            emits('update', page.props.instruments)
        },
        onError: err => {
        }
    })
}

const update = () => {
    parameterForm.post('/accreditation/instrument/create/'+storeInstrument.updateSelect.id, {
        preserveScroll: true,
        onSuccess: page => {
            storeInstrument.instruments = page.props.instruments
            emits('update', page.props.instruments)
        },
        onError: err => {
        }
    })
}

const submit = () => {
    if(!props.isEdit) { create() }
    else { update() }
}

if(props.isEdit)
{
    parameterForm.parameter = storeInstrument.updateSelect.title
    parameterForm.parameter_label = storeInstrument.updateSelect.description
    parameterForm.parent = storeInstrument.updateSelect.parent
}

</script>