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
                        @click="newTFC(user)"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                    >
                        {{user.name}}
                    </div>
                </div>
                   
            </div>
        </div>

        <div class="mt-4 mb-2">Task force chairperson</div>
        <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md p-1">
            <div class="h-11 w-full bg-gray-300/80 dark:bg-white/10 rounded flex items-center px-3 gap-2.5 inter-font itemCard" v-if="tfc.new">
                {{tfc.new.name}}
            </div>

            <div v-if="!tfc.new" class="text-center">
                <label for="Search" class="text-xs mx-auto w-full text-center">No chairperson assigned</label>
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
    accredlvl: Number,
    tfc: Object,
    members: Array
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
            if(modelValue.value){
                searchResult.value = res.data.response.filter(({id}) => id != props.tfc.old.userId)
                searchResult.value = searchResult.value.filter(({id}) => !props.members.some(user => user.userId == id))
            }
        })
})

const newTFC = user => {
    emits('getChairperson', user)
    modelValue.value = ''
}

</script>