<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            <div >Task Force</div>
        </template>

        <div v-if="!isAdd && !isChangeMember && !isEditChair && !isAddMember && !isMemberToRemove">
            <div class="mt-3" v-if="(Array.isArray(assignedTFM)?assignedTFM.length <= 0:false)">
                <AddTaskForce @click="isAdd = true">
                    Assign
                </AddTaskForce>
            </div>

            <div class="mt-4 py-3 text-center inter-font text-xs" v-if="(Array.isArray(assignedTFM)?assignedTFM.length <= 0:false)">
                No assigned task force
            </div>

            <div class="mt-4 py-3 flex justify-center" v-if="!assignedTFM">
                <Loading />
            </div>

            <div v-if="(Array.isArray(assignedTFM)?assignedTFM.length > 0:false)">

                <div class="mt-4 mb-2">Task force chairperson</div>
                <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md p-1">
                    <div class="h-11 w-full bg-gray-300/80 dark:bg-white/10 rounded flex items-center justify-between px-3 gap-2.5 inter-font itemCard">
                        {{assignedTFC.name}} 

                        <button class="hover:bg-dnscGreen hover:text-white/80 rounded p-1 px-4 transition_300 actionBtn" 
                            @click="changeChairPerson(assignedTFC)"
                        >
                                Change
                        </button>
                        
                    </div>
                </div>

                <div class="mt-4 mb-2 flex items-center justify-between">
                    Member 
                    <div class="flex gap-2">
                        <button class="hover:bg-dnscGreen hover:text-white/80 rounded p-1 px-4 transition_300 actionBtn" 
                        @click="isEditMember = true">Edit</button>

                        <button class="hover:bg-dnscGreen hover:text-white/80 rounded p-1 px-4 transition_300 actionBtn" 
                        @click="addEditMember">Add</button>
                    </div>
                </div>
                <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md max-h-[14rem] overflow-hidden flex">
                    <div class="w-full overflow-y-auto p-1">

                        <div class="w-full flex flex-col justify-center">
                            <div 
                                v-for="(user, index) in assignedTFM" :key="index"
                                class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 
                                cursor-pointer flex items-center px-3 gap-2.5 inter-font justify-between itemCard"
                            >
                                {{user.name}}

                                <div class="flex gap-1">
                                    <button 
                                        class="hover:bg-dnscGreen hover:text-white/80 rounded p-1 px-2 transition_300 actionBtn" 
                                        v-if="isEditMember"
                                        @click="editMember(user)"
                                    >
                                        Change
                                    </button>

                                    <div 
                                        class="hover:bg-red-500 hover:text-white/80 transition_300 p-0.5 pb-0 pr-[2.5px] rounded actionBtn"
                                        v-if="isEditMember"
                                        @click="removeTfMember(user)"
                                    >
                                        <Delete />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <AssignTFC
            v-if="(isAdd && !isEditMember && !isEditChair) && !taskForce.tfcId"
            @getChairperson="getTFC"
        />

        <AssignTFMember
            :tfcId="taskForce.tfcId"
            :tfcName="taskForce.tfc"
            :tfcMember="taskForce.members"
            v-if="(isAdd && !isEditMember && !isEditChair) && taskForce.tfcId"
            @getChairperson="getTFC"
            @handleMembers="getMember"
            @handleRemoveMember="removeMember"
        />

        <EditTaskForce
            :tfcId="taskForce.tfcId"
            :ChangeUser="taskForce.change"
            v-if="isChangeMember"
            @handleChange="changeMember"
        />

        <AddTaskForceMember
            :tfcId="taskForce.tfcId"
            :tfcMember="taskForce.members"
            :oldMember="assignedTFM"
            v-if="isAddMember"
            @handleMembers="getMember"
            @handleRemoveMember="removeMember"
        />

        <EditTaskForceChair
            :tfc="taskForce.change"
            :members="assignedTFM"
            v-if="isEditChair"
            @getChairperson="getNewTFC"
        />

        <RemoveMemberModal
            :area="area"
            :accredlvl="accredlvl"
            :user="memberToRemove"
            v-if="isMemberToRemove"
            @handleCancel="cancelAssign"
            @handleRemove="updateTFM"
        />

        <div class="mt-7 mb-3 flex gap-3 justify-end" v-if="isAdd || isEditMember || isChangeMember || isEditChair || isAddMember">
            <Cancel @click="cancelAssign" />
            <Submit v-if="taskForce.tfcId && (taskForce.change.new || taskForce.members.length > 0 )" @click="submit">
                <span v-if="isEditMember || isChangeMember || isEditChair || isAddMember">Update</span>
                <span v-else>Assign</span>
            </Submit>
        </div>
    </Modal>
</template>
<script setup>
import Modal from '../../../Modal.vue'
import AssignTFC from './AssignTF.vue'
import Loading from '../../../Loading.vue'
import Submit from '../../../Buttons/Submit.vue'
import Cancel from '../../../Buttons/Cancel.vue'
import AssignTFMember from './AssignTFMember.vue'
import Delete from '../../../Icons/deleteIcon.vue'
import RemoveMemberModal from './RemoveMember.vue'
import EditTaskForce from './EditTaskForceMember.vue'
import AddTaskForce from '../../../Buttons/Create.vue'
import AddTaskForceMember from './AddTaskForceMember.vue'
import EditTaskForceChair from './EditTaskForceChair.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { ref } from '@vue/reactivity'
import axios from 'axios'

const props = defineProps({
    area: Number,
    accredlvl: Number,
    isEdit: Boolean,
    selected: Object,
})
const emits = defineEmits(['handleClose'])

const taskForce = useForm({
    tfcId: null,
    tfc: null,
    members: [],
    change: {
        old: null,
        new: null
    }
})
const isAdd = ref(false)
const assignedTFC = ref(null)
const assignedTFM = ref(null)
const isEditChair = ref(false)
const isAddMember = ref(false)
const isEditMember = ref(false)
const isChangeMember =ref(false)
const memberToRemove = ref(null)
const isMemberToRemove = ref(false)

const cancelAssign = () => {
    taskForce.reset()
    isAdd.value = false
    isAddMember.value = false
    isEditChair.value = false
    isEditMember.value = false
    isChangeMember.value = false
    isMemberToRemove.value = false
}
const getTFC = user => {
    taskForce.tfcId = user.id
    taskForce.tfc = user.name
}
const addMember = member => {
    if(taskForce.members.length > 0){
        if(!existMember(member.id)){
            taskForce.members.push(member)
        }
    }else{
        taskForce.members.push(member)
    }
}
const existMember = (member) => {
    return  taskForce.members.find(({id}) => {
        if(id == member)
            return true
        else
            return false
    })
}
const getMember = user => {
    addMember(user)
}
const removeMember = index => {
    taskForce.members.splice(index, 1)
}
const editMember = user => {
    isChangeMember.value = true
    taskForce.change.old = user
    taskForce.tfcId = assignedTFC.value.userId
}
const addEditMember = () => {
    isAddMember.value = true
    taskForce.tfcId = assignedTFC.value.userId
}
const changeMember = user => {
    taskForce.change.new = user
}
const changeChairPerson = user => {
    isEditChair.value = true
    taskForce.change.old = user
    taskForce.tfcId = user.userId
}
const getNewTFC = user => {
    taskForce.change.new = user
}
const removeTfMember = member => {
    isEditMember.value = false
    isMemberToRemove.value = true
    memberToRemove.value = member
}
const updateTFM = user => {
    assignedTFM.value = assignedTFM.value.filter(({id}) => id != user.id)
    cancelAssign()
}

const submit = () => {
    if(isAdd.value){
        taskForce.post(`/program/assign/taskforce/${props.accredlvl}/${props.area}`, {
            onSuccess: page => {
                if(page.props.flash.success)
                {
                    emits('handleClose')
                }else{
                    console.log(page.props.flash.error)
                }
            }
        })
    }else if(isAddMember.value){
        taskForce.post(`/program/add_member/taskforce/${props.accredlvl}/${props.area}`, {
            onSuccess: page => {
                if(page.props.flash.success)
                {
                    emits('handleClose')
                }else{
                    console.log(page.props.flash.error)
                }
            }
        })
    }else if(isChangeMember.value){
        taskForce.post(`/program/change_member/taskforce/${props.accredlvl}/${props.area}`, {
            onSuccess: page => {
                if(page.props.flash.success)
                {
                    emits('handleClose')
                }else{
                    console.log(page.props.flash.error)
                }
            }
        })
    }else if(isEditChair.value){
        taskForce.post(`/program/change_chair/taskforce/${props.accredlvl}/${props.area}`, {
            onSuccess: page => {
                if(page.props.flash.success)
                {
                    emits('handleClose')
                }else{
                    console.log(page.props.flash.error)
                }
            }
        })
    }
}

axios.get(`/program/getAreaAssigned/${props.accredlvl}/${props.area}`)
.then(res => {
    let assigned = [...res.data.assigned]
    assigned.shift()
    assignedTFC.value = res.data.assigned[0]
    assignedTFM.value = assigned
})
.catch(err => {
    console.table(err)
})

</script>