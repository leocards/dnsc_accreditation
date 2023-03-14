<template>
    <Modal @handleClose="$emit('handleClose')">
        
        <template #header>
            Assigned Member
        </template>

        <div class="w-full mt-6 flex justify-center" v-if="!assigned">
            <Loading />
        </div>

        <div class="mt-6 rounded pb-3" v-if="!isAssign && myMethod.ifArray(assigned, '==', 0)">

            <div class="text-center dark:text-white/50">No Assigned member</div>
            <div class="flex justify-center mt-3">
                <button 
                    class="p-2 px-3 bg-dnscGreen/90 rounded text-white transition_300 hover:bg-dnscGreen hover:shadow-md dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] actionBtn"
                    @click="isAssign = true"
                >Assign</button>
            </div>
        </div>

        <div class="mt-6 rounded pb-3 d" v-if="!isAssign && myMethod.ifArray(assigned, '>', 0) && !userToEdit">
            <div class="w-full flex gap-1.5 justify-end">
                <button class="rounded hover:bg-dnscGreen px-2.5 py-0.5 transition_300 hover:text-white actionBtn"
                @click="isEditAssign = true">Edit</button>
                <button 
                    class="rounded hover:bg-dnscGreen px-2.5 py-0.5 transition_300 hover:text-white actionBtn 
                    hover:disabled:bg-black/10 hover:dark:disabled:bg-white/10 dark:text-white/30 hover:disabled:text-white/30" 
                    @click="addMemberAssign"
                    :disabled="disableAdd()"
                >
                    Add
                </button>
            </div>
            <div class="border border-slate-300/80 dark:border-primaryDarkBorder p-1 rounded-md mt-3 mb-6 itemCard">
                <div class="hover:bg-black/5 hover:dark:bg-white/10 rounded h-11 px-3 transition_300 flex justify-between items-center" v-for="member in assigned">
                    {{member.first_name}} {{member.last_name}}
                    <button class="rounded h-fit hover:bg-dnscGreen px-2.5 py-0.5 transition_300 hover:text-white actionBtn"
                    @click="userToEdit = member, isEditAssign = false"
                    v-if="isEditAssign">Edit</button>
                </div>
            </div>
        </div>

        <form @submit.prevent class="mt-6 pb-3" v-if="isAssign">
            <div v-if="!assignTo">
                <div>Select Member</div>
                <div class="border border-slate-300/80 dark:border-primaryDarkBorder p-1 rounded-md mt-3 mb-6 itemCard">
                    <div class="hover:bg-black/5 hover:dark:bg-white/10 rounded p-3 transition_300" v-for="member in taskForceMember" @click="assignAMember(member)">
                        {{member.first_name}} {{member.last_name}}
                    </div>
                </div>
            </div>

            <div v-else>
                <div class="border border-slate-300/80 dark:border-primaryDarkBorder p-1 rounded-md mt-3 mb-6">
                    <div class="bg-black/5 dark:bg-white/10 rounded p-3">
                        {{assignTo.first_name}} {{assignTo.last_name}}
                    </div>
                </div>

                <div class="mb-5">
                    <label for="due">Due</label>
                    <span class="errorMessage ml-2" v-if="assignForm.errors.due">{{assignForm.errors.due}}</span>
                    <input type="date" class="formInput mt-2" v-model="assignForm.due">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Cancel @click="cancel()" />
                <Submit v-if="assignTo" @click="submit()">
                    <span>Assign</span>
                </Submit>
            </div>
        </form>
        
        <div class="flex justify-end" v-if="isEditAssign">
            <Cancel @click="isEditAssign = false" />
        </div>

        <Edit
            :due="due"
            :user="userToEdit"
            v-if="userToEdit"
            @handleCancel="userToEdit = null"
            @handleClose="closeOnUpdate"
        />

    </Modal>
    <input type="hidden" name="" :value="getMembers($page.props.area.id, $page.props.home.id)">
</template>

<script setup>
import Edit from './EditDue.vue'
import Modal from '../../Modal.vue'
import Loading from '../../Loading.vue'
import Cancel from '../../Buttons/Cancel.vue'
import Submit from '../../Buttons/Submit.vue'
import myMethod from '../../../Store/Methods'
import axios from 'axios'
import { ref } from '@vue/reactivity'
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({
    instrument: Object,
    accredlvl: Number,
    due: Object
})
const emits = defineEmits(['handleClose'])

const assigned = ref(null)
const isAssign = ref(false)
const isEdit = ref(false)
const taskForceMember = ref([])
const assignTo = ref(null)
const isEditAssign = ref(false)
const userToEdit = ref(null)
const assignForm = useForm({
    due: null,
    area: null,
    userId: null,
    accredlvl: null,
    instrument: null,
})

const getMembers = (area, accredlvl) => {
    let response = myMethod.getTaskForce(area, accredlvl)

    response.then(res => {
        taskForceMember.value = res.data.members.filter( member => {
            return member.role == 'member'
        })
    })
    .catch(err => {
        console.log(err)
    })
}

const assignAMember = member => {
    assignTo.value = member
    assignForm.area = member.area
    assignForm.userId = member.id
    assignForm.accredlvl = member.accredlvl
    assignForm.instrument = props.instrument.id
}

const cancel = () => {
    if(isAssign.value && !assignTo.value)
    {
        isAssign.value = false
    }else{
        assignTo.value = null
        assignForm.reset()
    }
    assignForm.clearErrors()
}

const create = () => {
    assignForm.post('/task/assignMember', {
        onSuccess: page => {
            console.log(page.props.flash.error)
            if(page.props.flash.success)
                emits('handleClose')
        }
    })
}

const disableAdd = () => {
    return taskForceMember.value.length === 1 || taskForceMember.value.length === assigned.value.length
}

const addMemberAssign = () => {
    isAssign.value = true
    taskForceMember.value = taskForceMember.value.filter(({id}) => !assigned.value.some(user => user.id == id))
}

const closeOnUpdate = (user, date) => {
    console.log('asd')
    assigned.value.find((assigned, index) => {
        if(assigned.id == user.id)
            updateList(index, date)
    })
}

const returnDate = date => {
    return date.toLocaleString('en-US', {year: "numeric", month: "short", day: "numeric"})
}

const validateDue = () => {
    let inputDue = new Date(assignForm.due)
    let today = new Date()
    let ssDue = new Date(props.due.date_self_survey)
    let actualDue = new Date(props.due.date_actual_survey)

    if(inputDue <= today){
        assignForm.errors.due = 'Due should not be today or before'
        return false
    }else if(inputDue >= ssDue){
        assignForm.errors.due = 'Due should not be on '+returnDate(ssDue)+' or beyond'
        return false
    }else if(inputDue >= actualDue){
        assignForm.errors.due = 'Due should not be on '+returnDate(actualDue)+' or beyond'
        return false
    }else{
        assignForm.clearErrors()
        return true
    }
}

const submit = () => {
    if(!isEdit.value)
    {
        if(validateDue())
            create()
    }
}

function updateList(index, date){
    let data = [...assigned.value]
    data[index].due = date

    let updated = data.splice(index, 1)[0]
    assigned.value.splice(index, 1, updated)
}

//get the assigned member
axios.get(`/task/assigned_member/${props.instrument.id}/${props.accredlvl}`)
.then(res => {
    assigned.value = res.data.assigned
}).catch(err=>{
    
})

</script>