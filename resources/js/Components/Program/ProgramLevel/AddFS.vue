<template>
    <div class="mt-7">
        <form @submit.prevent="submit">
            <label for="name">Name</label>
            <span class="errorMessage ml-2" v-if="facultyStaff.errors.name">{{facultyStaff.errors.name}}</span>
            <div class="relative">
                <input type="text" class="mt-2" :class="[facultyStaff.errors.name?'formInput_error':'formInput']" placeholder="name" @input="search" v-model="facultyStaff.name">

                <div class="absolute max-h-[15rem] overflow-hidden subnavBg w-full rounded-md myshadow" v-if="isSuggest">
                    <ul class="max-h-[15rem] p-1.5 overflow-y-auto">
                        <li class="h-11 rounded hover:dark:bg-tertiaryDarkBg transition_300 flex items-center select-none cursor-pointer" 
                            v-for="user in results"
                            @click="selectSuggested(user)"
                        >
                            <div class="Oneline px-2">{{user.name}}</div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-4">
                <label for="institute">Designation</label>
                <span class="errorMessage ml-2" v-if="facultyStaff.errors.designation">{{facultyStaff.errors.designation}}</span>
                <select id="institute" class="mt-2" :class="[facultyStaff.errors.designation?'formInput_error':'formInput']" v-model="facultyStaff.designation">
                    <option value="">select</option>
                    <option :value="designation.id" v-for="designation in designations">{{designation.name}}</option>
                </select>
            </div>

            <div class="mt-7 mb-3 flex gap-3 justify-end">
                <Cancel width="w-32" @click="cancel" />
                <Submit width="w-32" :disabled="(facultyStaff.name == null || facultyStaff.designation == '')">
                    <span v-if="isEdit">Update</span>
                    <span v-else>Submit</span>
                </Submit>
            </div>

        </form>
    </div>
</template>
<script setup>
import Cancel from '../../Buttons/Cancel.vue'
import Submit from '../../Buttons/Submit.vue'
import { useForm } from "@inertiajs/inertia-vue3"
import axios from 'axios'
import { throttle } from 'lodash'
import { ref } from '@vue/reactivity'


const props = defineProps({
    isEdit: Boolean,
    program: Object,
    designations: Array,
    fs: Array,
})
const emits = defineEmits(['handleCancel', 'update'])

const facultyStaff = useForm({
    name: null,
    program: null,
    institute: null,
    designation: '',
})
const isSuggest = ref(false)
const results = ref([])
const hideSuggestions = ref(true)

const cancel = () => {
    facultyStaff.reset()
    facultyStaff.clearErrors()
    emits('handleCancel')
}

const getCurrentProgram = val => {
    facultyStaff.program = val.id
    facultyStaff.institute = val.instituteId
}
getCurrentProgram(props.program)

const create = () => {
    facultyStaff.post('/facultyStaff/create', {
        preserveScroll: true,
        onSuccess: page => {
            if(page.props.flash.success)
            {
                emits('handleCancel', true)
            }
        }
    })
}

const update = () => {
    
}

const removePosition = () => {

}

const submit = () => {
    if(!props.isEdit) { create() }
    else { update() }
}

const selectSuggested = user => {
    facultyStaff.name = user.name
    isSuggest.value = false
    results.value = []
}

const search = throttle(() => {
    if(facultyStaff.name && facultyStaff.name != '')
    {
        if(facultyStaff.name.trim() != '')
        {
            axios.post('/facultyStaff/suggestNames', {search: facultyStaff.name})
            .then(res => {
                results.value = res.data.filter(({name}) => !props.fs.some(user => name == user.name))

                if(res.data.length == 0) isSuggest.value = false
                else isSuggest.value = true
            })
        }else {isSuggest.value = false; results.value = []}
    }else {isSuggest.value = false; results.value = []}

    if(!facultyStaff.name || facultyStaff.name == '' || facultyStaff.name == " ")
    {
        isSuggest.value = false
        results.value = []
    }

}, 700)

</script>