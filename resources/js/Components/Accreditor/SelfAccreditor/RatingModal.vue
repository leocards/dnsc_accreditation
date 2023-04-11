<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Rate " {{inst.title}} "
        </template>


        <div class="mt-4">
            <form @submit.prevent="submitRate">
                <span class="errorMessage" v-if="rateForm.errors.rate">{{rateForm.errors.rate}}</span>
                <input ref="rateInput" type="text" :class="[rateForm.errors.rate?'formInput_error':'formInput']" v-model="rate" placeholder="rate">

                <div class="flex justify-end mt-3 mb-3">
                    <Submit :disabled="(!rate || rateForm.errors.rate) || rateForm.processing">
                        Submit
                    </Submit>
                </div>
            </form>
        </div>

    </Modal>
</template>
<script setup>
import Modal from '../../Modal.vue'
import Submit from '../../Buttons/Submit.vue'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({inst: Object})
const emits = defineEmits(['handleClose'])

const rate = ref(null)
const rateForm = useForm({
    rate: null,
    id: props.inst.rate.id
})
const rateInput = ref(null)

const submitRate = () => {
    rateForm.rate = rate.value
    rateForm.post('/self_accreditor/rate', {
        onSuccess: page => {
            if(page.props.flash.success)
            {
                emits('handleClose')
            }else{
                console.log(page.props.flash)
            }
        },
        onError: err => {
            console.log(err)
        }
    })
}

watch(rate, (value) => {
    let val = isNaN(value) ? value : parseFloat(value)

    if(val < 0)   
        rateForm.errors.rate = 'rate must not lesser than 0'
    else if (val > 5)
        rateForm.errors.rate = 'rate must not greater than 5'
    else if (typeof val === "string" && val != 'NA')
        rateForm.errors.rate = 'rate must be a number or NA'
    else 
        rateForm.errors.rate = null
})

</script>