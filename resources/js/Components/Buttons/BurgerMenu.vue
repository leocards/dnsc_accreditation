<template>
    <button type="button" class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center transition duration-500 border border-transparent
    hover:border-dnscGreen/50 hover:text-dnscGreen actionBtn hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
    :class="[storeLayout.toggleMenu?'bg-dnscGreen/10 text-dnscGreen':'']">
        <BurgerMenuIcon />
    </button>
</template>
<script setup>
import { useLayoutStore } from '../../Store/storeLayout'
import BurgerMenuIcon from '../Icons/menu.vue'

const storeLayout = useLayoutStore()
</script>