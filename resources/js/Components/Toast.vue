<template>
    <Teleport to="body">
        <div class="fixed bottom-6 left-7 w-[21rem] min-h-[4rem]  max-h-[10rem] rounded bg-white shadow-md z-[99999]">
            <slot />
        </div>
    </Teleport>
</template>

<script setup>

defineProps({

})

</script>