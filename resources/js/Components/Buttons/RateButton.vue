<template>
    <button type="button" class="accordionButton actionBtn">
        <RateIcon v-if="!rate && !disable" />
        <div class="w-full h-full leading-[10px] pt-[0.360rem] text-xs" v-else-if="rate">
            <div>{{rate}}</div>
            <div class="text-[8px]">Rate</div>
        </div>
    </button>
</template>

<script setup>
import RateIcon from '../Icons/starIcon.vue'

const props = defineProps({
    rate: String,
    disable: Boolean
})

// :class="[active ? 'hover:bg-dnscGreen/20' : (style?style:'hover:bg-black/10')]"
</script>