import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useToastStore = defineStore('Toast', ()=>{

    const toggleMenu = ref(false)

    return{
        toggleMenu
    }
})