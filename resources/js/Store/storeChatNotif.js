import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useChatStore = defineStore('Chat', () => {

    const page = ref(1)
    const loaded = ref(false)
    const chatCounter = ref(0)
    const isOpenConvo = ref(false)
    const conversation = ref(new Array())
    const isOpenMessages = ref(false)
    const chatConversation = ref(null)
    const selectedUserChat = ref(null)
    const lastConversation = ref(false)
    const isOpenNotifications = ref(false)
    const send_recieve = ref(null)

    const getUserConversation =  (user, authUser) => {
        isOpenConvo.value = true
        isOpenMessages.value = false
        selectedUserChat.value = user
        lastConversation.value = false
        page.value = 1
        conversation.value = new Array()
        send_recieve.value = 'sender' in user ? (user.sender == authUser?user.reciever:user.sender) : user.id
        getMessages(user.id)
        try {
            axios.get('/chat/seen_msg?convoId='+selectedUserChat.value.convoId).then(res => {
                chatConversation.value.find(chat => {
                    if(chat.seen == null && chat.convoId == res.data && authUser !== chat.sender){
                        chatCounter.value--
                        chat.seen = 1
                    }
                })
            })
        } catch (e) {
            //console.log(e)
        }
    }

    const getMessages = async (user) => {
        loaded.value = true
        try {
            let res = await axios.get(`/chat/getMessages/${user}?page=${page.value}`)
            let message = await res.data

            if(message.length !== 0){
                if(message.length < 30)
                    lastConversation.value = true

                conversation.value.push(...message)
                page.value+=1
                loaded.value = false
            }else{
                loaded.value = false
                lastConversation.value = true
            }
        } catch (e) {
            //console.log(e)
        }
    }

    const closeUserConversation = () => {
        isOpenConvo.value = false
        selectedUserChat.value = null
    }

    //NOTIFICATION 
    const notifications = ref([])
    const notifCounter = ref(0)
    const notifPage = ref(1)
    const notifLoaded = ref(false)

    const getNotification = async () => {
        notifLoaded.value = true
        try {
            let res = await axios.get(`/notifications?page=${notifPage.value}`)
            let data = await res.data.notif

            notifCounter.value = await res.data.count

            if(data.length !== 0){
                notifications.value.push(...data)
                notifPage.value+=1
                notifLoaded.value = false
            } else {
                notifLoaded.value = false
            }
        } catch (e) {
            //console.log(e)
        }
    }

    return {
        page,
        loaded,
        isOpenConvo,
        chatCounter,
        send_recieve,
        conversation,
        isOpenMessages,
        chatConversation,
        lastConversation,
        selectedUserChat,
        isOpenNotifications,

        getMessages,
        getUserConversation,
        closeUserConversation,

        //notification
        notifPage,
        notifLoaded,
        notifCounter,
        notifications,

        getNotification,
    }
})