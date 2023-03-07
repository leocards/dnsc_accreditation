<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Area
        </template>

        <form @submit.prevent="submit($page.props.current.id)" class="mt-9">
            <div>
                <span class="errorMessage" v-if="areaForm.errors.area">{{areaForm.errors.area}}</span>
                <input type="text" :class="[areaForm.errors.area?'formInput_error':'formInput']"  placeholder="Area" v-model="areaForm.area">
            </div>

            <div class="mt-7">
                <span class="errorMessage" v-if="areaForm.errors.area_label">{{areaForm.errors.area_label}}</span>
                <input type="text" :class="[areaForm.errors.area_label?'formInput_error':'formInput']"  placeholder="Area label" v-model="areaForm.area_label">
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

const props = defineProps({isEdit: Boolean, levelInstrument: Object})
const emits = defineEmits(['handleClose', 'update'])

const areaForm = useForm({
    createAs: 2,
    parent: null,
    area: null,
    area_label: null,
    level: props.levelInstrument.id
})

const create = () => {
    areaForm.post('/accreditation/instrument/create', {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', page.props.instruments)
        }
    })
}

const update = () => {
    areaForm.post('/accreditation/instrument/create/'+storeInstrument.updateSelect.id, {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', page.props.instruments)
        },
        onError: err => {
            console.table(err)
        }
    })
}

const submit = (parent) => {
    areaForm.parent = parent
    if(!props.isEdit) { create() }
    else { update() }
}

if(props.isEdit)
{
    areaForm.area = storeInstrument.updateSelect.title
    areaForm.area_label = storeInstrument.updateSelect.description
}
</script>