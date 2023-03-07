<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Instrument
        </template>

        <form @submit.prevent="submit" class="mt-9">
            <div>
                <span class="errorMessage" v-if="instrumentForm.errors.instrument">{{instrumentForm.errors.instrument}}</span>
                <input type="text" :class="[instrumentForm.errors.instrument?'formInput_error':'formInput']"  placeholder="Instument" v-model="instrumentForm.instrument">
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

const props = defineProps({isEdit: Boolean})
const emits = defineEmits(['handleClose', 'update'])

const instrumentForm = useForm({
    createAs: 0,
    instrument: null
})

const create = () => {
    instrumentForm.post('/accreditation/instrument/create', {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', [])
        }
    })
}

const update = () => {
    instrumentForm.post('/accreditation/instrument/create/'+storeInstrument.updateSelect.id, {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', [])
        }
    })
}

const submit = () => {
    if(!props.isEdit) { create() }
    else { update() }
}

if(props.isEdit)
{
    instrumentForm.instrument = storeInstrument.updateSelect.title
}
</script>