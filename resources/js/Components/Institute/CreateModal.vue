<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Institute
        </template>


        <form @submit.prevent="submitForm">

            <div class="mt-5">
                <label for="name">Name</label>
                <span class="errorMessage ml-2" v-if="instituteForm.errors.institute_name">{{instituteForm.errors.institute_name}}</span>
                <input type="text" id="name" class="formInput mt-2" v-model="instituteForm.institute_name" placeholder="Institute name">
            </div>
            <div class="mt-4">
                <label for="abbr">Abbreviation</label>
                <span class="errorMessage ml-2" v-if="instituteForm.errors.abbreviation">{{instituteForm.errors.abbreviation}}</span>
                <input type="text" id="abbr" class="formInput mt-2" v-model="instituteForm.abbreviation" placeholder="Ex. IC">
            </div>
            <div class="mt-4" v-if="isEdit">
                <label for="dean">Dean</label>
                <select class="formInput mt-2" id="dean" v-model="instituteForm.dean">
                    <option value="">select</option>
                    <option :value="dean.id" v-for="(dean, index) in deans" :key="index">{{dean.first_name+ ' ' + dean.last_name}}</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="estab">Established</label>
                <input type="date" id="estab" class="formInput mt-2" v-model="instituteForm.established">
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
import Modal from '../Modal.vue'
import Submit from '../Buttons/Submit.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { ref } from 'vue'
import axios from 'axios';

const props = defineProps({
    isEdit: Boolean,
    selected: Number,
    institutes: Array,
})
const emits = defineEmits(['handleClose'])

const deans = ref([])
const instituteForm = useForm({
    abbreviation: null,
    institute_name: null,
    dean: "",
    established: null,
    id: null
})

const getInstituteToEdit = (institute) => {
    let found = institute.find(({id}) => {
        return id == props.selected
    })
    instituteForm.id = found.id
    instituteForm.established = found.established
    instituteForm.abbreviation = found.abbreviation
    instituteForm.dean = found.dean ? found.dean : ''
    instituteForm.institute_name = found.institute_name
}

props.isEdit ? getInstituteToEdit(props.institutes) : ''

const create = () => {
    instituteForm.post('/institute/create', {
        onSuccess: page => {
            emits('handleClose')
        },
        onError: err => {
            //console.table(err)
        }
    })
}

const update = () => {
    instituteForm.post('/institute/update/'+instituteForm.id, {
        onSuccess: page => {
            emits('handleClose')
        }
    })
}
//console.log(props.institutes)
const submitForm = () => {
    if(!props.isEdit){ create() }
    else{ update() }
}

try{
    axios.get('/institute/deans?d='+props.selected)
    .then(res => {
        deans.value = res.data
    })
}catch(e){

}

</script>