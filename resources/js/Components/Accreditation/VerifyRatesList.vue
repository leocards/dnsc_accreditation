<template>
    <div class="fixed subnavBg myshadow rounded-md p-1.5 w-[19rem] h-fit right-4 overflow-hidden">
        <div class="overflow-y-auto max-h-[25.8rem]">
            <div class="w-full h-10 hover:bg-gray-300/80 dark:hover:bg-tertiaryDarkBg rounded flex items-center transition_300 px-2 cursor-default" v-for="(item, index) in accreds" :key="index">
                <div class="grow mr-1 h-fit Oneline">
                    {{item.title}}
                </div>
                <button class="w-fit px-2 py-1 hover:text-white transition_300 hover:bg-dnscGreen rounded" @click="$emit('handleViewRates', item)">
                    View
                </button>
            </div>

            <div class="w-full pl-2 py-2" v-if="myMethod.ifArray(accreds, '==', 0)">
                Nothing to verify
            </div>

            <div class="w-full py-2.5 flex justify-center" v-if="!accreds">
                <Loading />
            </div>
        </div>
    </div>
</template>

<script setup>
import Loading from '../Loading.vue'
import { ref } from "@vue/reactivity";
import axios from "axios";
import myMethod from '../../Store/Methods'

const emits = defineEmits([
    'handleViewRates'
])

const accreds = ref(null)

try{

    axios.get('/unverified')
    .then(res => {
        accreds.value = res.data
    })

} catch (e) {

}

</script>
