import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useAccreditorStore = defineStore('Accreditor', () => {
    const selected = ref(null)
    const selectedInst = ref(null)

    const resetValues = () => {
        selected.value = null
    }

    const selectInstrument = inst => {
        selected.value = inst
    }

    return {
        selected,
        selectedInst,

        //methods
        resetValues,
        selectInstrument,
    }
})