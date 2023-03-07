<template>
    <div class="mt-5">
        <Search 
            v-model="modelValue"
            placeholder="Assign Chairperson"
        />

        <div class="border border-slate-200 dark:border-primaryDarkBorder mt-4 rounded-md max-h-[14rem] overflow-hidden flex">
            <div class="w-full overflow-y-auto p-1">

                <div class="w-full flex flex-col justify-center">
                    <label for="Search" class="text-xs mx-auto w-full text-center" v-if="!modelValue">Search to assign</label>
                    <div class="text-xs mx-auto w-full text-center" v-if="myMethod.ifArray(searchResult, '<=', 0) && modelValue">
                        No results found for " {{modelValue}} "
                    </div>
                    <div 
                        v-for="(user, index) in searchResult" :key="index"
                        @click="getTFC(user)"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                    >
                        {{user.name}}
                    </div>
                </div>
                   
            </div>
        </div>
    </div>
</template>

<script setup>
import Search from '../../../Search.vue'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import axios from 'axios'
import myMethod from '../../../../Store/Methods'

const props = defineProps({
    area: Number,
    accredlvl: Number,
})
const emits = defineEmits(['getChairperson'])

const modelValue = ref('')
const searchResult = ref(null)

watch(modelValue, () => {
    if(modelValue.value == '')
        searchResult.value = null
    else
        axios.get(`/user/search/area_toAssign/${modelValue.value}`)
        .then(res => {
            if(modelValue.value)
                searchResult.value = res.data.response
        })
})

const getTFC = user => {
    emits('getChairperson', user)
}

</script>