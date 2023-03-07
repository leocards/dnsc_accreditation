<template>

    <div class="w-full h-fit ContentEditble">
        <div class="flex gap-1 relative">
           
            <Contenteditable 
                tag="p" 
                class="w-full min-h-[2.25rem] max-h-[7rem] overflow-y-auto p-2 outline-none whitespace-pre-wrap break-words
                rounded-md root" 
                :contenteditable="isEditable"
                v-model="messageInput"
                :no-html="true"
                :no-nl="false"
                @keypress="submitEnter"
                @returned="submitEnter"
            />

            <div class="absolute top-2 left-2 pointer-events-none select-none dark:text-white/40" v-show="!messageInput"><span v-if="placeholder">{{placeholder}}</span><span v-else>Send message</span></div>

            <button @click="sendButton"
            class="w-9 h-9 self-end hover:bg-gray-200 dark:hover:bg-tertiaryDarkBg shrink-0 pt-px text-dnscGreen flex items-center justify-center rounded-full">
                <i><SendIcon /></i>
            </button>
        </div>
    </div>

</template>

<script setup>
import SendIcon from './Icons/sendIcon.vue'
import Contenteditable from 'vue-contenteditable';
import { ref } from 'vue';


const props = defineProps({
    placeholder: String
})

const emits = defineEmits(['submitButton', 'handleMesage'])

const isEditable = ref(true)

const messageInput = ref(null)

const sendButton = () => {
    if(messageInput.value != "")
    {
        emits('handleMesage', messageInput.value)
        messageInput.value = null
    }
}

function submitEnter(e){
    if(!e.shiftKey && e.keyCode === 13){
        e.preventDefault();
        emits('handleMesage', messageInput.value)
        messageInput.value = null
    }
}

</script> 
