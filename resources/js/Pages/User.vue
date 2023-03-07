<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <NavTabs />
    </template>
    <template #subNav>

        <div class="w-full py-1.5 flex gap-1.5 sticky top-14 subnavBg border-t dark:border-primaryDarkBorder border-slate-200 px-2.5">
            <div v-if="$page.url.startsWith('/user/request')" class="flex items-center gap-2.5">
                <Link :href="'/user/request'" class="rounded h-9 px-2 flex items-center transition_300 dark:text-white/70"
                :class="[$page.url.startsWith('/user/request') && !$page.url.startsWith('/user/request?r=') ? 'hover_green dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]':'hover:hover_green']">
                    Pending
                </Link>
                <Link :href="'/user/request?r=true'" class="rounded h-9 px-2 flex items-center transition_300 dark:text-white/70"
                :class="[$page.url.startsWith('/user/request?r=true') ? 'hover_green dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]':'hover:hover_green']">
                    Removed
                </Link>
            </div>
            <div v-else>
                <Edit :disabled="!selected" @click="isOpenReg = true" />
            </div>
            <div class="ml-auto">
                <Search 
                    v-model="modelValue"
                    placeholder="Search user" 
                />
            </div>
        </div>
    </template>
    
    <div class="grid grid-cols-[repeat(auto-fill,minmax(15rem,1fr))] gap-3 mt-3" v-if="$page.url.startsWith('/user') && !$page.url.startsWith('/user/request')">
        <Card v-for="user in $page.props.users" :id="user.id" :selected="selected" @click="selectUser(user)">

            <div class="h-[calc(4rem-0.75rem)] w-full flex items-center">
                <div class="w-12 h-12 rounded-full overflow-hidden shrink-0">
                    <Avatar :avatar="user.avatar" />
                </div>
                <div class="select-none ml-2 h-fit">
                    <div class="Twoline">{{user.name}}</div>
                </div>
            </div>

        </Card>
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(15rem,1fr))] gap-3 mt-3" v-if="$page.url.startsWith('/user/request')">
        <Card v-for="user in $page.props.users" :id="user.id" :selected="selected">

            <div class="h-[calc(4rem-0.75rem)] w-full flex items-center">
                <div class="w-12 h-12 rounded-full overflow-hidden shrink-0">
                    <Avatar />
                </div>
                <div class="select-none ml-2 h-fit">
                    <div class="Twoline">{{user.name}}</div>
                </div>
            </div>

            <div class="flex ml-auto gap-1.5 dark:text-white/80">
                <button 
                    v-if="!$page.url.startsWith('/user/request?r=true')"
                    class="inter-font text-xs py-1.5 rounded min-w-[5rem] dark:bg-tertiaryDarkBg bg-gray-300 
                    hover:dark:bg-white/20 hover:bg-gray-400/80 transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
                    @click="removeRequest(user)"
                >Remove</button>

                <button 
                    v-if="!$page.url.startsWith('/user/request?r=true')"
                    class="inter-font text-xs py-1.5 rounded min-w-[5rem] bg-dnscGreen/80  text-white
                    hover:bg-dnscGreen transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
                @click="openRegistration(user)">Register</button>

                <button 
                    v-if="$page.url.startsWith('/user/request?r=true')"
                    class="inter-font text-xs py-1.5 rounded min-w-[5rem] bg-dnscGreen/80  text-white
                 hover:bg-dnscGreen transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
                @click="restoreReq(user)">Restore</button>
            </div>

        </Card>
    </div>

    <Registration 
        :isEdit="isEdit"
        :user="selectedUser"
        v-if="isOpenReg"
        @handleClose="closeRegistration"
        @update="closeRegistration"
    />
</Layout>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Search from '../Components/Search.vue'
import Avatar from '../Components/Avatar.vue'
import Edit from '../Components/Buttons/Edit.vue'
import Card from '../Components/User/UserCard.vue'
import NavTabs from '../Components/User/NavTab.vue'
import Registration from '../Components/User/RegisterModal.vue'
import { throttle } from 'lodash'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const isEdit = ref(false)
const selected = ref(null)
const isOpenReg = ref(false)
const modelValue = ref(null)
const selectedUser = ref(null)

const openRegistration = user => {
    isOpenReg.value = true
    selected.value = user.id
    selectedUser.value = user
}
const closeRegistration = () => {
    isOpenReg.value = false
    selected.value = null
    selectedUser.value = null
    isEdit.value = false
}
const selectUser = user => {
    isEdit.value = true
    selected.value = user.id
    selectedUser.value = user
}
const removeRequest = request => {
    Inertia.post('/user/removeRequest', {
        id: request.id
    })
}

const restoreReq = request => {
    Inertia.post('/user/restoreRequest', {
        id: request.id
    })
}

watch(modelValue, throttle(search => {
    if(window.location.pathname == '/user')
        Inertia.get('/user', {search: search}, {
            preserveState: true
        })
    else if(window.location.pathname == '/user/request')
        if((new URLSearchParams(window.location.search)).get('r')){
            Inertia.get('/user/request?r=true', {search: search}, {
                preserveState: true
            })
        }else
            Inertia.get('/user/request', {search: search}, {
                preserveState: true
            })
    
}, 900))

</script>