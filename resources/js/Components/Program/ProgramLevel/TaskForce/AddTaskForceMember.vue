<template>
    <div class="mt-5">
        <Search 
            v-model="modelValue"
            placeholder="Assign Member"
        />

        <div class="border mt-4 border-slate-200 dark:border-primaryDarkBorder rounded-md max-h-[14rem] overflow-hidden flex">
            <div class="w-full overflow-y-auto p-1">

                <div class="w-full flex flex-col justify-center">
                    <label for="Search" class="text-xs mx-auto w-full text-center" v-if="!modelValue">Search to assign</label>
                    <div class="text-xs mx-auto w-full text-center" v-if="myMethod.ifArray(searchResult, '<=', 0)">
                        No results found for " {{modelValue}} "
                    </div>
                    <div 
                        v-for="(user, index) in searchResult" :key="index"
                        @click="selectMember(user)"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                    >
                        {{user.name}}
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-4 mb-2">Member</div>
        <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md max-h-[14rem] overflow-hidden flex">
            <div class="w-full overflow-y-auto p-1">

                <div class="w-full flex flex-col justify-center">
                    <label for="Search" class="text-xs mx-auto w-full text-center" v-if="tfcMember.length <= 0">No member assigned</label>
                    
                    <div 
                        v-for="(user, index) in tfcMember" :key="index"
                        @click="$emit('handleRemoveMember', index)"
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 
                        cursor-pointer flex items-center px-3 gap-2.5 inter-font justify-between itemCard"
                    >
                        {{user.name}}

                        <Remove />
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import Search from '../../../Search.vue'
import Remove from '../../../Buttons/XButton.vue'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import axios from 'axios'
import myMethod from '../../../../Store/Methods'

const props = defineProps({
    tfcId: Number,
    tfcMember: Array,
    oldMember: Array,
})
const emits = defineEmits(['getChairperson', 'handleMembers', 'handleRemoveMember'])

const modelValue = ref('')
const searchResult = ref(null)

const selectMember = user => {
    emits('handleMembers', user)
    modelValue.value = ''
}

watch(modelValue, () => {
    if(modelValue.value == '')
        searchResult.value = null
    else
        axios.get(`/user/search/area_toAssign/${modelValue.value}/${props.tfcId}`)
        .then(res => {
            if(modelValue.value)
                searchResult.value = res.data.response.filter(({id}) => !props.oldMember.some(user => user.userId == id))
        })
})

const getTFC = user => {
    emits('getChairperson', user)
}

</script>