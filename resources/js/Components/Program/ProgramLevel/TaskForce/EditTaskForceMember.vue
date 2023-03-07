<template>
    <div class="mt-5">
        <Search 
            v-model="modelValue"
            @handleClear=""
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
                    <label for="Search" class="text-xs mx-auto w-full text-center" v-if="!ChangeUser.new">No member assigned</label>
                    
                    <div
                        v-else
                        class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 
                        cursor-pointer flex items-center px-3 gap-2.5 inter-font justify-between itemCard"
                    >
                        {{ChangeUser.new.name}}
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
    ChangeUser: Object,
})

console.log(props.ChangeUser)

const emits = defineEmits(['handleChange'])

const modelValue = ref('')
const searchResult = ref(null)

const selectMember = user => {
    emits('handleChange', user)
    modelValue.value = ''
}

watch(modelValue, () => {
    if(modelValue.value == '')
        searchResult.value = null
    else
        axios.get(`/user/search/area_toAssign/${modelValue.value}/${props.tfcId}`)
        .then(res => {
            if(modelValue.value)
                searchResult.value = res.data.response.filter(({id}) => { return id != props.ChangeUser.old.userId})
        })
})

</script>