import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useInstrumentStore = defineStore('Instrument', ()=>{

    const selected = ref(null)
    const updateSelect = ref(null)
    const instruments = ref([])
    const area = ref(null) //id of area to store when creating indicators/item

    const getInstruments = (inst) => {
        instruments.value = inst
    }

    return{
        area,
        selected,
        instruments,
        updateSelect,
        getInstruments,
    }
})