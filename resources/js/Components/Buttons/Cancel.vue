<template>
    <button type="button" class="rounded-md flex items-center justify-center disabled:button-disabled hover:bg-slate-300 bg-slate-200 hover:shadow-md transition_300
    border-slate-400 dark:bg-white/10 hover:dark:bg-white/20 h-10 min-w-[7rem] px-2.5 shrink-0 actionBtn dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
    :class="width">
        <slot>
            <div>Cancel</div>
        </slot>
    </button>
</template>

<script setup>
defineProps({
    width: String
})

</script>