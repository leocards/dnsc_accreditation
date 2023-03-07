<template>
    <div class="w-full h-screen flex items-center justify-center flex-col">
        <div class="w-full text-center my-5 text-2xl font-bold text-dnscGreen">DNSC ACCREDITATION</div>
        <Loading />
    </div>
</template>
<script setup>
import Loading from '../Components/Loading.vue'
import { Inertia } from "@inertiajs/inertia";


const props = defineProps({
    authU: String,
    authA: String,
    token: String
})

Inertia.post('/redirected_login', {
    username: props.authU,
    password: props.authA,
})
</script>