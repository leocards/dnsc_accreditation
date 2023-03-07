<template>
    <Modal width="w-[33rem]" @handleClose="$emit('handleClose')">
        <template #header>
            Faculty and Staff
        </template>

        <div v-if="!isAddFS">
            <div class="my-4">
                <CreatBtn @click="isAddFS = true">
                    Add
                </CreatBtn>
            </div>

            <div class="w-full h-10 dark:text-white/80 text-white bg-dnscGreen rounded-md">
                <div class="h-full grid grid-cols-[calc(100%-10rem),10rem] items-center" >
                    <div class="pl-2">Name</div>
                    <div class="">Designation</div>
                    <div class="" v-if="false">Edit</div>
                </div>
            </div>

            <div class="w-full max-h-[65vh] mt-3 border dark:border-primaryDarkBorder rounded-md flex overflow-hidden">
                <div class="min-h-fit overflow-y-auto grow p-1 flex flex-col gap-1">
                    <div class="grid grid-cols-[calc(100%-10rem),10rem] items-center h-10 hover:dark:bg-white/10 transition_300 rounded p-2 shrink-0"
                    v-for="(fs, index) in faculties" :key="index">
                        <div class="Oneline">{{fs.name}}</div>
                        <div class="pl-2">{{fs.job}}</div>
                        <div class="flex items-center justify-center" v-if="false">
                            <button 
                                class="w-fit px-2 py-px rounded bg-dnscGreen disabled:bg-tertiaryDarkBg disabled:dark:text-white/30" 
                                :disabled="!fs.isEditable"
                                @click=""
                            >
                                Edit
                            </button>
                        </div>
                    </div>

                    <div class="text-xs text-center my-2" 
                    v-if="(Array.isArray(faculties)? faculties.length === 0 : false)">
                        Empty list
                    </div>

                    <div class="mx-auto my-2" v-if="!faculties">
                        <Loading />
                    </div>
                </div>
            </div> 
        </div>

        <AddFacultyStaff 
            :fs="faculties??[]"
            :isEdit="isEdit"
            :program="program"
            :designations="designations"
            v-if="isAddFS"
            @handleCancel="cancel"
        />

    </Modal>
</template>
<script setup>
import Modal from '../../Modal.vue'
import Loading from '../../Loading.vue'
import AddFacultyStaff from './AddFS.vue'
import CreatBtn from '../../Buttons/Create.vue'
import axios from 'axios'
import { ref } from '@vue/reactivity'
import { useUserStore } from "../../../Store/storeUser"

const storeUser = useUserStore()

const props = defineProps({
    program: Object
})

const emits = defineEmits(['handleClose'])

const isEdit = ref(false)
const isAddFS = ref(false)
const faculties = ref(null)
const designations = ref([])

designations.value.splice(5, 3)//remove last 3 element

const getFaculties = async () => {
    let jobs = await axios.get('/user/get_des_and_auth')
    let facultyStaffs = await axios.get(`/facultyStaff/${props.program.id}/${props.program.instituteId}`)
    let res = await facultyStaffs.data.facultyStaff
    let des = await jobs.data.des_auth.designation

    console.log(res)

    designations.value = [...des.filter(({id}) => ![6, 7, 8, 9].includes(id))]

    faculties.value = res.map( fs => {
        return {
            designation: fs.designation,
            id: fs.id,
            instituteId: fs.instituteId,
            job: des.find(({id}) => id == fs.designation).name,
            name: fs.name,
            programId: fs.programId
        }
    })

    let pc = faculties.value.find(({designation}) => designation == 2) ?? null
    let dean = faculties.value.find(({designation}) => designation == 1) ?? null

    pc ? designations.value.splice(1, 1) : ''
    dean ? designations.value.shift() : ''
}
getFaculties()

const cancel = (val = false) => {
    isEdit.value = false
    isAddFS.value = false

    val ? getFaculties() : ''
}



</script>