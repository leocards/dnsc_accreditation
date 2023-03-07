<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Indicator
        </template>

        <form @submit.prevent="submit" class="mt-9">
            <div>
                <span class="errorMessage" v-if="indicatorForm.errors.indicator">{{indicatorForm.errors.indicator}}</span>
                <input type="text" :class="[indicatorForm.errors.indicator?'formInput_error':'formInput']"  placeholder="Indicator" v-model="indicatorForm.indicator">
            </div>

            <div class="mt-7">
                <span class="errorMessage" v-if="indicatorForm.errors.indicator_label">{{indicatorForm.errors.indicator_label}}</span>
                <textarea :class="[indicatorForm.errors.indicator_label?'textareaForm_error':'textareaForm']" placeholder="Indicator label" v-model="indicatorForm.indicator_label"></textarea>
            </div>

            <div class="mt-6">
                <span class="errorMessage" v-if="indicatorForm.errors.evidence_to_attach">{{indicatorForm.errors.evidence_to_attach}}</span>
                <textarea :class="[indicatorForm.errors.evidence_to_attach?'textareaForm_error':'textareaForm']" placeholder="Evidence to attach" v-model="indicatorForm.evidence_to_attach"></textarea>
            </div>

            <div class="mt-7 mb-3">
                <Submit width="w-32 ml-auto">
                    <span v-if="isEdit">Update</span>
                </Submit>
            </div>
        </form>
    </Modal>
    <input type="hidden" :data-value="countCHildren($page.props.instruments)">
</template>
<script setup>
import Modal from '../../Modal.vue'
import Submit from '../../Buttons/Submit.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { useInstrumentStore } from '../../../Store/storeInstrument'
import { ref } from '@vue/reactivity'

const storeInstrument = useInstrumentStore()

const props = defineProps({isEdit: Boolean, parent: Object, levelInstrument: Object})
const emits = defineEmits(['handleClose', 'update'])

const ind = ref(null)
const indicatorForm = useForm({
    createAs: 5,
    parent: !props.isEdit?props.parent.id:null,
    indicator: null,
    indicator_label: null,
    evidence_to_attach: null,
    level: props.levelInstrument.id,
    area: storeInstrument.area,
})

!props.isEdit ? (props.parent.indicator ? 
    (props.parent.indicator == 1 ? ind.value = 'S.':
        (props.parent.indicator == 2 ? ind.value = 'I.' :
            (props.parent.indicator == 3 ? ind.value = 'O.':'')))
    : (props.parent.category == 'item' ? ind.value = props.parent.title : '')) : ''

const countCHildren = inst => {
    if(!props.isEdit){
        let count = inst.filter(({parent}) => parent == props.parent.id)
    
        count.length == 0 ? 
            (props.parent.category == 'item' ? indicatorForm.indicator = ind.value+'.1': indicatorForm.indicator = ind.value+'1') : 
        (props.parent.category == 'item' ? indicatorForm.indicator = `${ind.value}.${(count.length)+=1}`:
        indicatorForm.indicator = `${ind.value}${(count.length)+=1}`)
    }
}

const create = () => {
    indicatorForm.post('/accreditation/instrument/create', {
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
    indicatorForm.post('/accreditation/instrument/create/'+storeInstrument.updateSelect.id, {
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
    //console.table(indicatorForm)
}

if(props.isEdit)
{
    indicatorForm.indicator = storeInstrument.updateSelect.title
    indicatorForm.indicator_label = storeInstrument.updateSelect.description
    indicatorForm.evidence_to_attach = storeInstrument.updateSelect.attachment
    indicatorForm.parent = storeInstrument.updateSelect.parent
}

</script>