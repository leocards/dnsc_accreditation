<template>
    <Head>
        <title>Accreditation | Accreditor</title>
    </Head>
    
    <div class="w-full h-14 bg-white fixed top-0 flex items-center z-30 subnavBg">
        <div class="md:w-60 h-full flex items-center gap-2 px-3">
            <BurgerMenu @click="toggleSideBar" />

            <div class="inter-font font-semibold text-dnscGreen max-sm:hidden"> ACCREDITATION </div>
        </div>

        <div class="grow">
        </div>

        <div class="pr-3 flex gap-2 items-center">
            <div>
                {{$page.props.user.user}}
            </div>
            <div class="w-10 h-10 rounded-full items-center flex justify-center bg-whiteBg dark:bg-white/10 dark:text-secondaryWhite group relative"
            v-click-outside="clickOutside">
                <button type="button" class="w-10 h-10 rounded-full overflow-hidden" @click="isViewProfOpeion = !isViewProfOpeion">
                    <Avatar :avatar="$page.props.user.img" />
                </button>

                <div class="rounded-md bg-white myshadow dark:bg-tertiaryDarkBg py-2 absolute top-10 right-0 w-44 text-left z-50" v-if="isViewProfOpeion">
                    <ul>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2" @click="isProfile = true, clickOutside()">Profile</li>

                        <li class="text-xs dark:text-white/40 text-slate-400 pl-2 mt-2">Theme</li>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2 flex items-center gap-2" 
                            @click="enableDark"
                        >Dark mode <div class="w-2 h-2 rounded-full bg-dnscGreen" v-if="$page.props.theme"></div> </li>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2 flex items-center gap-2" 
                            @click="enableLight"
                        >Light mode <div class="w-2 h-2 rounded-full bg-dnscGreen" v-if="!$page.props.theme"></div> </li>
                        <li class="my-2 border-t dark:border-white/30 border-slate-200"></li>
                        <li class="dark:hover:bg-white/10 hover:bg-gray-300/80 transition_300 cursor-pointer py-2 px-2" @click="logout">Logout</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="subnavBg lg:w-60 w-60 h-screen md:h-[calc(100%-3.5rem)] shrink-0 fixed md:top-14 z-50" v-if="!hideSidebar">

        <div class="px-3 pb-1 w-full ml-auto flex items-center justify-end gap-2 border-b border-slate-200 dark:border-primaryDarkBorder"
        v-if="$page.props.currentProgram">
            <span>Home</span>
            <Home @click="getHome($page.props.accreditor)" />
        </div>

        <div class="text-center py-4" v-if="!$page.props.currentProgram">
            Select program
        </div>

        <div class="py-2 overflow-y-auto" v-else>

            <AreaCard 
                :area="area"
                v-for="area in $page.props.areas"
                @handleClick="getAreaInstrument(area.id, $page.props.accred, $page.props.accreditor)"
            />

        </div>

    </div>

    <Profile :user="profile" v-if="isProfile" @handleClose="isProfile = false" />
    
    <div class="min-h-[calc(100vh-3.5rem)] mt-14 max-sm:ml-0 max-sm:w-full md:w-full lg:ml-60 lg:w-[calc(100%-15rem)]"
    :class="[!hideSidebar?'':'']">
        <div class="px-2.5">
            <slot />
        </div>
    </div>

</template>

<script setup>
import AreaCard from './AreaCard.vue'
import Home from '../Buttons/Home.vue'
import Avatar from '../Avatar.vue'
import BurgerMenu from '../Buttons/BurgerMenu.vue'
import Profile from '../User/Profile.vue'
import { ref } from 'vue'
import { Head } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    user: Object
})

const hideSidebar = ref(false)

const toggleSideBar = () => {
    hideSidebar.value = !hideSidebar.value
}

const profile = ref(null)
const isProfile = ref(false)
const isViewProfOpeion = ref(false)

const logout = () => {
    Inertia.get('/logout')
}

const getProfile = async (id) => {
    try {
        let user = await axios.get('/user/profile/'+id)
        profile.value = user.data

    } catch (e) {
        profile.value = 'error'
    }
}
getProfile(props.user.userId)

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

const getAreaInstrument = (area, accred, isAccreditor) => {
    Inertia.get((isAccreditor?'/accreditor':'/self_accreditor'), {
        program: accred.programId,
        level: accred.instrumentId,
        survey: accred.id,
        area: area
    })
}

const getHome = isAccreditor => {
    if(isAccreditor)
        Inertia.get('/accreditor')
    else
        Inertia.get('/self_accreditor')
}

if(window.innerWidth <= 768){
    hideSidebar.value = true
}


</script>