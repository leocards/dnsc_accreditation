<template>
    <div class="w-full flex mt-3">
        <div class="w-10 h-10 shrink-0 rounded-md overflow-hidden">
            <Avatar :avatar="announce.avatar" />
        </div>

        <div class="w-full bg-slate-200/40 dark:bg-white/10 ml-3 p-2 rounded-md">

            <div class="flex justify-between">
                <div class="flex h-fit items-center">
                    {{announce.name}}
                    <span class="text-xs text-slate-500 dark:text-white/80 ml-3"><Time :time="announce.time" /></span>
                </div>

                <div class="px-2 flex items-center shrink-0 gap-2" v-if="announce.name == 'You'">
                    <EditButton title="Edit" @click="$emit('handleEdit')"/>
                    <button title="Delete" class="p-2.5 hover:bg-red-500/20 dark:hover:bg-red-500/50 hover:text-red-500
                    rounded-full dark:hover:text-white transition_300 hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
                    @click="$emit('handleRemove')">
                        <RemoveIcon />
                    </button>
                </div>
            </div>

            <div class="font-semibold pb-4 text-base">{{announce.title}}</div>

            <div class="whitespace-pre-wrap break-words">{{announce.message}}</div>
        </div>
    </div>
</template>

<script setup>
import Avatar from '../Avatar.vue'
import Time from '../TimePass.vue'
import EditButton from '../Buttons/Edit.vue'
import RemoveIcon from '../Icons/deleteIcon.vue';

const props = defineProps({
    announce: Object
})
const emits = defineEmits([
    'handleEdit',
    'handleRemove',
    'handleRemove'
])

</script>