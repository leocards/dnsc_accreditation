<template>
    <div class="mx-auto z-30 lg:ml-[] min-h-[20rem] md:w-[30rem] max-sm:w-[26rem] subnavBg rounded-lg py-6 px-8 my-8 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative">
        <div class="w-full text-center pb-2 text-lg">
            <span class="font-semibold text-dnscGreen">Pre registration </span>
        </div>
        <SuccesReg v-if="successReg" />
        <form @submit.prevent="submit">
            <div class=" mt-1">
                <label for="first_name">First name</label>
                <span class="errorMessage ml-2 inter-font" v-if="Register.errors.first_name">{{Register.errors.first_name}}</span>
                <input type="text" class="formInput mt-2" placeholder="first name" v-model="Register.first_name">
            </div>
            <div class=" mt-3">
                <label for="last_name">Last name</label>
                <span class="errorMessage ml-2 inter-font" v-if="Register.errors.last_name">{{Register.errors.last_name}}</span>
                <input type="text" class="formInput mt-2" placeholder="last name" v-model="Register.last_name">
            </div>
            <div class=" mt-3">
                <label for="username">Username</label>
                <span class="errorMessage ml-2 inter-font" v-if="Register.errors.username">{{Register.errors.username}}</span>
                <input type="text" class="formInput mt-2" placeholder="username" v-model="Register.username">
            </div>
            <div class=" mt-3 ">
                <label for="password">Password</label>
                <span class="errorMessage ml-2 inter-font" v-if="Register.errors.password">{{Register.errors.password}}</span>
                <div class="relative mt-2">
                    <input type="password" ref="pass" v-model="Register.password" id="password" class="formInput" style="padding-right: 2.5rem;" placeholder="password">
                    <button type="button" class="absolute top-[9px] right-2.5" @click="togglePass">
                        <EyeClose v-if="ispasswordHidden" />
                        <EyeOpen v-if="!ispasswordHidden" />
                    </button>
                </div>
            </div>
            <div class=" mt-3 ">
                <label for="cpassword">Confirm password</label>
                <span class="errorMessage ml-2 inter-font" v-if="Register.errors.confirm_password">{{Register.errors.confirm_password}}</span>
                <div class="relative mt-2">
                    <input type="password" ref="cpass" v-model="Register.confirm_password" id="cpassword" class="formInput" style="padding-right: 2.5rem;" placeholder="confirm password">
                    <button type="button" class="absolute top-[9px] right-2.5" @click="toggleCPass">
                        <EyeClose v-if="isCpasswordHidden" />
                        <EyeOpen v-if="!isCpasswordHidden" />
                    </button>
                </div>
            </div>

            <div class="mb-5 mt-10">
                <Submit width="w-full">
                    Register
                </Submit>
            </div>
        </form>
        <div class="border-t pt-6 border-slate-200 dark:border-primaryDarkBorder">
            <Link href="/forgot" class="py-4 pb-3 hover:text-green-600 hover:underline w-fit">Forgot password</Link>
            <div class="mb-2"></div>
            <Link href="/index" class="pb-4 w-fit">Already have account ?
                <span class="hover:text-green-600 hover:underline">Login</span>
            </Link>
        </div>
    </div>
</template>

<script setup>
import Submit from '../Buttons/Submit.vue'
import EyeOpen from '../Icons/eyeOpenIcon.vue'
import EyeClose from '../Icons/eyeCloseIcon.vue'
import SuccesReg from './RegistrationConfirmation.vue'
import { useForm } from '@inertiajs/inertia-vue3'
import { ref } from '@vue/reactivity'
import { Inertia } from '@inertiajs/inertia'

const ispasswordHidden = ref(true)
const isCpasswordHidden = ref(true)
const pass = ref(null)
const cpass = ref(null)
const Register = useForm({
    first_name: null,
    last_name: null,
    username: null,
    password: null,
    confirm_password: null,
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

const succesRegistration = success => {
    if(success){
        successReg.value = true

        setTimeout(()=>{
            successReg.value = false
        }, 5000)
    }
}

const submit = () => {
    Register.post('/pre-register', {
        onSuccess: page => {
            //console.table()
            if(page.props.flash.success){
                Register.reset()
                Register.clearErrors()
                //Inertia.get('/index')
                succesRegistration(true)
            }
        },
        onError: err => {
            console.table(err)
        }
    })
}

</script>