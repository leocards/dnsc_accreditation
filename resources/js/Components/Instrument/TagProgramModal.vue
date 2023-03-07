<template>
    <Modal @handle-close="$emit('handleClose')">
        <template #header>
            <span v-if="!isEdit">Tag Program</span>
            <span v-else>Update tagged Program</span>
        </template>
        
        <div v-if="!tag">
            <div class="mt-6">
                <Search
                    v-model="modelValue"
                    placeholder="Search program"
                />

                <div class="border border-slate-200 dark:border-primaryDarkBorder mt-4 rounded-md max-h-[14rem] overflow-hidden flex" v-if="searchResult">
                    <div class="w-full overflow-y-auto p-1">
                        <OptionCard 
                            v-for="program in searchResult"
                            :label="program.program_name"
                            :title="program.program_name"
                            @click="getProgramToTag(program)"
                            v-if="searchResult.length > 0"
                        />
                        <div v-else class="text-xs w-full text-center text-black/60 dark:text-white/30">No results found for " {{modelValue}} " </div>
                    </div>
                </div>

                <div class="flex justify-center p-1 border border-slate-200 dark:border-primaryDarkBorder mt-4 rounded-md" v-if="modelValue && !searchResult">
                    <Loading  />
                </div>
            </div>

            <div class="mt-7">
                <div>Tagged Program</div>
                <div class="border border-slate-200 dark:border-primaryDarkBorder mt-3 rounded-md max-h-[14rem] overflow-hidden flex">
                    <div class="w-full overflow-y-auto p-1">
                        <OptionCard 
                            v-for="program in programTagged"
                            :label="program.program_name"
                            :title="program.program_name"
                            @click="getProgramToUpdate(program)" 
                        />
                        <div v-if="(Array.isArray(programTagged) ? programTagged.length === 0: false)" 
                        class="text-center text-black/60 dark:text-white/30">No tagged program</div>
                        
                        <div class="flex justify-center">
                            <Loading v-if="!programTagged" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <div class="mt-6">
                <OptionCard 
                    :label="selectedProgram.program_name"
                    :title="selectedProgram.program_name"
                    class="pointer-events-none"
                />
            </div>

            <div class="mt-7 max-w-[29rem]">

                <form @submit.prevent="submit">
                    <label for="selfSurvey" class="w-fit">Date of self survey</label> 
                    <span class="errorMessage ml-2" v-if="tagForm.errors.date_of_self_survey">{{tagForm.errors.date_of_self_survey}}</span>
                    <input type="date" class="mt-2 mb-6"
                        :class="[tagForm.errors.date_of_self_survey ? 'formInput_error':'formInput']"
                        v-model="tagForm.date_of_self_survey"
                    >

                    <label for="selfSurvey" class="w-fit">Date of actual survey</label> 
                    <span class="errorMessage ml-2" v-if="tagForm.errors.date_of_actual_survey">{{tagForm.errors.date_of_actual_survey}}</span>
                    <input type="date" class="mt-2"
                        :class="[tagForm.errors.date_of_actual_survey ? 'formInput_error':'formInput']"
                        v-model="tagForm.date_of_actual_survey"
                    >

                    <div class="mt-7 mb-3 flex gap-3 justify-end">
                        <Cancel width="w-32" @click="cancelTag" />
                        <Submit width="w-32">
                            <span v-if="!isEdit">Save</span>
                            <span v-else>Update</span>
                        </Submit>
                    </div>
                </form>

            </div>
        </div>

    </Modal>
</template>

<script setup>
import Modal from '../Modal.vue'
import Search from '../Search.vue'
import Loading from '../Loading.vue'
import OptionCard from './ItemCard.vue'
import Submit from '../Buttons/Submit.vue'
import Cancel from '../Buttons/Cancel.vue'
import { ref } from '@vue/reactivity'
import { useForm } from '@inertiajs/inertia-vue3'
import { watch } from '@vue/runtime-core'
import axios from 'axios'

const props = defineProps({level: Object})
const emits = defineEmits(['handleClose', 'update'])

const programs = ref([{id: 1, program_name: 'Bachelor of Science in Information Technology', dat_actual_survey: '2023-02-23',
    dat_self_survey: '2023-03-30'}])

const tagForm = useForm({
    level: null,
    program: null,
    date_of_self_survey: null,
    date_of_actual_survey: null,
})

const tag = ref(false)
const isEdit = ref(false)
const modelValue = ref('') //value for search
const searchResult = ref(null)
const programTagged = ref(null)
const selectedProgram = ref(null)

const cancelTag = () => {
    tag.value = false
    tagForm.reset()
    tagForm.clearErrors()
    selectedProgram.value = null
}

const getProgramToTag = (program) => {
    tag.value = true
    modelValue.value = ''
    tagForm.program = program.id
    tagForm.level = props.level.id
    selectedProgram.value = program
}
const getProgramToUpdate = (program) => {
    tag.value = true
    isEdit.value = true
    selectedProgram.value = program
    tagForm.level = props.level.id
    tagForm.program = program.program
    tagForm.date_of_self_survey = program.date_self_survey.slice(0, 10)
    tagForm.date_of_actual_survey = program.date_actual_survey.slice(0, 10)
}


watch(modelValue, ()=>{
    if(modelValue.value == ''){
        searchResult.value = null
    }
    else{
        axios.get('/program/search/'+modelValue.value)
        .then(res => {
            searchResult.value = res.data.programs
        })
    }
})

const create = () => {
    tagForm.post('/accreditation/create', {
        preserveScroll: true,
        onSuccess: page => {
            emits('update', [])
            emits('handleClose')
            //console.log(page.props.flash)
        }
    })
}

const update = () => {
    tagForm.post('/accreditation/update_tag_program', {
        preserveScroll: true,
        onSuccess: page => {
            if(page.props.flash.success){
                emits('update', [])
                emits('handleClose')
            }else{
                console.log(page.props.flash)
            }
        }
    })
}

const submit = () => {
    if(!isEdit.value){ create() }
    else if(isEdit.value){
        update()
    }
}

axios.get('/accreditation/tagged/'+props.level.id)
    .then(res => {
        programTagged.value = res.data.tagged
    })
    .catch(err => { })

</script>