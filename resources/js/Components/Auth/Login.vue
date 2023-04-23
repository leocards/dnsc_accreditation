<template>
    <div class="mx-auto z-30 lg:ml-[] min-h-[20rem] md:w-[30rem] max-sm:w-[24rem] subnavBg my-20 rounded-lg pt-8 px-8 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative">

        <SuccesReg :data-succesReg="succesReg($page.props.flash.success)" v-if="successReg" />

        <div class="w-full text-center pb-3 text-lg capitalize">
            <span class="font-semibold uppercase text-dnscGreen"> Welcome </span>
        </div>

        <form @submit.prevent="submit">
            <div class=" mt-5">
                <label for="username">Username</label>
                <span class="errorMessage ml-2 inter-font" v-if="Auth.errors.username">{{Auth.errors.username}}</span>
                <input type="text" id="username" v-model="Auth.username" class="formInput mt-2" placeholder="username">
            </div>
            <div class=" mt-7 ">
                <label for="password">Password</label>
                <span class="errorMessage ml-2 inter-font" v-if="Auth.errors.password">{{Auth.errors.password}}</span>
                <div class="relative mt-2">
                    <input type="password" ref="pass" v-model="Auth.password" id="password" class="formInput" style="padding-right: 2.5rem;" placeholder="password">
                    <button type="button" class="absolute top-[9px] right-2.5" @click="togglePass">
                        <EyeClose v-if="ispasswordHidden" />
                        <EyeOpen v-if="!ispasswordHidden" />
                    </button>
                </div>
            </div>

            <div class="mb-5 mt-10">
                <Submit width="w-full">
                    Login
                </Submit>
            </div>
        </form>

        <div class="border-t py-6 border-slate-200 dark:border-primaryDarkBorder">
            <Link href="/forgot" class="py-4 pb-3 hover:text-green-600 hover:underline w-fit">Forgot password</Link>
            <div class="mb-2"></div>
            <div class="pb-4 w-fit">Don't have account ?
                <Link href="/register" class="hover:text-green-600 hover:underline">Pre register</Link>
            </div>
        </div>

    </div>
</template>

<script setup>
import Submit from '../Buttons/Submit.vue'
import EyeOpen from '../Icons/eyeOpenIcon.vue'
import EyeClose from '../Icons/eyeCloseIcon.vue'
import SuccesReg from './RegistrationConfirmation.vue'
import { ref } from '@vue/reactivity'
import { onMounted, watch } from '@vue/runtime-core'
import { useForm } from '@inertiajs/inertia-vue3'
import axios from 'axios'

const ispasswordHidden = ref(true)
const pass = ref(null)
const Auth = useForm({
    username: null,
    password: null,
})
const successReg = ref(false)

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

const submit = () => {
    Auth.post('/login', {
        onSuccess: page => {
            //console.log(page.props.flash.success)

            page.props.flash.error == 'invalid' ?
                Auth.errors.password = 'Invalid password':'';

            if(page.props.flash.success){
                window.location.href = '/verify_user'
            }
        },
        onError: error => {
            //console.log(error)
        }
    })
}

const succesReg = success => {
    if(success){
        successReg.value = true

        setTimeout(()=>{
            successReg.value = false
        }, 3000)
    }
}

</script>