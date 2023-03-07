<template>
    <div class="w-full min-h-fit max-h-[90vh] flex" :class="[[1, 6].includes($page.props.user.auth) ?'py-2':'p-0']" >
        <div class="w-full rounded subnavBg grow p-2 flex flex-col">
            <div class="w-full h-10 flex items-center justify-between">
                <div class="flex items-center gap-4 text-green-600">
                    <AnnouncementIcon />
                    Announcements
                </div>

                <div v-if="[1, 6].includes($page.props.user.auth)">
                    <AddButton :active="true" :style="'p-0.5 px-2 pl-1'" @click="$emit('newAnnouncement')">
                        New
                    </AddButton>
                </div>
            </div>

            <div class="border border-slate-300 dark:border-primaryDarkBorder rounded p-3 pt-0 mt-3 h-full overflow-y-auto overscroll-contain">
                <div class="w-full py-4 text-center text-slate-400" v-if="myMethod.ifArray(announcements, '==', 0)">No Announcement</div>
                
                <Card
                    :announce="ann" 
                    v-for="(ann, index) in announcements" 
                    v-if="announcements" 
                    @handleRemove="$emit('handleRemove', ann.id, index)"
                    @handleEdit="$emit('updateAnnounce', ann)"
                />

                <div class="flex items-center justify-center py-3" v-if="!announcements">
                    <Loading />
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import Loading from '../Loading.vue'
import Card from './AnnouncementCard.vue'
import AddButton from '../Buttons/AddButton.vue'
import AnnouncementIcon from '../Icons/announcementIcon.vue'
import axios from 'axios'
import { ref } from '@vue/reactivity'
import myMethod from '../../Store/Methods'

const props = defineProps({
    announcements: Object
})
const emits = defineEmits([
    'updateAnnounce',
    'newAnnouncement',
    'handleRemove'
])

</script>