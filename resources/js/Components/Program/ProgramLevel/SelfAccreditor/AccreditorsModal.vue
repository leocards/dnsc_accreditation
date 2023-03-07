<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Self Accreditor
        </template>

        <div v-if="!isAssign">
            <div class="mt-4">
                <AssignButton @click="isAssign = true">
                    Assign
                </AssignButton>
            </div>

            <div class="mt-4 py-3 text-center inter-font" v-if="myMethod.ifArray(accreditors, '==', 0)">
                No assigned self accreditor
            </div>

            <div class="mt-4 py-3 flex justify-center" v-if="!accreditors">
                <Loading />
            </div>

            <div class="border border-slate-200 dark:border-primaryDarkBorder mt-4 rounded-md max-h-[14rem] overflow-hidden flex" v-if="myMethod.ifArray(accreditors, '>', 0)">
                <div class="w-full overflow-y-auto p-1">
                    <div class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                    v-for="user in accreditors">
                        {{user.user}}
                    </div>
                </div>
            </div>
        </div>

        <CreateSA
            v-else
            :assigned="accreditors"
            :self_accreditors="self_accreditor.accreditors"
            @handleSelfAccreditors="getAccreditor"
        />

        <div class="mt-7 mb-3 flex gap-3 justify-end" v-if="isAssign">
            <Cancel @click="cancel" />
            <Submit @click="assignSA">
                Assign
            </Submit>
        </div>
        
    </Modal>
</template>
<script setup>
import Modal from '../../../Modal.vue'
import Loading from '../../../Loading.vue'
import Cancel from '../../../Buttons/Cancel.vue'
import Submit from '../../../Buttons/Submit.vue'
import CreateSA from './AssignSelfAccreditor.vue'
import AssignButton from '../../../Buttons/Create.vue'
import { ref } from '@vue/reactivity'
import myMethod from '../../../../Store/Methods'
import { useForm } from '@inertiajs/inertia-vue3'
import { warn } from '@vue/runtime-core'

const props = defineProps({
    accredlvl: Number,
    area: Object,
})
const emits = defineEmits(['handleClose'])

const isAssign = ref(false)
const accreditors = ref(null)
const self_accreditor = useForm({
    accreditors: [],
    area: props.area.id,
    accredlvl: props.accredlvl
})

const cancel = () => {
    isAssign.value = false
    self_accreditor.reset('accreditors')
}

const getAccreditor = user => {
    self_accreditor.accreditors.push(user)
}

const assignSA = () => {
    self_accreditor.post('/program/assign/selfAccreditor', {
        onSuccess: page => {
            emits('handleClose')
        }
    })
}

try {
    axios.post('/program/getassigned/selfAccreditor', {
        area: props.area.id,
        accredlvl: props.accredlvl,
    }).then(res => {
        accreditors.value = res.data.assigned
    }).catch(error => {
    })
} catch (e) {
    console.log(e)
}

</script>