<template>
    <Modal @handleClose="$emit('handleClose')">
        
        <div class="min-h-[7rem] mt-7 mb-4 px-10 flex items-center justify-center">

            Exclude " {{area.title}} " from computation of overall rating

        </div>

        <div class="flex gap-1.5 mb-3 justify-end">
            <Cancel @click="$emit('handleClose')"/>
            <Submit @click="submit" :disabled="['lvl', 'inst'].includes(area.category)">
                Confirm
            </Submit>
        </div>
    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import Cancel from '../Buttons/Cancel.vue'
import Submit from '../Buttons/Submit.vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    area: Object
})

const emits = defineEmits([
    'handleClose',
])

const submit = () => {
    if(props.area.category != 'lvl' || props.area.category != 'inst')
        Inertia.visit(`/accreditation/instrument/exclude/${props.area.id}`, {
            method: 'get',
            preserveState: true,
            preserveScroll: true,
            onSuccess: page => {
                if(page.props.flash.success)
                    emits('handleClose')
            }
        })
}

</script>