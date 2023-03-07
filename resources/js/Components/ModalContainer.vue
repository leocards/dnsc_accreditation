<template>
    <teleport to='body'>
        <div class="fixed top-0 w-full h-full bg-black/40 dark:bg-black/60 py-8 modal z-[9999] overflow-y-auto" @contextmenu.prevent>
            <slot />
        </div>
    </teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useLayoutStore } from '../Store/storeLayout'

const storeLayout = useLayoutStore()

onMounted(() => {
    document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
    if(!storeLayout.keepScrollHidden)
        document.body.style.overflow = ''
})

</script>