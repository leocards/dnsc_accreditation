<template>
    <div class="w-[15rem] min-h-[10rem] fixed top-0 z-[300] bg-white dark:bg-tertiaryDarkBg shrink-0 rounded-md 
    dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] hidden" 
    id="optionContainer" 
    @contextmenu.prevent
    v-click-outside="clickOutside" ref="option">
        <div class="w-full py-2 myshadow overflow-hidden rounded-md shadow-lg">
            <ul class="optionList">
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                @click="viewDocument">
                    <ViewIcon /> View document
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer" v-if="false">
                    <LocationIcon /> Find location
                </li>
                <!-- <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer">
                    <ActivityIcon /> Activity
                </li> -->
                
                <li class="my-2 border-t border-slate-200 dark:border-white/20" v-if="isMember"></li>

                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                v-if="isMember || ([1,6,4].includes($page.props.user.auth) && $page.url.startsWith('/program'))"
                :class="[isReview?'pointer-events-none text-black/30 dark:text-white/30':'']"
                @click="$emit('handleReview', 'Approve')">
                    <ApproveIcon /> Approve
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                v-if="isMember || ([1,6,4].includes($page.props.user.auth) && $page.url.startsWith('/program'))"
                :class="[isReview?'pointer-events-none text-black/30 dark:text-white/30':'']"
                @click="$emit('handleReview', 'Revise')">
                    <ReviseIcon /> Revise
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_30 cursor-pointer"
                v-if="isMember || ([1,6,4].includes($page.props.user.auth) && $page.url.startsWith('/program'))"
                :class="[isReview?'pointer-events-none text-black/30 dark:text-white/30':'']"
                @click="$emit('handleReview', 'Reject')">
                    <RejectIcon /> Reject
                </li>

                <li class="my-2 border-t border-slate-200 dark:border-white/20"></li>

                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                @click="editDocument">
                    <EditIcon /> Edit
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                @click="ManageVersion">
                    <ManageVersionIcon /> Manage version
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                v-if="isMember"
                @click="share">
                    <ShareIcon /> Share
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                @click="attachTo">
                    <AttachIcon /> Attach to
                </li>
                <li class="px-2 py-2 flex items-center gap-2.5 transition_300 select-none actionBtn"
                :class="[selectedDocument?selectedDocument.type == 'link'?'dark:text-white/30 text-black/30':'hover:bg-black/10 dark:hover:bg-white/20 cursor-pointer'
                :'hover:bg-black/10 dark:hover:bg-white/20 cursor-pointer']"
                @click="download(selectedDocument)">
                    <DownloadIcon /> Download
                </li>

                <li class="my-2 border-t border-slate-200 dark:border-white/20"></li>

                <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300 cursor-pointer"
                @click="removeDocument(selectedDocument)">
                    <DeleteIcon /> Remove
                </li>
            </ul>
        </div>

        <a :href="`/download/${selectedDocument?selectedDocument.id:''}`" class="hidden" ref="downloadLink"></a>
    </div>
</template>
<script setup>
import ViewIcon from '../Icons/smallEyeOpenIcon.vue'
import ActivityIcon from '../Icons/activityIcon.vue'
import LocationIcon from '../Icons/locationIcon.vue'
import ApproveIcon from '../Icons/checkIcon.vue'
import ReviseIcon from '../Icons/reviseDocIcon.vue'
import RejectIcon from '../Icons/closeIcon.vue'
import EditIcon from '../Icons/editIcon.vue'
import ManageVersionIcon from '../Icons/manageIcon.vue'
import ShareIcon from '../Icons/shareIcon.vue'
import AttachIcon from '../Icons/attachIcon.vue'
import DownloadIcon from '../Icons/downloadIcon.vue'
import DeleteIcon from '../Icons/deleteIcon.vue'
import { ref } from '@vue/reactivity'
import { onMounted } from '@vue/runtime-core'
import { Inertia } from '@inertiajs/inertia'
import { useDocumentStore } from '../../Store/storeDocument'

const storeDocument = useDocumentStore()

const props = defineProps({
    isReview: Boolean,
    isMember: Boolean,//if a user is a member then it should be false otherwise true
    selectedDocument: Object
})

const emits = defineEmits([
    'handleEdit',
    'handleShare',
    'handleDelete',
    'handleReview',
    'handleActivity',
    'handleAttachTo',
    'handleDownload',
    'handleFindLocation',
    'handleViewDocument',
    'handleManageVersion',
])

const option = ref(null)
const downloadLink = ref(null)

const editDocument = () => {
    emits('handleEdit')
    clickOutside()
}
const ManageVersion = () => {
    emits('handleManageVersion')
    clickOutside()
}
const share = () => {
    emits('handleShare')
    clickOutside()
}
const download = (val) => {
    if(val.type != 'link')
        downloadLink.value.click()
}

const attachTo = () => {
    storeDocument.attachDocument = props.selectedDocument
    window.onbeforeunload = storeDocument.preventReload
    clickOutside()
}

const removeDocument = docu => {
    storeDocument.setDocuToRemove = docu
    clickOutside()
}

const clickOutside = () => {
    option.value.classList.add('hidden')
}

const viewDocument = () => {
    emits('handleViewDocument')
    clickOutside()
}

</script>