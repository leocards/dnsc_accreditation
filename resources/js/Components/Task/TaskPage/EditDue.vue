<template>
    <div>
        <div>
            <div class="border border-slate-300/80 dark:border-primaryDarkBorder p-1 rounded-md mt-3 mb-6">
                <div class="bg-black/5 dark:bg-white/10 rounded p-3">
                    {{user.first_name+' '+user.last_name}}
                </div>
            </div>
            <div class="mb-5">
                <label for="due">Due</label>
                <span class="errorMessage ml-2" v-if="dueForm.errors.due">{{dueForm.errors.due}}</span>
                <input type="date" class="formInput mt-2" v-model="dueForm.due">
            </div>
        </div>
        <div class="flex justify-end gap-3 pb-3">
            <Cancel @click="$emit('handleCancel')" />
            <Submit @click="submit()">
                <span>Update</span>
            </Submit>
        </div>
    </div>
</template>
<script setup>
import Cancel from '../../Buttons/Cancel.vue'
import Submit from '../../Buttons/Submit.vue'
import { useForm } from "@inertiajs/inertia-vue3"

const props = defineProps({
    user: Object,
    due: Object
})
const emits = defineEmits([
    'handleCancel',
    'handleClose',
])

const date = props.user.due.slice(0, 10)

const dueForm = useForm({
    id: props.user.assignId,
    user: props.user.id,
    due: date
})

const returnDate = date => {
    return date.toLocaleString('en-US', {year: "numeric", month: "short", day: "numeric"})
}

const validateDue = () => {
    let inputDue = new Date(dueForm.due)
    let today = new Date()
    let ssDue = new Date(props.due.date_self_survey)
    let actualDue = new Date(props.due.date_actual_survey)

    if(inputDue <= today){
        dueForm.errors.due = 'Due should not be today or before'
        return false
    }else if(inputDue >= ssDue){
        dueForm.errors.due = 'Due should not be on '+returnDate(ssDue)+' or beyond'
        return false
    }else if(inputDue >= actualDue){
        dueForm.errors.due = 'Due should not be on '+returnDate(actualDue)+' or beyond'
        return false
    }else{
        dueForm.clearErrors()
        return true
    }
}

const submit = () => {
    if(validateDue())
        dueForm.post('/task/assignMember/edit', {
            onSuccess: page => {
                emits('handleClose', props.user, dueForm.due)
                emits('handleCancel')
            }
        })
}



</script>