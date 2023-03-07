<template>
    <Teleport to='body' v-if="storeDocument.attachDocument && !storeDocument.attachDocumentMinimize">
        <div class="rounded fixed w-[18rem] bg-white dark:bg-tertiaryDarkBg bottom-6 left-7 z-[100] p-2 flex flex-col shadow-md actionBtn" v-if="!storeDocument.processAttachment">
            <div class="transition_300 w-fit ml-auto px-1 rounded hover:bg-dnscGreen hover:text-white cursor-pointer actionBtn" title="Minimize"
            @click="storeDocument.attachDocumentMinimize = true">
                <Minimize/>
            </div>

            <div class="mb-8 mt-3">
                Select location to attach document
                <div class="italic mt-1.5"> {{storeDocument.attachDocument.title}}</div>
            </div>
            <div class="ml-auto">
                <Cancel @click="removeAttachmet" />
            </div>
        </div>

        <div v-else class="rounded fixed w-[18rem] bg-white dark:bg-tertiaryDarkBg bottom-6 left-7 z-[100] p-2 py-4 flex flex-col">
            Attaching document...
        </div>
    </Teleport>
</template>
<script setup>
import Cancel from './../Buttons/Cancel.vue';
import Minimize from '../Icons/removeIcon.vue'
import { useDocumentStore } from "../../Store/storeDocument";

const storeDocument = useDocumentStore()

const removeAttachmet = () => {
    window.onbeforeunload = null
    storeDocument.attachDocument = null
}

</script>