<template>
    <button type="button" class="action-btn disabled:action-btn-disabled actionBtn hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative group">
        <div class="-mt-0.5 rotate-180">
            <BackIcon />
        </div>
        <!-- <span class="px-2 py-1.5 bg-white dark:bg-tertiaryDarkBg dark:border-primaryDarkBorder border border-slate-300 rounded 
        whitespace-nowrap text-xs top-8 group-hover:block hidden right-8 absolute">Open</span> -->
    </button>
</template>

<script setup>
import BackIcon from '../Icons/backIcon.vue'
</script>