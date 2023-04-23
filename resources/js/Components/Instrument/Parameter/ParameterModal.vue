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

            <div class="mt-4" v-if="!isEdit || !loading">
                <div :class="{'pointer-events-none opacity-50':parameterForm.indicators[0].isHidden}" class="select-none flex gap-1 items-center mb-2">
                    <input 
                        id="S"
                        type="checkbox" 
                        :value="parameterForm.indicators[0].title" 
                        v-model="parameterForm.indicators[0].check"
                        @click="parameterForm.indicators[0].check = !parameterForm.indicators[0].check"
                        class="h-4 w-4 text-dnscGreen"
                    >
                    <label for="S" class="hover:cursor-pointer hover:text-dnscGreen/80" v-text="parameterForm.indicators[0].title"></label>
                </div>
                <div :class="{'pointer-events-none opacity-50':parameterForm.indicators[1].isHidden}" class="select-none flex gap-1 items-center mb-2">
                    <input 
                        id="I"
                        type="checkbox" 
                        :value="parameterForm.indicators[1].title" 
                        v-model="parameterForm.indicators[1].check" 
                        @click="parameterForm.indicators[1].check = !parameterForm.indicators[1].check"
                        class="h-4 w-4 text-dnscGreen"
                    >
                    <label for="I" class="hover:cursor-pointer hover:text-dnscGreen/80" v-text="parameterForm.indicators[1].title"></label>
                </div>

                <div :class="{'pointer-events-none opacity-50':parameterForm.indicators[2].isHidden}" class="select-none flex gap-1 items-center mb-2">
                    <input 
                        id="O"
                        type="checkbox" 
                        :value="parameterForm.indicators[2].title" 
                        v-model="parameterForm.indicators[2].check" 
                        @click="parameterForm.indicators[2].check = !parameterForm.indicators[2].check"
                        class="h-4 w-4 text-dnscGreen"
                    >
                    <label for="O" class="hover:cursor-pointer hover:text-dnscGreen/80" v-text="parameterForm.indicators[2].title"></label>
                </div>
            </div>

            <div v-else class="mt-4">
                <Loading />
            </div>

            <div class="mt-7 mb-3">
                <Submit width="w-32 ml-auto" :disabled="loading">
                    <span v-if="isEdit">Update</span>
                </Submit>
            </div>
        </form>

    </Modal>
</template>
<script setup>
import Modal from '../../Modal.vue'
import Loading from '../../Loading.vue'
import Submit from '../../Buttons/Submit.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { useInstrumentStore } from '../../../Store/storeInstrument'
import axios from 'axios';
import { ref } from 'vue'

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
            isHidden: false,
            check: false
        },
        {
            id: 2,
            ind: 'I',
            title: "Implementation",
            isHidden: false,
            check: false
        },
        {
            id: 3,
            ind: 'O',
            title: "Outcome/s",
            isHidden: false,
            check: false
        }
    ]
})
const loading = ref(false)

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

const getIndicators = async () => {
    loading.value = true
    try {
        let req = await axios.post('/accreditation/instrument/parameter-indicators',{
            id: storeInstrument.updateSelect.id
        })
        let res = await req.data

        let array = res.map((val)=> {
            return parseInt(val)
        })

        parameterForm.indicators.forEach((val, index) => {
            if (array.includes(val.id))
            {
                parameterForm.indicators[index].isHidden = true
                parameterForm.indicators[index].check = false
            }
        })

        loading.value = false
    } catch (e) {
    }
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
    getIndicators()
} else {
    parameterForm.indicators[0].check = true
    parameterForm.indicators[1].check = true
    parameterForm.indicators[2].check = true
}

</script>