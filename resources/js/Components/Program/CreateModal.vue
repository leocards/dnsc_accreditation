<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="isEdit">Update</span>
            <span v-else>Create</span>
            Program
        </template>

        <form @submit.prevent="submit">
            <div class="mt-5">
                <label for="name">Program name</label>
                <span class="errorMessage ml-2" v-if="programForm.errors.program_name">{{programForm.errors.program_name}}</span>
                <input type="text" id="abbr" class="formInput mt-2" placeholder="Program name" v-model="programForm.program_name">
            </div>
            <div class="mt-4">
                <label for="abbr">Abbreviation</label>
                <span class="errorMessage ml-2" v-if="programForm.errors.abbreviation">{{programForm.errors.abbreviation}}</span>
                <input type="text" id="name" class="formInput mt-2" placeholder="Ex. BSIT" v-model="programForm.abbreviation">
            </div>
            <div class="mt-4">
                <label for="institute">Institute</label>
                <span class="errorMessage ml-2" v-if="programForm.errors.institute">{{programForm.errors.institute}}</span>
                <select id="institute" class="formInput mt-2" v-model="programForm.institute">
                    <option value="">select</option>
                    <option :value="institute.id" v-for="institute in $page.props.institutes">{{institute.abbreviation}}</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="BOT">BOT resolution</label>
                <input type="text" id="BOT" class="formInput mt-2" placeholder="BOT resolution" v-model="programForm.bot_resolution">
            </div>
            <div class="mt-4">
                <label for="establsihed">Established</label>
                <input type="date" id="establsihed" class="formInput mt-2" v-model="programForm.established">
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
import { onUnmounted } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({
    isEdit: Boolean,
    programs: Array,
    selected: Number,
})

const emits = defineEmits(['handleClose', 'update'])

const programForm = useForm({
    abbreviation: null,
    program_name: null,
    institute: '',
    bot_resolution: null,
    established: null,
    id: null
})

const getProgramToEdit = (programs) => {
    let program = programs.find(({id}) => {
        return props.selected == id
    })
    programForm.abbreviation = program.abbreviation
    programForm.program_name = program.program_name
    programForm.institute = program.instituteId
    programForm.bot_resolution = program.bot
    programForm.established = program.established
    programForm.id = program.id
}

props.isEdit ? getProgramToEdit(props.programs) : ''

const create = () => {
    programForm.post('/program/create', {
        onSuccess: page => {
            programForm.reset()
            programForm.clearErrors()
            emits('update')
        },
        onError: err => { }
    })
}

const update = () => {
    programForm.post('/program/update/'+programForm.id, {
        onSuccess: page => {
            programForm.reset()
            programForm.clearErrors()
            emits('update')
        },
        onError: err => { }
    })
}

const submit = () => {
    if(!props.isEdit) { create() }
    else{ update() }

}

onUnmounted(()=>{
    programForm.reset()
    programForm.clearErrors()
})

</script>