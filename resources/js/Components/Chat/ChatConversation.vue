<template>
    <Teleport to="body"> 

        <div class="fixed bottom-0 right-5 flex justify-end">
            <div class="ml-2 rounded-t-lg dark:myshadow">
                <div class="w-[21rem] bg-white myshadow h-[29rem] rounded-t-lg overflow-hidden flex flex-col subnavBg dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative">
                    <div class="absolute rounded subnavBg top-12 self-center overflow-hidden" v-if="!sentStatus.sent">
                        <div class="bg-blue-600/30 p-1.5">
                            Sending
                        </div>
                    </div>
                    <div class="absolute rounded subnavBg top-12 self-center overflow-hidden" v-if="!sentStatus.error">
                        <div class="bg-red-600/30 p-1.5">
                            Error sending message
                        </div>
                    </div>

                    <ChatHeader :user="storeChat.selectedUserChat" @handleClose="storeChat.closeUserConversation" />

                    <MessageContainer>
                        <template #load>
                            <div class="flex justify-center py-2" v-if="storeChat.loaded">
                                <Loading />
                            </div>

                            <div class="flex justify-center mb-2" v-if="!storeChat.loaded && !storeChat.lastConversation">
                                <button class="hover:bg-dnscGreen rounded p-1.5 transition_300 hover:text-white" @click="storeChat.getMessages">Load more</button>
                            </div>
                        </template>
                        
                        <div 
                            class="w-full px-2 flex" :class="[checkNextEqual(mess, index) && checkPrevEqual(mess, index)?'pb-0.5':checkNextEqual(mess, index)?'':'pb-0.5']" 
                            v-for="(mess, index) in storeChat.conversation"
                        >
                            <Reciever
                                v-if="mess.sender != $page.props.user.userId"
                                :message="mess"
                                :style="checkNextEqual(mess, index) && checkPrevEqual(mess, index) ? 'rounded-l-md' : checkNextEqual(mess, index)?'rounded-tl-md rounded-bl-2xl':
                                        checkPrevEqual(mess, index)?'rounded-bl-md rounded-tl-2xl':'rounded-l-2xl'"
                            />
                            <Sender
                                v-if="mess.sender == $page.props.user.userId"
                                :message="mess"
                                :style="checkNextEqual(mess, index) && checkPrevEqual(mess, index) ? 'rounded-r-md' : checkNextEqual(mess, index)?'rounded-tr-md rounded-br-2xl':
                                        checkPrevEqual(mess, index)?'rounded-br-md rounded-tr-2xl':'rounded-r-2xl'"
                            />
                        </div>
                    </MessageContainer>

                    <div class="w-full min-h-[3.1rem] max-h-[9rem] p-2 shrink-0 border-t dark:border-primaryDarkBorder border-slate-300 relative">
                        <InputMessage 
                            @handleMesage="message"
                        />
                    </div>
                </div>
            </div>
        </div>

    </Teleport>
</template>

<script setup>
import Loading from '../Loading.vue'
import Sender from './components/Sender.vue'
import Reciever from './components/Reciever.vue'
import InputMessage from '../ContentEditable.vue'
import ChatHeader from './components/ChatHeader.vue'
import MessageContainer from './components/MessageContainer.vue'
import axios from 'axios'
import { useChatStore } from '../../Store/storeChatNotif'
import { ref } from '@vue/reactivity'

const storeChat = useChatStore();

const props = defineProps({
    user: Number
})
const sentStatus = ref({
    sent: true,
    error: true
})

function checkNextEqual(item, index) {
    return storeChat.conversation[index+1] && item.sender == storeChat.conversation[index+1].sender;
}
function checkPrevEqual(item, index) {
    return index !== 0 && storeChat.conversation[index-1].sender == item.sender;
}

const message = mess => {
    if(mess == '' || !mess) ''
    else
        if(mess.trim() != ''){
            sentStatus.value.sent = false
            axios.post('/chat/send', {
                user: storeChat.selectedUserChat.id,
                message: mess
            }).then(res => {
                let data = res.data
                storeChat.conversation.unshift({...data})
                'convoId' in storeChat.selectedUserChat ? '' : storeChat.selectedUserChat.convoId = data.convoId
                
                let isExist = storeChat.chatConversation.find((val, index) => {
                    if(val.convoId === data.convoId)
                    {
                        storeChat.chatConversation[index].seen = data.seen
                        storeChat.chatConversation[index].sender = data.sender
                        storeChat.chatConversation[index].message = data.message
                        storeChat.chatConversation[index].created_at = data.created_at

                        storeChat.chatConversation.unshift(storeChat.chatConversation.splice(index, 1)[0])
                        return val
                        //console.log(storeChat.chatConversation.unshift(storeChat.chatConversation.splice(index, 1)[0]))
                    }
                })

                if(!isExist){
                    storeChat.chatConversation.unshift(res.data)
                }
                
                sentStatus.value.sent = true
            }).catch(e => {
                sentStatus.value.error = false
                console.log(e)
                setTimeout(() => {
                    sentStatus.value.error = true
                    sentStatus.value.sent = true
                }, 2500);
            })
        }
}
</script>