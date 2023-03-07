<template>
    <div class="w-full h-14 bg-white fixed top-0 flex z-40 subnavBg">
        <div class="md:w-60 h-full flex items-center gap-2 px-3">
            <BurgerMenu @click="$emit('handleMenu')" />

            <div class="inter-font font-semibold text-dnscGreen max-sm:hidden"> ACCREDITATION </div>
        </div>

        <div class="grow flex items-center subnavBg">
            <slot />
        </div>
        
        <div class="shink-0 h-full flex items-center px-2.5 gap-2 text-slate-500">

            <button type="button" class="w-10 h-10 rounded-full items-center flex justify-center bg-whiteBg dark:bg-white/10 dark:text-secondaryWhite 
            transition duration-300 hover:dark:bg-white/20 hover:bg-dnscGreen/20 hover:text-dnscGreen dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative"
            @click="storeChat.isOpenMessages = true">
                <Counter v-if="storeChat.chatCounter > 0" :counter="storeChat.chatCounter" :position="'-top-0.5 right-1'" />
                <MessageIcon />
            </button>
            <button type="button" class="w-10 h-10 rounded-full items-center flex justify-center bg-whiteBg dark:bg-white/10 dark:text-secondaryWhite 
            transition duration-300 hover:dark:bg-white/20 hover:bg-dnscGreen/20 hover:text-dnscGreen dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] relative"
            @click="$emit('handleNotification')">
                <Counter v-if="storeChat.notifCounter > 0" :counter="storeChat.notifCounter" :position="'-top-0.5 right-1'" />
                <NotificationIcon />
            </button>
            
            <div class="w-10 h-10 rounded-full items-center flex justify-center bg-whiteBg dark:bg-white/10 dark:text-secondaryWhite group relative"
            v-click-outside="clickOutside">
                <button type="button" class="w-10 h-10 rounded-full overflow-hidden" @click="isViewProfOpeion = !isViewProfOpeion">
                    <Avatar :avatar="$page.props.user.img" />
                </button>

                <div class="rounded-md bg-white myshadow dark:bg-tertiaryDarkBg py-2 absolute top-10 right-0 w-44 text-left z-50" v-if="isViewProfOpeion">
                    <ul>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2" @click="getProfile">Profile</li>

                        <li class="text-xs dark:text-white/40 text-slate-400 pl-2 mt-2">Theme</li>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2 flex items-center gap-2" 
                            @click="enableDark"
                        >Dark mode <div class="w-2 h-2 rounded-full bg-dnscGreen" v-if="$page.props.theme"></div> </li>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2 flex items-center gap-2" 
                            @click="enableLight"
                        >Light mode <div class="w-2 h-2 rounded-full bg-dnscGreen" v-if="!$page.props.theme"></div> </li>
                        <li class="my-2 border-t dark:border-white/30 border-slate-200"></li>
                        <a href="/logout"><li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 py-2 px-2">Logout</li>  </a>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>
<script setup>
import Avatar from './Avatar.vue'
import Counter from './CountNotification.vue'
import MessageIcon from './Icons/chatIcon.vue'
import BurgerMenu from './Buttons/BurgerMenu.vue'
import NotificationIcon from './Icons/notificationIcon.vue'
import { Inertia } from '@inertiajs/inertia'
import { useChatStore } from '../Store/storeChatNotif'
import { ref } from '@vue/reactivity'
import { onMounted } from '@vue/runtime-core'

const storeChat = useChatStore()

const emits = defineEmits(['handleMenu', 'handleChat', 'handleNotification', 'handleProfile'])

const isViewProfOpeion = ref(false)

const logout = () => {
    Inertia.get('/logout')
}

const clickOutside = () => {
    isViewProfOpeion.value = false
}

const enableDark = () => {
    async function setTheme(){
        try {
            let res = await axios.post('/user/setTheme', { theme: true})
            window.location.reload()
        } catch (e) {
            console.log(e.message)
        }
    }
    setTheme()
}
const enableLight = () => {
    async function setTheme(){
        try {
            let res = await axios.post('/user/setTheme', { theme: null})
            window.location.reload()
        } catch (e) {
            console.log(e.message)
        }
    }
    setTheme()
}

const getProfile = () => {
    emits('handleProfile')
    clickOutside()
}

</script>