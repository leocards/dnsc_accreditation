<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <div class="">
            {{( myMethod.role($page.props.areaRole) ?? 'Member')}}
        </div>
    </template>
    <template #subNav>
        <div class="w-full py-1.5 flex items-center gap-1.5 sticky top-14 subnavBg dark:border-primaryDarkBorder border-t border-slate-200 px-2.5 z-30">
            <Back @click="routeBackHome($page.props.home)" title="Back" />
            <Crumbs :crumbs="[]" :current="$page.props.area" class="pointer-events-none" />
            <Upload 
                title="Upload"
                :disabled="(!selected?'':selected.category != 'item')"
                @click="isUpload = true"
            />
        </div>
    </template>
    
    <div class="w-full relative flex">
        <div class="pb-3 w-full " :class="{'mr-[22.3rem]':((isComment || isDocument || isInstrumentInfo) && storeTaskPage.selected)}" ref="accordionContainer">
            <div class="mt-2 w-full h-fit rounded-md bg-white dark:bg-secondaryDarkBg p-1.5 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            v-for="instrument in $page.props.instruments">
                <TaskAccordion 
                    :isRoot="true"
                    :location="location"
                    :inst="instrument"
                    @resetItem="setResetLocation"
                    @handleSelect="getSelect"
                    @handleAssign="assignMember"
                    @handleEvidence="showEvidenceToEdit"
                    @handleInfo="getInstrumentInfo"
                    @handleDocument="showDocument"
                    @handleResetSelect="resetSelected"
                    @handleComment="getInstrumentComment"
                    @handleMarkComplete="toggleMarkComplete"
                />
            </div>
        </div>

        <InstrumentComment
            :accredlvl="$page.props.home.id"
            :instrument="storeTaskPage.updateSelect"
            v-if="isComment && storeTaskPage.selected"
            @handleClose="closeInstrumentComment"
        />
        <DocumentAside 
            :evidence="storeDocument.evidenceIndex"
            :user="$page.props.user.userId"
            :role="$page.props.areaRole"
            :accredlvl="$page.props.home.id"
            :instrument="storeTaskPage.updateSelect"
            v-if="isDocument && storeTaskPage.selected"
            @handleClose="closeDocument"
            @handleView="viewDocument"
            @handleEdit="editDocu"
            @handleManageVersion="getManageVersion"
            @handleShare="getShare"
        />
        <InstrumentAside
            :accred="$page.props.home.id"
            :instrument="storeTaskPage.updateSelect"
            v-if="isInstrumentInfo && storeTaskPage.selected"
            @handleClose="closeInstrumentInfo"
        />

    </div>

    <DocumentUploadModal
        :isEdit="isEdit"
        :instrument="selected"
        :document="selectedDocu"
        :isNewVersion="isNewVersion"
        :accredlvl="$page.props.home.id"
        v-if="storeTaskPage.selected && isUpload"
        @handleClose="closeUploadModal"
    />

    <DocumentViewerModal
        :area="$page.props.area.id"
        :accredlvl="$page.props.home.id"
        :instrument="storeTaskPage.updateSelect"
        v-if="openDocument && storeTaskPage.selected"
        @handleClose="closeViewDocument"
    />

    <AssignMemberModal
        :due="$page.props.due"
        :accredlvl="$page.props.home.id"
        :instrument="storeTaskPage.updateSelect"
        v-if="isAssignMember && storeTaskPage.selected"
        @handleClose="closeAssignMember"
    />

    <RemoveCompleteModal
        :inst="storeTaskPage.updateSelect"
        v-if="isUnmarkComplete && storeTaskPage.selected"
        @handleClose="isUnmarkComplete = false"
        @handleCancel="resetSelected(storeTaskPage.updateSelect), isUnmarkComplete = false"
        @handleDelete="unmarkAsComplete"
    />

    <ManageVersionModal
        :document="selectedDocu"
        v-if="isManageVersion && selectedDocu && storeTaskPage.selected"
        @handleClose="isManageVersion = false"
        @handleNewVersion="uploadNewVersion"
    />

    <ShareModal
        :document="selectedDocu"
        v-if="isShare && selectedDocu && storeTaskPage.selected"
        @handleClose="isShare = false"
    />

    <AttatchToEvidenceModal
        v-if="storeDocument.evidenceAttach.showModal"
        @handle-close="storeDocument.evidenceAttach.showModal = false"
    />

    <EditIndicator 
        :inst="selectedIndicatorToEdit"
        v-if="selectedIndicatorToEdit"
        @handleClose="selectedIndicatorToEdit = null"
    />

</Layout>
<input type="hidden" :value="setLocation($page.props.location??[])">
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Back from '../Components/Buttons/Back.vue'
import Crumbs from '../Components/BreadCrumbs.vue'
import Upload from '../Components/Buttons/Upload.vue'
import ShareModal from '../Components/Document/Share.vue'
import TaskAccordion from '../Components/Task/TaskAccordion.vue'
import EditIndicator from '../Components/Instrument/EditIndicator.vue'
import InstrumentAside from '../Components/Instrument/InstrumentAside.vue'
import AttatchToEvidenceModal from '../Components/Document/AttachToEvidence.vue'
import RemoveCompleteModal from '../Components/Task/TaskPage/RemoveComplete.vue'
import DocumentAside from '../Components/Program/ProgramLevel/DocumentAside.vue'
import DocumentViewerModal from '../Components/Document/DocumentViewerModal.vue'
import ManageVersionModal from '../Components/Document/Upload/ManageVersion.vue'
import AssignMemberModal from '../Components/Task/TaskPage/AssignMemberModal.vue'
import DocumentUploadModal from '../Components/Document/Upload/DocumentModal.vue'
import InstrumentComment from '../Components/Program/ProgramLevel/InstrumentComment.vue'
import myMethod from '../Store/Methods'
import { Inertia } from '@inertiajs/inertia'
import { onMounted, onUnmounted, ref } from 'vue'
import { useTaskPageStore } from '../Store/storeTaskPage'
import { useDocumentStore } from '../Store/storeDocument'
import { useLayoutStore } from '../Store/storeLayout'

const storeTaskPage = useTaskPageStore()
const storeDocument = useDocumentStore()
const storeLayout = useLayoutStore()

const props = defineProps({home:Object})

const location = ref([])
const isEdit = ref(false)
const isShare = ref(false)
const selected = ref(null)
const isUpload = ref(false)
const isComment = ref(null)
const isDocument = ref(false)
const selectedDocu = ref(null) // for edit
const isNewVersion = ref(false)
const openDocument = ref(false)
const isAssignMember = ref(false)
const isManageVersion = ref(false)
const isUnmarkComplete = ref(false)
const isInstrumentInfo = ref(false)
const accordionContainer = ref(null)
const selectedIndicatorToEdit = ref(null)

const showEvidenceToEdit = inst => {
    selectedIndicatorToEdit.value = inst
}

const role = role => {
    return role ? 'Task Force Chairperson' : null
}
const routeBackHome = query => {
    Inertia.get(`/task?program=${query.programId}&level=${query.id}`)
}

//select accordion/instrument
const getSelect = (inst) => {
    selected.value = inst
    storeTaskPage.selected = inst.id
    storeTaskPage.updateSelect = inst
}
//reset selected
const resetSelected = inst => {
    if(inst.id == storeTaskPage.selected)
    {
        selected.value = null
        storeTaskPage.selected = null
        storeTaskPage.updateSelect = null
    }
}
//close documentAside
const closeDocument = () => {
    selected.value = null
    isDocument.value = false
    storeTaskPage.selected = null
    storeTaskPage.updateSelect = null
}
//show documentAside and select instrument
const showDocument = inst => {
    getSelect(inst)//select instrument
    isComment.value = false
    isDocument.value = true
    isInstrumentInfo.value = false
}
//close the document view modal
const closeViewDocument = () => {
    openDocument.value = false
}
//open the document view modal
const viewDocument = () => {
    openDocument.value = true
}
//close the upload modal
const closeUploadModal = (res=false) => {
    isUpload.value = false
    if(res){
        closeDocument()
        isEdit.value = false
    }else{
        isEdit.value ? isEdit.value = false : '';
        isNewVersion.value ? isNewVersion.value = false : '';
    }

    storeLayout.keepScrollHidden = false
}
const assignMember = val => {
    getSelect(val)
    isAssignMember.value = true
}
const closeAssignMember = () => {
    isAssignMember.value = false
}
const getInstrumentComment = inst => {
    getSelect(inst)
    isComment.value = true
    isDocument.value = false
    isInstrumentInfo.value = false
}
const getInstrumentInfo = inst => {
    getSelect(inst)
    isInstrumentInfo.value = true
    isDocument.value = false
    isComment.value = false
}
const closeInstrumentInfo = () => {
    selected.value = null
    storeTaskPage.selected = null
    isInstrumentInfo.value = false
    storeTaskPage.updateSelect = null
}
const closeInstrumentComment = () => {
    selected.value = null
    isComment.value = false
    storeTaskPage.selected = null
    storeTaskPage.updateSelect = null
}

const setLocation = val => {
    location.value = val
}
const setResetLocation = id => {
    if(location.value.includes(id)){
        location.value = location.value.filter( val => { return val != id })
    }
}

const toggleMarkComplete = (instrument, setAsComplete) => {
    ////console.log(setAsComplete)
    if(!setAsComplete)
        Inertia.post('/task/set_as_complete', {
            id: instrument.progress.id,
            instrumentId: instrument.id,
            accredlvl: props.home.id,
            isComplete: setAsComplete
        }, {
            preserveScroll: true,
        })
    else{
        getSelect(instrument); 
        isUnmarkComplete.value = true;
    }
        ////console.log(setAsComplete)/*  */ //isUnmarkComplete.value = true
}
const unmarkAsComplete = () => {
    Inertia.post('/task/set_as_complete', {
        id: storeTaskPage.updateSelect.progress.id,
        instrumentId: storeTaskPage.updateSelect.id,
        accredlvl: props.home.id,
        isComplete: true
    }, {
        preserveScroll: true,
        onSuccess: page => {
            isUnmarkComplete.value = false
        }
    })
}

//document options
const editDocu = docu => {
    isEdit.value = true
    isUpload.value = true
    selectedDocu.value = docu
}
const getManageVersion = docu => {
    selectedDocu.value = docu
    isManageVersion.value = true
}
const uploadNewVersion = () => {
    isUpload.value = true
    isNewVersion.value = true
    isManageVersion.value = false
    storeLayout.keepScrollHidden = true
}
const getShare = docu => {
    isShare.value = true
    selectedDocu.value = docu

    //console.log(docu)
}

const windowsClick = e => {
    if(!e.target.closest(`#taskAccordion${storeTaskPage.selected}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal') && !e.target.closest('.itemCard') &&
    !e.target.closest('.ContentEditble') && !e.target.closest('.asideContainer') && !e.target.closest('#optionContainer'))
    {
        selected.value = null
        isManageVersion.value = null
        storeTaskPage.selected = null

        isComment.value = false
        isDocument.value = false
        isInstrumentInfo.value = false
    }
}
onMounted(()=>{
    ////console.log(props.)

    window.addEventListener('click', windowsClick)
    if(accordionContainer.value){
        myMethod.resizedElement(accordionContainer.value, () => {
            let evidenceElement = document.querySelectorAll('.aqwQdwEr')
            let labelElement = document.querySelectorAll('.qwIOrAQty')
            try{
                if(accordionContainer.value.clientWidth <= 896)
                {
                    if (evidenceElement.length > 0)
                        if(accordionContainer.value.clientWidth <= 640)
                        {
                            labelElement.forEach(el => {
                                el.style.display = 'none'
                            })
                        }else if(accordionContainer.value.clientWidth > 640){
                            labelElement.forEach(el => {
                                el.style.display = ''
                            })
                            evidenceElement.forEach(el => {
                                el.style.display = 'none'
                            })
                        }
                }else{
                    if (evidenceElement.length > 0){
                        evidenceElement.forEach(el => {
                            el.style.display = ''
                        })
                    }
                    if(labelElement.length > 0){
                        labelElement.forEach(el => {
                            el.style.display = ''
                        })
                    }
                }
            }catch(e){
                //console.log()
            }
        })
    }
})
onUnmounted(()=>{
    window.removeEventListener('click', windowsClick)
    storeTaskPage.reset()
})

</script>