<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Level
        </template>

        <form @submit.prevent="submit($page.props.current.id)" class="mt-9">
            <div>
                <span class="errorMessage" v-if="levelForm.errors.level_and_phase">{{levelForm.errors.level_and_phase}}</span>
                <input type="text" :class="[levelForm.errors.level_and_phase?'formInput_error':'formInput']"  placeholder="Level and phase" v-model="levelForm.level_and_phase">
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

const levelForm = useForm({
    createAs: 1,
    parent: null,
    level_and_phase: null
})

const create = () => {
    levelForm.post('/accreditation/instrument/create', {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', [])
        }
    })
}

const update = () => {
    levelForm.post('/accreditation/instrument/create/'+storeInstrument.updateSelect.id, {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', [])
        }
    })
}

const submit = (parent) => {
    levelForm.parent = parent
    if(!props.isEdit) { create() }
    else { update() }
}

if(props.isEdit)
{
    levelForm.level_and_phase = storeInstrument.updateSelect.title
}
</script>