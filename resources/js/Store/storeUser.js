import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useUserStore = defineStore('User', ()=>{

    const designation = ref([])
    const authorization = ref([])

    const get_des_and_auth = () => {
        if(designation.value.length === 0)
        {
            axios.get('/user/get_des_and_auth')
            .then(res => {
                authorization.value.push(...res.data.des_auth.auth)
                designation.value.push(...res.data.des_auth.designation)
            }).catch(err => {
                //console.log(err)
            })
        }
    }

    return{
        designation,
        authorization,
        get_des_and_auth,
    }
})