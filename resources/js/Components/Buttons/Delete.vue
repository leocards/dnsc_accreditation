<template>
    <button type="button" class="rounded-md flex items-center justify-center disabled:button-disabled 
    bg-red-500 hover:bg-red-600 text-white hover:shadow-md
    dark:bg-red-800/80 hover:dark:bg-red-800 transition_300 h-10 min-w-[7rem] px-2.5 actionBtn
     dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]">
        <slot>
            <div>Delete</div>
        </slot>
    </button>
</template>

<script setup>

defineEmits(['handleCreate'])
</script>