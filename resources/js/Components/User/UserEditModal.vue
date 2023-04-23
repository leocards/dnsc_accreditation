<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Register
        </template>

        <form @submit.prevent="submit" v-if="!isLoading">
            <div class="border border-slate-300/80 dark:border-primaryDarkBorder p-1 rounded-md mt-3 mb-6">
                <div class="bg-black/5 dark:bg-white/10 rounded p-3 text-center">
                    {{user.name}}
                </div>
            </div>
            <div>
                <label for="designation">Designation</label>
                <span class="errorMessage ml-2" v-if="register.errors.designation">{{register.errors.designation}}</span>
                <select id="designation" class="formInput mt-2" v-model="register.designation" @change="changeDesignation(register.designation)">
                    <option value="">select</option>
                    <option :value="des.id" v-for="des in designation">{{des.name}}</option>
                </select>
            </div>

            <div class="mt-5">
                <label for="Institute">Institute</label>
                <span class="errorMessage ml-2" v-if="register.errors.institute">{{register.errors.institute}}</span>
                <select id="Institute" class="formInput mt-2" v-model="register.institute"
                :disabled="!register.designation || ([6, 7, 8].includes(register.designation))"
                >
                    <option value="">select</option>
                    <option :value="institute.id" v-for="institute in institutes">{{institute.abbreviation}}</option>
                </select>
            </div>

            <div class="mt-5">
                <label for="program">Program</label>
                <span class="errorMessage ml-2" v-if="register.errors.program">{{register.errors.program}}</span>
                <select id="program" class="formInput mt-2" v-model="register.program"
                :disabled="!register.designation || ([1, 4, 5, 6, 7, 8].includes(register.designation))">
                    <option value="">select</option>
                    <option 
                        :value="program.id" 
                        v-if="register.institute" 
                        v-for="program in (programs.filter(({instituteId}) => instituteId == register.institute))"
                    >
                        {{program.abbreviation}}
                    </option>
                </select>
            </div>

            <div class="mt-7 mb-3">
                <Submit width="w-32 ml-auto" :disabled="!register.designation">
                    <span v-if="isEdit">Update</span>
                    <span v-else>Register</span>
                </Submit>
            </div>
        </form>

        <div class="w-full grow flex justify-center mt-6" v-else>
            <Loading />
        </div>
    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Loading from '../Loading.vue'
import Submit from '../Buttons/Submit.vue'
import axios from 'axios'
import { useForm } from '@inertiajs/inertia-vue3'
import { ref } from '@vue/reactivity'

const props = defineProps({
    user: Object,
    isEdit: Boolean,
})
const emits = defineEmits(['handleClose', 'update'])

const register = useForm({
    designation: '',
    auth: null,
    institute: '',
    program: '',
    user: props.user.id,
})
const isLoading = ref(true)
const auths = ref(null)
const designation = ref(null)
const programs = ref(null)
const institutes = ref(null)

const changeDesignation = id => {
    let auth = auths.value.find(auth => {
        return auth.roles.includes(id) ?? null
    })

    register.auth = auth ? auth.id : null

    if([6, 7, 8].includes(id) || !id) 
    {
        register.reset('institute')
        register.reset('program')
    }
    if([1, 4, 5].includes(id))
    {
        register.reset('program')
    }
}

const registerUser = () => {
    register.post('/user/request/register', {
        preserveScroll: true,
        onSuccess: page => {
            ////console.log(page.props.flash)
            emits('update')
        }
    })
}

const submit = () => {
    if(!props.isEdit)
    {
        registerUser()
    }
}

function getAuthsAndDes(){
    return axios.get('/user/get_des_and_auth')
}
function getPrograms(){
    return axios.get('/program/getPrograms')
}
function getInstitutes(){
    return axios.get('/institute/getInstitutes')
}

Promise.all([getAuthsAndDes(), getPrograms(), getInstitutes()])
.then(res => {
    auths.value = res[0].data.des_auth.auth
    designation.value = res[0].data.des_auth.designation
    programs.value = res[1].data.programs
    institutes.value = res[2].data.institutes
    isLoading.value = false
})
.catch(err => {
    //console.log(err)
})

</script>