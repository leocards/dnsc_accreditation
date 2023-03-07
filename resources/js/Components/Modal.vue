<template>
    <Container>
        
        <div class="mx-auto min-w-[30rem] max-w-fit min-h-[20vh] rounded-md bg-white dark:bg-secondaryDarkBg slate-800 myshadow relative p-2
        dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] max-sm_378:min-w-[20rem]"
        id="modalRef"
        :class="width">
            <div class="absolute top-1.5 right-1.5">
                <CloseButton @click="$emit('handleClose')" :disabled="disableClose"/>
            </div>
            <div class="pt-4 text-lg">
                <slot name="header" />
            </div>

            <slot />
        </div>

    </Container>
</template>

<script setup>
import Container from './ModalContainer.vue'
import CloseButton from './Buttons/XButton.vue'
import { ref } from '@vue/reactivity'

defineProps({
    width: String,
    disableClose: Boolean
})
const emits = defineEmits(['handleClose'])



</script>