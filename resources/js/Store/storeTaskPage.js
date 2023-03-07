import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useTaskPageStore = defineStore('TaskPage', ()=>{

    const selected = ref(null)
    const updateSelect = ref(null)

    const reset = () => {
        selected.value = null
        updateSelect.value = null
    }

    return{
        reset,
        selected,
        updateSelect,
    }
})