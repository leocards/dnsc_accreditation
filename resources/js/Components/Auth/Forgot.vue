<template>
    <div class="mx-auto z-30 lg:ml-[] min-h-[20rem] md:w-[30rem] max-sm:w-[26rem] subnavBg rounded-lg py-6 px-8 my-8 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]">
        <div class="w-full text-center pb-2 text-lg">
            <span class="font-semibold text-dnscGreen"> Forgot Password </span>
        </div>

        <div class="mt-3">
            <label for="username">Username</label>
            <span class="errorMessage ml-2 inter-font" v-if="forgot.errors.username">{{forgot.errors.username}}</span>
            <input type="text" class="formInput mt-2" placeholder="username" :disabled="isConfirm" v-model="forgot.username">

            <div class="mb-5 mt-10" v-if="!isConfirm">
                <Submit width="w-full" @click="verify">
                    Verify username
                </Submit>
            </div>
        </div>

        <form @submit.prevent="changePassword" v-if="isConfirm">
            <div class=" mt-3 ">
                <label for="password">New Password</label>
                <span class="errorMessage ml-2 inter-font" v-if="forgot.errors.new_password">{{forgot.errors.new_password}}</span>
                <div class="relative mt-2">
                    <input type="password" ref="pass" v-model="forgot.new_password" id="password" class="formInput" style="padding-right: 2.5rem;" placeholder="new password">
                    <button type="button" class="absolute top-[9px] right-2.5" @click="togglePass">
                        <EyeClose v-if="ispasswordHidden" />
                        <EyeOpen v-if="!ispasswordHidden" />
                    </button>
                </div>
            </div>
            <div class=" mt-3 ">
                <label for="cpassword">Confirm password</label>
                <span class="errorMessage ml-2 inter-font" v-if="forgot.errors.confirm_password">{{forgot.errors.confirm_password}}</span>
                <div class="relative mt-2">
                    <input type="password" ref="cpass" v-model="forgot.confirm_password" id="cpassword" class="formInput" style="padding-right: 2.5rem;" placeholder="confirm password">
                    <button type="button" class="absolute top-[9px] right-2.5" @click="toggleCPass">
                        <EyeClose v-if="isCpasswordHidden" />
                        <EyeOpen v-if="!isCpasswordHidden" />
                    </button>
                </div>
            </div>

            <div class="mb-5 mt-10" v-if="isConfirm">
                <Submit width="w-full" @click="verify">
                    Change password
                </Submit>
            </div>
        </form>

        <div class="border-t pt-6 border-slate-200 dark:border-primaryDarkBorder">
            <Link href="/index" class="pb-4 w-fit">
                <span class="hover:text-green-600 hover:underline">Login</span>
            </Link>
            <div class="mb-2"></div>
            <Link href="/register" class="pb-4 w-fit">Don't have account ?
                <span class="hover:text-green-600 hover:underline">Pre register</span>
            </Link>
        </div>
    </div>
</template>

<script setup>
import Submit from '../Buttons/Submit.vue'
import EyeOpen from '../Icons/eyeOpenIcon.vue'
import EyeClose from '../Icons/eyeCloseIcon.vue'
import { ref } from '@vue/reactivity'
import { useForm } from '@inertiajs/inertia-vue3'
import axios from 'axios'

const isConfirm = ref(false)
const ispasswordHidden = ref(true)
const isCpasswordHidden = ref(true)
const pass = ref(null)
const cpass = ref(null)
const forgot = useForm({
    username: null,
    new_password: null,
    confirm_password: null
})
const togglePass = () => {
    if(ispasswordHidden.value)
    {
        pass.value.type = 'text'
        ispasswordHidden.value = false
    }else{
        pass.value.type = 'password'
        ispasswordHidden.value = true
    }
}
const toggleCPass = () => {
    if(isCpasswordHidden.value)
    {
        cpass.value.type = 'text'
        isCpasswordHidden.value = false
    }else{
        cpass.value.type = 'password'
        isCpasswordHidden.value = true
    }
}
const verify = () => {
    //isConfirm.value = true
    axios.post('/verify_username', {
        ...forgot
    })
    .then(res => {
        forgot.username = res.data
        forgot.clearErrors()
        isConfirm.value = true
    }).catch(error=>{
        if(error.response.status == 422)
            forgot.errors.username = error.response.data.message
    })
}
const changePassword = () => {
    forgot.post('/change_my_password')
}

</script>