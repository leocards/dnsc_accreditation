<template>
    <Modal 
        @handleClose="$emit('handleClose')"
    >
        <template #header>
            Document Versions
        </template>

        <div class="max-h-[80vh] min-h-[40vh] pb-3">
            <div class="flex justify-end py-4">
                <button 
                    class="rounded-md inter-font bg-dnscGreen/90 hover:bg-dnscGreen transition_300 hover:shadow-md dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] h-9 px-2 text-white"
                    @click="$emit('handleNewVersion')"
                >
                    Upload new version
                </button>
            </div>

            <div>
                Current version

                <div class="p-1 cursor-default mt-2.5">
                    <div class="Oneline max-w-[26rem]">{{document.title}}</div>
                    <div class="text-xs mt-1 Oneline max-w-[28rem] secondaryText">
                        <Date :time="document.updated_at" />
                        <span class="ml-2" title="">{{document.get_user.first_name+' '+document.get_user.last_name}}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                Old version

                <div class="max-h-[50vh] overflow-y-auto pt-1" id="adasdasd">
                    <VersionCard 
                        :version="version"
                        v-for="version in versions"
                        @handleVersionMore="clickMore"
                    />
                </div>

                <div class="flex justify-center py-3" v-if="!versions">
                    <Loading/>
                </div>

                <div class="flex justify-center py-3 dark:text-white/50 text-gray-400" v-if="versions && myMethods.ifArray(versions, '==', 0)">
                    No older version
                </div>
            </div>
        </div>

        <div class="rounded bg-white dark:bg-tertiaryDarkBg w-44 fixed myshadow top-0 py-2 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] hidden" id="versionOption">
            <ul>
                <li class="p-2 transition_300 hover:bg-black/10 dark:hover:bg-white/20 cursor-pointer select-none" @click="setOldToCurrent">Set as current</li>
                <a :href="`/download/${selectedVersion}`">
                    <li class="p-2 transition_300 hover:bg-black/10 dark:hover:bg-white/20 cursor-pointer select-none">
                        Download
                    </li>
                </a>
            </ul>
        </div>

    </Modal>
</template>

<script setup>
import Modal from '../../Modal.vue'
import Date from '../../DateTime.vue'
import Loading from '../../Loading.vue'
import VersionCard from './VersionCard.vue'
import myMethods from '../../../Store/Methods'
import { onMounted, ref } from '@vue/runtime-core'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    document: Object
})
const emits = defineEmits([
    'handleClose',
    'handleNewVersion',
])

const modalRef = ref(null)
const versions = ref(null)
const versionOption = ref(null)
const selectedVersion = ref(null)

const clickMore = version => {
    selectedVersion.value = version.id
    //console.log(selectedVersion.value)
}

const setOldToCurrent = () => {
    Inertia.post('/document/set_as_current', {
        current: props.document.id,
        newCurrent: selectedVersion.value
    }, {
        onSuccess: page => {
            if(page.props.flash.success)
                emits('handleClose')
            //console.log(page.props.flash.error)
        }
    })
}

onMounted(() => {
    modalRef.value = document.getElementById('modalRef');
    versionOption.value = document.getElementById('versionOption');
    myMethods.MoreMenus(modalRef.value, versionOption.value)

})

try {
    axios.post('/document/get/versions', {
        id: props.document.id
    })
    .then(res => {
        versions.value = res.data.versions
    })
} catch (e) {
    console.log(e)
}


</script>