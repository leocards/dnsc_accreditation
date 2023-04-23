<template>
    <Head>
        <title>Accreditation</title>
    </Head>
    
    <Navigation 
        @handle-menu="storeLayout.toggleMenu = !storeLayout.toggleMenu"
        @handleNotification="storeChat.isOpenNotification = true"
        @contextmenu.prevent
        @handleProfile="isProfile = true"
    >
            <slot name="navSlot" />
    </Navigation>

    <SideNavigation :toggleMenu="storeLayout.toggleMenu"/>

    <div class="pt-14 min-h-[calc(100vh)] max-sm:ml-0 max-sm:w-full md:ml-16 md:w-[calc(100%-4rem)]"
    :class="[storeLayout.toggleMenu ? 'ml-16 w-[calc(100%-4rem)]' : 'ml-60 w-[calc(100%-15rem)] lg:ml-60 lg:w-[calc(100%-15rem)]']"
    @contextmenu.prevent>
        <slot name="subNav"/>
        <div class="px-2.5">
            <slot />
        </div>
    </div>

    <Chat 
        :user="$page.props.user.userId"
        v-if="storeChat.isOpenConvo && storeChat.selectedUserChat" 
    /><!-- @handleChat="storeChat.isOpenMessages = true" -->

    <ErrorToast v-if="$page.props.flash.error"/>

    <SuccessToast v-if="$page.props.flash.success"/>

    <Messages v-if="storeChat.isOpenMessages" @handleClose="storeChat.isOpenMessages = false" />

    <Notifications v-if="storeChat.isOpenNotification" @handleClose="storeChat.isOpenNotification = false"  />

    <RemoveDocument v-if="storeDocument.setDocuToRemove" />

    <Attach 
        
    />

    <Profile 
        :user="profile"
        v-if="isProfile"
        @handleClose="changeonUpdateProfile"
    />
    <!--  flex items-center -->
</template>

<script setup>
import Profile from './User/Profile.vue'
import Navigation from './Navigation.vue'
import Cancel from './Buttons/Cancel.vue'
import Messages from './Chat/Messages.vue'
import Attach from './Document/Attach.vue'
import Chat from './Chat/ChatConversation.vue'
import SideNavigation from './SideNavigation.vue'
import ErrorToast from '../Components/Toasts/Error.vue'
import RemoveDocument from './Document/RemoveDocument.vue'
import SuccessToast from '../Components/Toasts/Success.vue'
import Notifications from './Notification/Notifications.vue'
import axios from 'axios'
import { ref } from 'vue'
import { Head } from '@inertiajs/inertia-vue3'
import { useUserStore } from '../Store/storeUser'
import { useLayoutStore } from '../Store/storeLayout'
import { useChatStore } from '../Store/storeChatNotif'
import { useDocumentStore } from '../Store/storeDocument'

const storeUser = useUserStore()
const storeChat = useChatStore()
const storeLayout = useLayoutStore()
const storeDocument = useDocumentStore()

const props = defineProps({
    user: Object
})
const isProfile = ref(false)
const profile = ref(null)

//get the designations and authentications
storeUser.get_des_and_auth()

const getProfile = async (id) => {
    try {
        let user = await axios.get('/user/profile/'+id)
        profile.value = user.data

    } catch (e) {
        profile.value = 'error'
    }
}
getProfile(props.user.userId)

const changeonUpdateProfile = update => {
    if(update){
        getProfile(props.user.userId)
    }
    isProfile.value = false
}

try {
    axios.get('/chat')
    .then(res => {
        storeChat.chatCounter = res.data.count
        storeChat.chatConversation = res.data.chat
    })
    //.filter(({convoId}) => !chatConversation.some(chat => chat.convoId == convoId))
} catch (e) {

}

const removeAttachmet = () => {
    window.onbeforeunload = null
    storeDocument.attachDocument = null
}

function appendIfNotExist(user)
{
    storeChat.chatConversation.unshift(user)
}

function appendIfExist(index, message)
{
    storeChat.chatConversation[index].seen = message.seen
    storeChat.chatConversation[index].sender = message.sender
    storeChat.chatConversation[index].message = message.message
    storeChat.chatConversation[index].created_at = message.created_at

    storeChat.chatConversation.unshift(storeChat.chatConversation.splice(index, 1)[0])
}

try {
    
    Echo.private('SendMessageTo.'+props.user.userId)
        .listen('SendMessageTo', (e) => {
            if(storeChat.isOpenConvo && storeChat.selectedUserChat)
            {
                storeChat.chatConversation.find((convo, index) => {
                    if(convo.convoId == e.message.convoId){
                        appendIfExist(index, e.message)
                        return true;
                    }
                })
                
                if(storeChat.selectedUserChat.convoId == e.message.convoId){
                    storeChat.conversation.unshift({...e.message})
                    axios.get('/chat/seen_msg?convoId='+storeChat.selectedUserChat.convoId).then(res => {
                        storeChat.chatConversation.find(chat => {
                            if(chat.seen == null && chat.convoId == res.data && authUser !== chat.sender){
                                chat.seen = 1
                            }
                        })
                    })
                    storeChat.selectedUserChat.seen = 1
                }else{
                    storeChat.chatConversation.find(chat => {
                        if(chat.convoId == e.message.convoId && !chat.seen){
                            storeChat.chatCounter++
                        }
                    })
                }
            }else{
                let isFound = storeChat.chatConversation.find((convo, index) => {
                    if(convo.convoId === e.message.convoId)
                    {
                        if(!convo.seen)
                            storeChat.chatCounter++

                        appendIfExist(index, e.message)

                        return true;
                    }
                })

                if(!isFound){
                    appendIfNotExist(e.message)
                    storeChat.chatCounter++
                }
            }
        });

} catch (e) {
    //console.log(e)
}

try {
    Echo.private('docuComment.'+props.user.userId)
        .listen('DocumentComment', (e) => {
            storeChat.notifications.unshift(e.comment)
            storeChat.notifCounter++
        });
} catch (e) {
    //console.log(e)
}

try {
    Echo.private('review.'+props.user.userId)
        .listen('DocumentReview', (e) => {
            ////console.log(e)
            storeChat.notifications.unshift(e.review)
            storeChat.notifCounter++
        });

} catch (e) {
    //console.log(e)
}

try {
    Echo.private('docupload.'+props.user.userId)
        .listen('DocumentUpload', (e) => {
            ////console.log(e)
            storeChat.notifications.unshift(e.upload)
            storeChat.notifCounter++
        });

} catch (e) {
    //console.log(e)
}

storeChat.getNotification()

const instance = axios.create({
    baseURL: '/api',
});

instance.interceptors.response.use(
    response => response,
    error => {
        if (error.response.status === 419 || error.response.status === 401) {
            // Redirect to login page or show an error message
            location.reload();
        }
        return Promise.reject(error);
    }
);

window.addEventListener('resize', ()=> {
    if(window.innerWidth <= 768){
        if(!storeLayout.toggleMenu)
        {
            storeLayout.toggleMenu = true
        }
    }else if(window.innerWidth >= 1024){
        storeLayout.toggleMenu = false
    }
})
if(window.innerWidth <= 768){
    if(!storeLayout.toggleMenu)
    {
        storeLayout.toggleMenu = true
    }
}else if(window.innerWidth >= 1024){
    storeLayout.toggleMenu = false
}


</script>