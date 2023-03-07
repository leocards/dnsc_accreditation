<template>
    <div class="max-w-[14rem] py-1.5 px-3 h-fit bg-dnscGreen ml-auto text-white whitespace-pre-wrap break-words inter-font rounded-l-2xl"
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