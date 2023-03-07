<template>
    <div class="absolute top-0 left-0 bg-black/50 w-full h-full flex pt-28 justify-center analytics">
        
        <div class="rounded-md bg-white dark:bg-tertiaryDarkBg w-[19rem] items-center h-[10rem] p-2.5 flex flex-col">

            <div class="overflow-y-auto py-2">
                {{errorMsg}}
            </div>

            <div class="flex justify-end mt-auto w-full pt-2">
                <button class="p-4 py-1.5 bg-dnscGreen/90 hover:bg-dnscGreen text-white/80 rounded transition_300" @click="$emit('handleClose')">Close</button>
            </div>
        </div>

    </div>
</template>

<script setup>

const props = defineProps({
    errorMsg: String
})
const emit = defineEmits(['handleClose'])

</script>