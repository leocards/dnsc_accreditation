<style scoped>
.padding_right {
    padding-right: 2.8rem !important;
}
</style>

<template>
    <div class="w-full h-fit relative ">
        <input 
            type="text" 
            id="Search" 
            autocomplete="off"
            :placeholder="placeholder" 
            class="formInput padding_right" 
            :value="modelValue"
            @input="getSearchValue($event.target.value)"
            
        >

        <button class="absolute p-1 right-2.5 top-[6.5px] rounded hover:hover_green hover:dark:text-white/80 transition duration-300 actionBtn
        hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
        v-if="!modelValue"
        @click="focusSearch">
            <SearchIcon />
        </button>
        <button class="absolute p-1 right-2.5 top-[6.5px] rounded hover:hover_green hover:dark:text-white/80 transition duration-300 actionBtn
        hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
        v-if="modelValue"
        @click="clearSearch">
            <XIcon />
        </button>
    </div>
    
</template>

<script setup>
import XIcon from '../Components/Icons/closeIcon.vue'
import SearchIcon from '../Components/Icons/searchIcon.vue'
import { throttle } from 'lodash'
import { ref } from '@vue/reactivity'

const props = defineProps({placeholder: String, modelValue: String})
const emits = defineEmits(['handleSearch', 'handleClear', 'update:modelValue'])

//toggle the search Icon
const toggleSearch = ref(false)

const focusSearch = () => {
    document.getElementById('Search').focus()
}

const clearSearch = () => {
    focusSearch()
    toggleSearch.value = false
    getSearchValue.cancel()
    emits('update:modelValue', '')
}

const getSearchValue = throttle(searches => {
    if(!searches || searches == '') { 
        emits('update:modelValue', '')
        toggleSearch.value = false
    }else{
        if(searches.trim() != ''){
            emits('update:modelValue', searches)
            toggleSearch.value = true
        }
    }
}, 900)

</script>