<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Open " {{program.program}} " for Survey
        </template>

        <div class="flex gap-2 flex-wrap mt-5" v-if="!isComplete">

            <button 
                class="min-w-[12rem] grow py-3 rounded hover:bg-gray-300/80 dark:hover:bg-white/10 transition_300 actionBtn"
                @click="openSurvey(2, true)"
            >
                Self survey
            </button>

            <button 
                class="min-w-[12rem] grow py-3 rounded hover:bg-gray-300/80 dark:hover:bg-white/10 transition_300 actionBtn"
                @click="openSurvey(1, program.selfSurvey)"
            >
                Actual survey
            </button>

        </div>

        <div class="flex gap-2 flex-wrap mt-5 text-center px-4 max-w-[28rem] mx-auto" v-else>
            Cannot open for actual survey, self survey must be completed first.
        </div>
    </Modal>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia'
import { ref } from '@vue/reactivity'
import Modal from '../Modal.vue'

const props = defineProps({
    program: Object
})

const emits = defineEmits([
    'handleClose'
])

const isComplete = ref(false)
const loading = ref(false)

const openSurvey = (survey, isSelfSurveyComplete = false) => {
    if(!isSelfSurveyComplete)
        isComplete.value = true
    else{
        if(!loading.value){
            loading.value = true
            Inertia.post('/accreditation/open_survey', {
                id: props.program.id,
                survey: survey
            }, {
                onSuccess: page => {
                    emits('handleClose')
                }
            })
        }
    }
}

</script>