<template>
    <Teleport to="body">
        
        <div class="w-fit h-[98vh] fixed top-1.5 right-2 max-sm_378:right-0 z-[10000] shadow-xl rounded-xl">
            <div class="w-[22.5rem] relative h-full subnavBg flex flex-col dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]
            border border-slate-200 dark:border-primaryDarkBorder/20 rounded-xl overflow-hidden">
                <div class="text-xl p-1 flex justify-between items-center">
                    <span class="inter-font p-1">Messages</span>
                    <span>
                        <Close @click="$emit('handleClose')" />
                    </span>
                </div>
                <div class="px-2 pb-1.5 shadow-sm z-20">
                    <Search 
                        v-model="modelValue"
                        placeholder="Search user"
                    />
                </div>

                <div class="w-full grow overflow-y-auto" v-if="!modelValue">
                    <div class="text-center text-gray-500 py-3 select-none" v-if="myMethod.ifArray(storeChat.chatConversation, '==', 0)">
                        Empty messages
                    </div>

                    <div class=" flex justify-center py-4" v-if="!storeChat.chatConversation">
                        <Loading />
                    </div>

                    <div class="w-full p-1.5 pb-1">
                        <Card v-for="user in storeChat.chatConversation" @click="storeChat.getUserConversation(user, $page.props.user.userId)" :sender="user.sender" :seen="user.seen">
                            <div class="w-14 h-14 shrink-0 rounded-full overflow-hidden bg-gray-400/30">
                                <Avatar :avatar="user.avatar" />
                            </div>
                            <div class="ml-2 p-1 flex flex-col justify-center grow select-none">
                                <div :class="{'font-semibold text-dnscGreen':(!user.seen && user.sender !== $page.props.user.userId)}">{{user.name}}</div>
                                <div class="text-xs mt-1 flex items-center gap-0.5">
                                    <span 
                                        class="inter-font text-gray-400 dark:text-white/40 Oneline max-w-[14rem]" 
                                        v-if="user.sender == $page.props.user.userId"
                                    >
                                        You: <span class="">{{user.message}}</span> 
                                    </span>
                                    <span 
                                        :class="[!user.seen?'text-dnscGreen':'text-gray-400 dark:text-white/40']"
                                        class="inter-font  Oneline max-w-[14rem]" 
                                        v-else
                                    >
                                        {{user.message}}
                                    </span>
                                    <span class="flex gap-1"> &#183; <TimeAgo :time="user.created_at" :mmDD="true" /></span>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>

                <div class="w-full grow overflow-y-auto" v-else>

                    <div class="py-10 flex justify-center" v-if="!searchResult">
                        <Loading />
                    </div>
                    
                    <div class="w-full p-1.5 pb-1">
                        <Card v-for="user in searchResult" @click="storeChat.getUserConversation(user, $page.props.user.userId)" :seen="1" :search="true">
                            <div class="w-14 h-14 shrink-0 rounded-full overflow-hidden bg-gray-400/30">
                                <Avatar :avatar="user.avatar" />
                            </div>  
                            <div class="ml-2 p-1 flex flex-col justify-center grow select-none">
                                <div class="">{{user.name}}</div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

    </Teleport>
</template>

<script setup>
import Card from './ChatCard.vue'
import Search from '../Search.vue'
import Avatar from '../Avatar.vue'
import Loading from '../Loading.vue'
import TimeAgo from '../TimePass.vue'
import Close from '../Buttons/XButton.vue'
import axios from 'axios'
import { throttle } from 'lodash'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import myMethod from '../../Store/Methods'
import { useChatStore } from '../../Store/storeChatNotif'

const storeChat = useChatStore()

const emits = defineEmits(['handleClose'])

const modelValue = ref('') //value for search
const searchResult = ref(null)

watch(modelValue, throttle((search) => {
    if(search == '' || !search) searchResult.value = null
    else
        if(search.trim() != '')
        {
            axios.get(`/chat/search/${search}`)
            .then(res => {
                searchResult.value = res.data
            })
        }
}, 900))


</script>