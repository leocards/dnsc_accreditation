<template>
    <div class="w-full px-3 mb-2">
        <div 
            class="py-1.5 px-2 transition_300 cursor-pointer
            rounded"
            :class="[$page.props.currentArea?$page.props.currentArea.id == area.id?'bg-dnscGreen text-white':classes:classes]"
            @click="$emit('handleClick')"
        >
            <div>{{area.title}} </div>
            <div>{{area.description}} </div>
        </div>
    </div>
</template>

<script setup>

const props = defineProps({
    area: Object
})
defineEmits(['handleClick'])

const classes = 'border border-slate-300 dark:border-primaryDarkBorder hover:border-dnscGreen/20 hover:bg-dnscGreen/20 hover:text-dnscGreen';

</script>