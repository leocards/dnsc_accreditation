<template>
    <div class="w-full subnavBg rounded-md h-fit overflow-hidden mb-0.5"
    :class="[!seen && !search && sender !== $page.props.user.userId? 'dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]' : 'hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]']">
        <div class="w-full h-full flex  p-1 transition_300"
        :class="[!seen && !search && sender !== $page.props.user.userId? 'bg-dnscGreen/10 dark:bg-dnscGreen/20' : 'hover:bg-black/5 hover:dark:bg-white/10']">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    seen: Number,
    sender: Number,
    search: Boolean
})

</script>