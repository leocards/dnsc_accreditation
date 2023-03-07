<template>
    <img 
        :src="avatar ? '/storage/avatar/'+avatar : '/storage/avatar/default.png' " 
        alt="avatar" 
        onerror="this.onerror=null; this.src='/storage/avatar/default.png'" 
        class="object-cover w-full"
        :class="{'h-full':imageLoaded}"
        @load="imageLoaded = true"
    />

    <div class="w-full h-full bg-slate-200 dark:bg-white/40 animate-pulse" v-if="!imageLoaded"></div>
</template>

<script setup>
import { ref } from "@vue/reactivity"

defineProps({
    avatar: String
})

const imageLoaded = ref(false)

</script>