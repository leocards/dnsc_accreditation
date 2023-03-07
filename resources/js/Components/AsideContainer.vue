<template>
    <div class="w-[22rem] fixed right-2 ml-2 shrink-0 subnavBg rounded-md shadow-md overflow-hidden flex flex-col asideContainer"
    :class="[top ? top: 'top-[7.3rem] h-[calc(100vh-7.9rem)]']">
        <slot />
    </div>
</template>
<script setup>
defineProps({top:String})
</script>