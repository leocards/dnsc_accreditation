<template>
    <div class="max-w-[14rem] py-1.5 px-3 h-fit bg-gray-200/60 dark:bg-tertiaryDarkBg whitespace-pre-wrap break-words mr-auto inter-font rounded-r-2xl"
        :class="style"
    >
        {{message.message}}
    </div>
</template>

<script setup>

const props = defineProps({
    style: String,
    message: Object
})

</script>