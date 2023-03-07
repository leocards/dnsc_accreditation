<template>
    <div class="w-full min-h-[4rem] rounded-md p-2 transition_300 relative mt-0.5"
    :class="[!notif.seen?'dark:bg-dnscGreen/20 bg-dnscGreen/10':'hover:dark:bg-tertiaryDarkBg hover:bg-gray-300/40']">
        <div class="Twoline" v-if="notif.action == 'due'">Due task</div>
        <div class="Twoline" :class="{'font-semibold text-dnscGreen':!notif.seen}" v-else>{{notif.name.name}}</div>

        <div 
            class="inter-font mt-1 text-[13.5px]"
            :class="[viewMore?'':'Twoline']"
            v-if="['upload', 'review', 'due'].includes(notif.action)"
        >
            {{notif.details}}
        </div>

        <div 
            class="inter-font mt-1 text-[13.5px]"
            :class="[viewMore?'':'Twoline']"
            v-if="notif.action == 'commented' && notif.documentId"
        >
            Commented on a document <span v-if="notif.isOwner">you uploaded</span> named <i>{{notif.details}}</i> 
        </div>

        <div class="mt-1 text-xs flex items-center justify-between">
            <Time :time="notif.created_at" />
            <div>
                <button 
                    class="hover:text-dnscGreen hover:bg-dnscGreen/20 transition_300 rounded p-px px-1
                    hover:dark:bg-dnscGreen hover:dark:text-white mr-2" 
                    @click="viewMore = !viewMore"
                    v-if="(notif.details.length > 64)"
                >View more</button>
                <button class="hover:text-dnscGreen hover:bg-dnscGreen/20 transition_300 rounded p-px px-1
                hover:dark:bg-dnscGreen hover:dark:text-white" @click="$emit('handleMark')">Mark as read</button>
            </div>
        </div>

        <div class="w-3 h-3 rounded-full bg-dnscGreen absolute shrink-0 top-1 right-1" v-if="!notif.seen"></div>
    </div>
</template>

<script setup>
import { ref } from '@vue/reactivity'
import Time from '../TimePass.vue'

const props = defineProps({
    notif: Object
})
const emits = defineEmits([
    'handleMark'
])

const viewMore = ref(false)

</script>