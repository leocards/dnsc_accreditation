<template>
    <div class="w-full h-32 flex flex-col">
        <div class="grow flex items-center">
            Remove " {{user.name}} " as Task Force Member
        </div>
        <div class="flex justify-end gap-2">
            <Cancel @click="$emit('handleCancel')" />
            <DeleteButton @click="removeMember">
                Remove
            </DeleteButton>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/inertia-vue3'
import axios from 'axios'
import Cancel from '../../../Buttons/Cancel.vue'
import DeleteButton from '../../../Buttons/Delete.vue'

const props = defineProps({
    user: Object,
    area: Number,
    accredlvl: Number
})
const emits = defineEmits([
    'handleCancel',
    'handleRemove'
])

const removeForm = useForm({
    id: props.user.id,
    user: props.user.userId,
    area: props.area,
    accredlvl: props.accredlvl
})

const removeMember = () => {
    try {
        axios.post('/program/remove/taskforce/member', {
            ...removeForm
        })
        .then(res => {
            emits('handleRemove', props.user)
        })
    } catch (e) {
        console.log(e)
    }
}

</script>