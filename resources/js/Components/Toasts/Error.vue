<template>
    <Toast>
        <div class="w-full bg-red-300/30 rounded p-2 flex items-center min-h-[4rem]">
            <div class=" w-[calc(100%-2.6rem)] grow pr-2 text-red-600">
                <div>

                </div>
                <div class="break-words whitespace-pre-wrap">
                    {{$page.props.flash.error}}
                </div>
            </div>
            
            <button 
            type="button" 
            class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center bg-red-200 text-red-600 actionBtn"
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

const toasted = flash => {
    flash.success ? flash.success = null : ''
    setTimeout(()=> {
        flash.error = null
    }, 5000)
}

const hideToast = (flash) => {
    flash.error = null
}

</script>