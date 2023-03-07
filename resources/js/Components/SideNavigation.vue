<template>
    <div :class="[!toggleMenu?'max-sm:w-full h-full fixed bg-black/40 z-[31]':'']" @contextmenu.prevent>

    </div>
    <div class="subnavBg max-sm:h-full max-sm:top-0 md:h-[calc(100%-3.5rem)] shrink-0 fixed top-14 md:py-2 border-r border-slate-200 z-[32] overflow-hidden
    dark:border-primaryDarkBorder slate-700/70"
    :class="[toggleMenu ? 'w-16 max-sm:shadow-md max-sm:hidden':'md:w-16 lg:w-60 max-sm:w-60']" @contextmenu.prevent>

        <div class="max-sm:blcok md:hidden h-14 flex items-center gap-2 px-3 mb-2">
            <BurgerMenu @click="storeLayout.toggleMenu = !storeLayout.toggleMenu" />

            <div class="inter-font font-semibold text-dnscGreen"> ACCREDITATION </div>
        </div>

        <ul class="select-none">
            <li>
                <Link href="/dashboard">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/dashboard') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <DashboardIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Dashboard</span>
                    </div>
                </Link>
            </li>
            <li v-if="myMethod.authorized($page.props.user.auth, 2)">
                <Link href="/institute">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/institute') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <InstitutesIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Institutes</span>
                    </div>
                </Link>
            </li>
            <li v-if="myMethod.authorized($page.props.user.auth, 3)">
                <Link href="/program">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/program') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <ProgramsIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Programs</span>
                    </div>
                </Link>
            </li>
            <li v-if="myMethod.authorized($page.props.user.auth, 4)">
                <Link href="/accreditation">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/accreditation') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <AccreditationIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Accreditation</span>
                    </div>
                </Link>
            </li>
            <li v-if="myMethod.authorized($page.props.user.auth, 5)">
                <Link href="/user">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/user') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <UsersIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Users</span>
                    </div>
                </Link>
            </li>
            <li>
                <Link href="/task">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/task') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <TasksIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Tasks</span>
                    </div>
                </Link>
            </li>
            <li>
                <Link href="/document/uploads">
                    <div class="w-full p-2 py-3.5 md:justify-center" 
                    :class="[$page.url.startsWith('/document') ? 'active-sn':'default-sn'], [toggleMenu?'justify-center max-sm:justify-start':' lg:justify-start']">
                        <DocuIcon />
                        <span :class="[toggleMenu?'md:hidden max-sm:block':'md:hidden lg:block']" class="">Documents</span>
                    </div>
                </Link>
            </li>

            <li class="mt-2" v-if="storeDocument.attachDocument && storeDocument.attachDocumentMinimize">
                <ul>
                    <li class="dark:bg-white/10 transition_300 bg-black/5 hover:bg-gray-300/80 flex items-center w-full p-2 py-3.5 cursor-pointer actionBtn" 
                    @click="storeDocument.attachDocumentMinimize = false">
                        <div class="Oneline">
                            {{storeDocument.attachDocument.title}}
                        </div>
                        <div class="shrink-0 transition_300 p-0.5 rounded-full cursor-pointer hover:bg-dnscGreen actionBtn hover:text-white ml-auto" @click="removeAttachmet">
                            <CloseIcon />
                        </div>

                        <div class="py-0.5 whitespace-nowrap" v-if="storeDocument.processAttachment">
                            Attaching document...
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
        <div class="absolute bottom-52 right-44 -z-50 w-[12rem] h-[90vh] bg-[#01a66f] rounded-full -rotate-45 opacity-5 blur-2xl"></div>
        <div class="absolute -bottom-60 right-0 -z-50 w-[4rem] h-[90vh] bg-[#00aead] rounded-full -rotate-45 opacity-10 blur-2xl"></div>

    </div>
</template>
<script setup>
import DocuIcon from './Icons/docuIcon.vue'
import UsersIcon from './Icons/userIcon.vue'
import TasksIcon from './Icons/taskIcon.vue'
import BurgerMenu from './Buttons/BurgerMenu.vue'
import ProgramsIcon from './Icons/programIcon.vue'
import DashboardIcon from './Icons/dashboardIcon.vue'
import InstitutesIcon from './Icons/instituteIcon.vue'
import AccreditationIcon from './Icons/accreditIcon.vue'
import CloseIcon from './Icons/closeIcon.vue'
import myMethod from '../Store/Methods'
import { useLayoutStore } from '../Store/storeLayout'
import { useDocumentStore } from '../Store/storeDocument'

const storeLayout = useLayoutStore()
const storeDocument = useDocumentStore()

const props = defineProps({
    toggleMenu: Boolean
})

const removeAttachmet = () => {
    window.onbeforeunload = null
    storeDocument.attachDocument = null
}

</script>