<template>
    <Toast>
        <div class="w-full bg-dnscGreen/20 rounded p-2 flex items-center min-h-[4rem]">
            <div class=" w-[calc(100%-2.6rem)] grow pr-2 text-dnscGreen">
                <div>

                </div>
                <div class="break-words whitespace-pre-wrap">
                    {{$page.props.flash.success}}
                </div>
            </div>
            
            <button
            type="button"
            class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center hover_green actionBtn"
            @click="hideToast($page.props.flash)">
                <XIcon />
            </button>

            <input type="hidden" :value="toasted($page.props.flash)">
        </div>
    </Toast>
</template>
<script setup>
import Toast from '../Toast.vue'
import XIcon from '../Icons/closeIcon.vue'
import { ref } from '@vue/reactivity'

const toasted = val => {
    val.error ? val.error = null : ''
    setTimeout(()=> {
        val.success = null
    }, 5000)
}

const hideToast = (flash) => {
    flash.success = null
}

</script>