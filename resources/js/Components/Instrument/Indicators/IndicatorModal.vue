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

            <div class="mt-7 mb-6">
                <span class="errorMessage" v-if="indicatorForm.errors.indicator_label">{{indicatorForm.errors.indicator_label}}</span>
                <textarea :class="[indicatorForm.errors.indicator_label?'textareaForm_error':'textareaForm']" placeholder="Indicator label" v-model="indicatorForm.indicator_label"></textarea>
            </div>

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
import myMethod from '../../../Store/Methods'
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
    evidence_to_attach: [{evidence: null}],
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
            if(page.props.flash.success){
                storeInstrument.instruments = page.props.instruments
                emits('update', page.props.instruments)
            }else{
                console.log(page.props.flash.error)
            }
        },
        onError: err => {
        }
    })
    //console.table(indicatorForm.evidence_to_attach)
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

const updateValue = (index, val) => {
    indicatorForm.evidence_to_attach[index].evidence = val
}

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
    if(!props.isEdit) { create() }
    else { update() }
    //console.table(indicatorForm)
}

if(props.isEdit)
{
    indicatorForm.indicator = storeInstrument.updateSelect.title
    indicatorForm.indicator_label = storeInstrument.updateSelect.description
    if(myMethod.isValidJson(storeInstrument.updateSelect.attachment))
    {
        indicatorForm.evidence_to_attach = myMethod.isValidJson(storeInstrument.updateSelect.attachment)
    }else{
        indicatorForm.evidence_to_attach[0].evidence = storeInstrument.updateSelect.attachment
    }
    indicatorForm.parent = storeInstrument.updateSelect.parent
}

</script>