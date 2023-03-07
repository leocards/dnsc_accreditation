<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <div>
            
        </div>
    </template>

    <div class="py-2.5">
        <StatisticsCard v-if="[1, 6].includes($page.props.user.auth)" />

        <Announcement 
            :announcements="announcements" 
            @handleRemove="removeAnnounce"
            @updateAnnounce="openUpdateAnnouncement"
            @newAnnouncement="isAnnounce = true" 
        />

        <Analytics v-if="[1, 6].includes($page.props.user.auth)" />
    </div>

    <AnnouncementModal 
        :edit="updateAnnount"
        v-if="isAnnounce"
        @handleClose="isAnnounce = false, updateAnnount = null"
        @handleSave="addNewAnnouncement"
    />
</Layout>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Analytics from '../Components/Dashboard/Analytics.vue'
import StatisticsCard from '../Components/Dashboard/Statistics.vue'
import Announcement from '../Components/Dashboard/Announcement.vue'
import AnnouncementModal from '../Components/Dashboard/AnnouncementModal.vue'
import { ref } from '@vue/reactivity'
import { Inertia } from '@inertiajs/inertia'

const isAnnounce = ref(false)
const announcements = ref(null)
const updateAnnount = ref(null)

const addNewAnnouncement = announce => {
    if(updateAnnount.value){
        let index = announcements.value.findIndex(({id}) => id === announce.id)
        announcements.value[index] = announce
        isAnnounce.value = false
    }else{
        isAnnounce.value = false
        announcements.value.unshift(announce)
    }
}

const openUpdateAnnouncement = announce => {
    updateAnnount.value = announce
    isAnnounce.value = true
}

const removeAnnounce = (id, index) => {
    Inertia.post('/dashboard/remove_announce', {
        id: id
    }, {
        onSuccess: page => {
            if(page.props.flash.success)
                announcements.value.splice(index, 1)
        }
    })
}

try {
    axios.post('/announcements')
    .then(res => {
        announcements.value = res.data
    })
} catch (e) {

}

</script>