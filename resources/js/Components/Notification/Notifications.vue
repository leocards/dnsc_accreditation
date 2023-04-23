<template>
    <Teleport to="body">
        <div class="w-fit h-[98vh] fixed top-1.5 right-2 max-sm_378:right-0 z-[10000] shadow-xl rounded-xl">
            <div class="w-[22.5rem] relative h-full subnavBg flex flex-col dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]
            border border-slate-200 dark:border-primaryDarkBorder/20 rounded-xl overflow-hidden">
                <div class="text-xl p-1 flex justify-between items-center shrink-0">
                    <span class="inter-font p-1">Notifications</span>
                    <span>
                        <Close @click="$emit('handleClose')" />
                    </span>
                </div>

                <button class="hover:text-dnscGreen hover:bg-dnscGreen/20 transition_300 rounded p-px px-1 w-fit ml-2
                hover:dark:bg-dnscGreen hover:dark:text-white"
                @click="markAsRead(null, null, false)" v-if="storeChat.notifications.length > 0">Mark all as read</button>

                <div class="overflow-y-auto px-2 pb-2 overscroll-contain">
                    <Card
                        v-for="(notif, index) in storeChat.notifications"
                        :notif="notif"
                        @handleMark="markAsRead(notif.id, index, notif.seen)"
                    />
                </div>


            </div>
        </div>
    </Teleport>
</template>

<script setup>
import Card from './Card.vue'
import Close from '../Buttons/XButton.vue'
import { useChatStore } from '../../Store/storeChatNotif'
import axios from 'axios'

const storeChat = useChatStore()

const emits = defineEmits(['handleClose'])

const markAsRead = (id = false, index = null, seen = false) => {

    try {
        if(!seen && id || !seen && !id)
            axios.post('/notification/mark_as_read',{
                id: id,
            }).then(res => {
                if(!id){
                    storeChat.notifications.forEach(notif => {
                        notif.seen = 1
                    });

                    storeChat.notifCounter = 0
                }else{
                    storeChat.notifications[index].seen = 1
                    storeChat.notifCounter--
                }
            })
    } catch (e) {
        //console.log(e)
    }

}

</script>