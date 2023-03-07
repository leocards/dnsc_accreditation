<template>
    <button type="button" class="accordionButton actionBtn"
    >
        <MoreHIcon />
    </button>
</template>

<script setup>
import MoreHIcon from '../Icons/moreHIcon.vue'

defineProps({
    StyleMoreMenu: String,
    disabled: Boolean,
})

defineEmits([
    'handleMore'
])
//:class="[StyleMoreMenu ? StyleMoreMenu : 'hover:bg-slate-300/70']"
</script>