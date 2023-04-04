<template>
    <Modal @handleClose="">
        <template #header>
            <div>
                Instrument details
            </div>
        </template>

        <div class="mt-4 mb-3">

            <div>
                <div class="dark:text-white/40">Label</div>
                <div>{{inst.title}}</div>
            </div>
            <div class="mt-3 max-w-[30rem]" v-if="inst.description">
                <div class="dark:text-white/40">Description</div>
                <div>{{inst.description}}</div>
            </div>
            <div class="mt-3 max-w-[30rem]" v-if="inst.attachment">
                <div 
                    v-if="myMethod.isValidJson(inst.attachment)" 
                    v-for="(item, index) in myMethod.isValidJson(inst.attachment)" 
                    :key="index"
                    :class="{'mt-3':(index != 0)}"
                >
                    <div class="dark:text-white/40">Evidence to attach</div>
                    <div>{{item.evidence}}</div>
                </div>
                <div v-else>
                    <div class="dark:text-white/40">Evidence to attach</div>
                    <div>{{inst.attachment}}</div>
                </div>
            </div>

        </div>

    </Modal>
</template>
<script setup>
import Modal from '../Modal.vue'
import myMethod from '../../Store/Methods'

const props = defineProps({
    inst: Object
})

</script>