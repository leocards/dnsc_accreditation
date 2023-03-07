import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useLayoutStore = defineStore('Layout', ()=>{

    const toggleMenu = ref(false)

    const keepScrollHidden = ref(false)

    return{
        toggleMenu,
        keepScrollHidden
    }
})