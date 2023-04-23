<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            New Announcement
        </template>

        <form @submit.prevent="submit($page.props.flash)">

            <div class="mt-3">
                <label for="title">Title</label>
                <span class="errorMessage ml-2" v-if="announceForm.errors.title">{{announceForm.errors.title}}</span>
                <input type="text" id="title" class="formInput mt-0.5" placeholder="title" v-model="announceForm.title">
            </div>

            <div class="mt-3">
                <label for="message">Message</label>
                <span class="errorMessage ml-2" v-if="announceForm.errors.message">{{announceForm.errors.message}}</span>
                <textarea name="" id="message" class="textareaForm mt-0.5" placeholder="text here" v-model="announceForm.message"></textarea>
            </div>

            <div class="flex mt-4 justify-end pb-3">
                <Submit>
                    <span v-if="edit">Update</span>
                </Submit>
            </div>
        </form>
        
    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Submit from '../Buttons/Submit.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import axios from 'axios'

const props = defineProps({
    edit: Object
})
const emits = defineEmits([
    'handleClose',
    'handleSave'
])

const announceForm = useForm({
    id: props.edit?props.edit.id:null,
    title: props.edit?props.edit.title:null,
    message: props.edit?props.edit.message:null,
})

const submit = (flash) => {
    axios.post('/announcement/new', {
        ...announceForm
    })
    .then(res => {
        emits('handleSave', res.data)
    })
    .catch(e => {
        announceForm.errors.title = e.response.data.errors.title[0]
        announceForm.errors.message = e.response.data.errors.message[0]
    })
}

</script>