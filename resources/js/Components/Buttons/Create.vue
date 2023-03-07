<template>
    <button type="button" class="rounded-md flex items-center justify-center disabled:button-disabled button-green h-10 min-w-[7rem] px-2.5 actionBtn
     dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] max-sm:min-w-fit">
        <slot>
            <div>Create</div>
        </slot>
    </button>
</template>

<script setup>

defineEmits(['handleCreate'])
</script>