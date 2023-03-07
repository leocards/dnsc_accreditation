<template>
    <div class="h-12 shrink-0 shadow dark:border-b dark:border-gray-100/5">
        <div class="w-full h-full flex items-center px-1">

            <div class="w-8 h-8 shrink-0 rounded-full overflow-hidden">
                <Avatar :avatar="user.avatar" />
            </div>

            <div class="Oneline ml-2">
                {{user.name}}
            </div>

            <button 
                class="ml-auto w-8 h-8 shrink-0 rounded-full flex items-center justify-center 
                    hover:bg-black/10 hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] 
                    transition_300 hover:dark:bg-white/10"
                    @click="$emit('handleClose')"
            >
                <CloseIcon />
            </button>

        </div>
    </div>
</template>
<script setup>
import Avatar from '../../Avatar.vue'
import CloseIcon from '../../Icons/closeIcon.vue'

const props = defineProps({
    user: Object
})
const emits = defineEmits([
    'handleClose'
]);

</script>
