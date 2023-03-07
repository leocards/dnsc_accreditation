import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useProglevelStore = defineStore('Proglevel', ()=>{

    const selected = ref(null)
    const updateSelect = ref(null)
    const instruments = ref([])

    const getInstruments = (inst) => {
        instruments.value = inst
    }

    return{
        selected,
        instruments,
        updateSelect,
        getInstruments,
    }
})