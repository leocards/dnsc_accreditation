<script setup>
import { ref } from 'vue';
import Modal from '../Modal.vue'
import Loading from '../Loading.vue'
import VerifiedAccord from './VerifiedAccord.vue';

const close = defineEmits(['close'])
const props = defineProps({
    id: Number,
    accreds: Object
})

const accred = ref(null) 

try {

    axios.get('/ratings/'+props.id)
    .then(res => {
        accred.value = res.data
    })

} catch {

}
</script>

<template>
    <Modal width="max-w-[60rem]" @handleClose="$emit('close')">
        <div class="text-lg mb-3">Ratings</div>

        <div class="flex px-2 text-lg mb-3">
            {{ accreds.title }} <span class="ml-auto"> rate: {{ accreds.rate }} </span>
        </div>

        <div class="max-h-[75vh] overflow-y-auto" v-if="accred">
            <div class="mb-2" v-for="(item, index) in accred">
                <VerifiedAccord :key="index" :inst="item" v-if="accred" />
            </div>
        </div>

        <div class="w-full flex justify-center" v-else>
            <Loading />
        </div>
    </Modal>
</template>