<template>
    <div class="mt-4">
        <Search
            v-model="modelValue"
            placeholder="Assign Self Accreditor"
        />

        <div class="border border-slate-200 dark:border-primaryDarkBorder mt-4 rounded-md max-h-[14rem] overflow-hidden flex">
            <div class="w-full overflow-y-auto p-1">

                <div class="w-full flex flex-col justify-center">
                    <label for="Search" class="mx-auto w-full text-center" v-if="!modelValue && !searchResult">Search to assign</label>

                    <div class=" mx-auto w-full text-center" v-if="myMethod.ifArray(searchResult, '<=', 0)">
                        No results found for " {{modelValue}} "
                    </div>
                    
                    <div class="flex justify-center py-2" v-if="modelValue && !searchResult">
                        <Loading />
                    </div>

                    <div 
                        v-for="(user, index) in searchResult" :key="index"
                        @click="getSA(user)"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                    >
                        {{user.name}}
                    </div>
                </div>
                   
            </div>
        </div>

        <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md max-h-[14rem] overflow-hidden flex mt-5">
            <div class="w-full overflow-y-auto p-1">

                <div class="w-full flex flex-col justify-center">
                    <label class="mx-auto w-full text-center" v-if="self_accreditors.length <= 0">No self accreditor assigned</label>
                    
                    <div 
                        v-for="(user, index) in self_accreditors" :key="index"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 
                        cursor-pointer flex items-center px-3 gap-2.5 inter-font justify-between itemCard"
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
import Loading from '../../../Loading.vue'
import axios from 'axios'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import myMethod from '../../../../Store/Methods'

const props = defineProps({
    self_accreditors: Array,
    assigned: Array
})
const emits = defineEmits(['handleSelfAccreditors'])

const modelValue = ref('')
const searchResult = ref(null)

watch(modelValue, ()=> {
    if(modelValue.value == '')
        searchResult.value = null
    else
        axios.get(`/user/search/selfaccreditor/${modelValue.value}`)
        .then(res => {
            searchResult.value = res.data.result.filter(({id}) => !props.self_accreditors.some(user => user.id === id))
            searchResult.value = searchResult.value.filter(({id}) => !props.assigned.some(user => user.userId === id))
        })
})

const getSA = user => {
    emits('handleSelfAccreditors', user)
    modelValue.value = ''
}

</script>