<template>
    <div class="dark:bg-tertiaryDarkBg/40 bg-whiteBg w-full rounded-md p-2">
        <div class="flex items-center gap-0.5">
            <div class="w-6 h-6 overflow-hidden rounded-full">
                <Avatar :avatar="comment.user.avatar" />
            </div>
            <div class="ml-2 mr-1" v-if="comment.userId == $page.props.user.userId">You</div>
            <div class="ml-2 mr-1" v-else>{{comment.user.first_name+' '+comment.user.last_name}}</div>
            <span class="text-xs flex gap-1"> &#8226; <TimePass :time="comment.created_at" /></span>
        </div>

        <div class="inter-font mt-1.5 pl-[2.1rem] whitespace-pre-wrap break-words">
            {{comment.comment}}
        </div>
    </div>
</template>

<script setup>
import Avatar from './Avatar.vue'
import TimePass from './TimePass.vue'

const props = defineProps({comment: Object})
</script>