<template>
    <div class="p-1 cursor-default mb-1.5 flex gap-1 items-center justify-between">
        <div>
            <div class="Oneline max-w-[24rem]">{{version.title}}</div>
            <div class="text-xs mt-1 Oneline max-w-[21rem] secondaryText">
                <Date :time="version.updated_at" />
                <span class="ml-2" title="lorem">{{version.user.first_name+' '+version.user.last_name}}</span>
            </div>
        </div>
        <div class="flex items-center h-fit relative">
            <button class="w-8 h-8 rounded hover:bg-black/10 dark:hover:bg-white/10 flex items-center justify-center transition_300 moreMenuVersion"
            title="More"
            @click="$emit('handleVersionMore', version)">
                <More />
            </button>
        </div>
    </div>
</template>

<script setup>
import Date from '../../DateTime.vue'
import More from '../../Icons/moreVIcon.vue'

const props = defineProps({version: Object})

defineEmits(['handleVersionMore'])

</script>