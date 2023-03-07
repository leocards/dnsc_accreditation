<template>
    <AsideContainer :top="top">
        <div class="bg-inherit sticky top-0 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] flex items-center p-1">
            <div class="w-full ">
                <slot name="head"/>
            </div>

            <CloseButton @click="$emit('handleClose')" />
        </div>

        <div class="flex flex-col overflow-y-auto grow">
            <slot />
        </div>

        <slot name="input"/>
        
    </AsideContainer>
</template>

<script setup>
import AsideContainer from './AsideContainer.vue'
import CloseButton from './Buttons/XButton.vue'

defineProps({top:String})
defineEmits(['handleClose'])

</script>