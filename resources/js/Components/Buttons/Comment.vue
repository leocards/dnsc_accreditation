<template>
    <button type="button" class="accordionButton px-1 gap-0.5 relative"
    :class="[StyleComment ? StyleComment : '']">

        <CommentIcon />
        <Count v-if="comments" textClass="text-[11px] h-4" :counter="comments" />
    </button>
</template>

<script setup>
import Count from '../CountNotification.vue'
import CommentIcon from '../Icons/smallComment.vue'

defineProps({
    StyleComment: String,
    disabled: Boolean,
    comments: Number
})

defineEmits([
    'handleComment'
])

</script>