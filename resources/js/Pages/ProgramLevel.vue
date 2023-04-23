<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <div class="pl-2.5">
            <button type="button" class="px-2.5 h-9 flex items-center rounded hover:hover_green transition duration-300 hover:dark:text-white/80 
            hover:dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            @click="openFacultyStaff = true" title="Faculty and Staff">
                <FacultyStaffIcon />
                <div class="ml-3 max-sm:hidden">Faculty and Staff</div>
            </button>
        </div>

        <div class="ml-3 text-base">{{$page.props.program.abbreviation}}</div>
    </template>
    <template #subNav>
        <div class="w-full py-1.5 flex gap-1.5 sticky top-14 subnavBg dark:border-primaryDarkBorder border-t border-slate-200 px-2.5 z-30">
            <BackButton 
                title="Back"
                @click="routeBack($page.props.program.id, 
                ($page.props.currentlvl?$page.props.currentlvl.id:null),
                ($page.props.crumbs.length > 1?$page.props.crumbs.at(1).id:null))" 
                :disabled="$page.props.crumbs.length <= 0" 
            />

            <Link :href="`/program/level/prog/${$page.props.program.id}/level/`" title="Home">
                <HomeButton />
            </Link>

            <Crumbs :crumbs="$page.props.crumbs" :current="$page.props.current" />

            <div class="flex gap-1.5" v-if="![4, 3].includes($page.props.user.auth)">
                <TaskForceButton 
                    title="Task force"
                    :disabled="!storeProgLvl.selected || category != 'area' || $page.props.user.auth == 4" 
                    v-if="($page.props.current?$page.props.current.category == 'lvl':'')" 
                    @click="getAssignArea"
                />
            </div>

            <AccreditorButton 
                title="Accreditor"
                :disabled="!storeProgLvl.selected || category != 'area' || $page.props.user.auth == 4" 
                v-if="![4, 3].includes($page.props.user.auth) && ($page.props.current?$page.props.current.category == 'lvl':'')" 
                @click="getAssignSA"
            />
            <UploadButton 
                title="Upload" 
                :disabled="!storeProgLvl.selected || category != 'item'" 
                v-if="($page.props.current?$page.props.current.category == 'area':'')" 
                @click="isUpload = true"
            />
        </div>
    </template>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-3 pb-3" 
    v-if="($page.props.current?$page.props.current.category != 'area':true)">
        <Card 
            :instrument="instrument"
            :selected="storeProgLvl.selected"
            v-for="instrument in $page.props.instruments"
            @click="getSelect(instrument)"
            @dblclick="routeInstrument($page.props.program.id, 
                ($page.props.current?$page.props.currentlvl.id:instrument.id), instrument.id)"
            @handleRoute="routeInstrument($page.props.program.id, 
                ($page.props.current?$page.props.currentlvl.id:instrument.id), instrument.id)"
        />
    </div>

    <div class="w-full relative flex" v-if="($page.props.current?$page.props.current.category == 'area':false)">
        <div class="pb-3 w-full" :class="{'mr-[22.3rem]':((isComment || isDocument || isInstInfo) && storeProgLvl.selected)}" ref="accordionContainer">
            <div class="mt-2 w-full h-fit rounded-md bg-white dark:bg-secondaryDarkBg p-1.5 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            v-for="(instrument, index) in [...$page.props.instruments.filter(({parent}) => { return parent == $page.props.current.id})]" :key="index">
                <Accordion 
                    :inst="instrument"
                    @handleInfo="handleInfo"
                    @handleSelect="getSelect"
                    @handleDocument="showDocument"
                    @handleComment="getInstrumentComment"
                />
            </div>
        </div>

        <InstrumentComment
            :instrument="storeProgLvl.updateSelect"
            :accredlvl="$page.props.currentlvl.accredId"
            v-if="isComment && storeProgLvl.selected"
            @handleClose="closeInstrumentComment"
        />
        <DocumentAside 
            :evidence="storeDocument.evidenceIndex"
            :user="$page.props.user.userId"
            :instrument="selected"
            :accredlvl="$page.props.currentlvl.accredId"
            v-if="isDocument && storeProgLvl.selected"
            @handleEdit="editDocu"
            @handleClose="CloseDocument"
            @handleView="viewDocument"
            @handleManageVersion="getManageVersion"
        />

        <InstrumentAside 
            :instrument="selected"
            :accred="$page.props.currentlvl.accredId"
            v-if="storeProgLvl.selected && isInstInfo"
            @handleClose="closeInstInfo"
        />
    </div>

    <FacultyList
        :program="$page.props.program"
        v-if="openFacultyStaff"
        @handleClose="openFacultyStaff = false"
    />

    <TaskForceModal
        :selected="selected"
        :area="storeProgLvl.selected"
        :accredlvl="$page.props.currentlvl.accredId"
        v-if="storeProgLvl.selected && isAssignTf"
        @handleClose="closeAssignArea"
    />

    <AccreditorModal
        :area="selected"
        :accredlvl="$page.props.currentlvl.accredId"
        v-if="storeProgLvl.selected && isAssignSA"
        @handleClose="closeAssignSA"
    />

    <DocumentUploadModal
        :isEdit="isEdit"
        :document="selectedDocu"
        :instrument="selected"
        :isNewVersion="isNewVersion"
        :accredlvl="$page.props.currentlvl.accredId"
        v-if="storeProgLvl.selected && isUpload"
        @handleClose="closeUploadModal"
    />

    <DocumentViewerModal
        :area="$page.props.current.id"
        :instrument="storeProgLvl.updateSelect"
        :accredlvl="$page.props.currentlvl.accredId"
        v-if="openDocument && storeProgLvl.selected"
        @handleClose="closeViewDocument"
    />

    <ManageVersionModal
        :document="selectedDocu"
        v-if="isManageVersion && selectedDocu && storeProgLvl.selected"
        @handleClose="isManageVersion = false"
        @handleNewVersion="uploadNewVersion"
    />

    <AttatchToEvidenceModal 
        v-if="storeDocument.evidenceAttach.showModal"
        @handle-close="storeDocument.evidenceAttach.showModal = false"
    />

</Layout>
<input 
    type="hidden" 
    :value="storeProgLvl.getInstruments($page.props.instruments)"
>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Crumbs from '../Components/BreadCrumbs.vue'
import HomeButton from '../Components/Buttons/Home.vue'
import BackButton from '../Components/Buttons/Back.vue'
import UploadButton from '../Components/Buttons/Upload.vue'
import FacultyStaffIcon from '../Components/Icons/groupIcon.vue' 
import TaskForceButton from '../Components/Buttons/Taskforce.vue'
import AccreditorButton from '../Components/Buttons/Accreditor.vue'
import Accordion from '../Components/Program/ProgramLevel/Accordion.vue'
import Card from '../Components/Program/ProgramLevel/InstrumentCard.vue'
import InstrumentAside from '../Components/Instrument/InstrumentAside.vue'
import AttatchToEvidenceModal from '../Components/Document/AttachToEvidence.vue'
import DocumentAside from '../Components/Program/ProgramLevel/DocumentAside.vue'
import DocumentViewerModal from '../Components/Document/DocumentViewerModal.vue'
import ManageVersionModal from '../Components/Document/Upload/ManageVersion.vue'
import DocumentUploadModal from '../Components/Document/Upload/DocumentModal.vue'
import FacultyList from '../Components/Program/ProgramLevel/FacultyListModal.vue'
import InstrumentComment from '../Components/Program/ProgramLevel/InstrumentComment.vue'
import TaskForceModal from '../Components/Program/ProgramLevel/TaskForce/TaskfoceModal.vue'
import AccreditorModal from '../Components/Program/ProgramLevel/SelfAccreditor/AccreditorsModal.vue'
import { onMounted, onUnmounted, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { useProglevelStore } from '../Store/storeProgramLvl'
import myMethod from '../Store/Methods'
import { useDocumentStore } from '../Store/storeDocument'

const storeProgLvl = useProglevelStore()
const storeDocument = useDocumentStore()

const isEdit = ref(false)
const selected = ref(null)//Object
const category = ref(null)
const selectedDocu = ref(null) // for edit
const openFacultyStaff = ref(false)
const isAssignTf = ref(false)
const isAssignSA = ref(false)
const isUpload = ref(false)
const accordionContainer = ref(null)
const isComment = ref(false)
const isDocument = ref(false)
const openDocument = ref(false)
const isInstInfo = ref(false)
const isNewVersion = ref(false)
const isManageVersion = ref(false)

const routeInstrument = (program, level, instrument) => {
    storeProgLvl.selected = null
    Inertia.get(`/program/level/prog/${program}/level/${level}/${instrument}`)
}

const routeBack = (program, level, instrument) => {
    if(!instrument)
        Inertia.get(`/program/level/prog/${program}/level/`)
    else
        Inertia.get(`/program/level/prog/${program}/level/${level}/${instrument}`)
}   

const getSelect = (inst) => {
    selected.value = inst
    category.value = inst.category
    storeProgLvl.selected = inst.id
    storeProgLvl.updateSelect = inst
}
const editDocu = docu => {
    isEdit.value = true
    isUpload.value = true
    selectedDocu.value = docu
}
const closeUploadModal = (res=false) => {
    isUpload.value = false
    //isNewVersion.value = false
    if(res){
        CloseDocument()
        isEdit.value = false
    }else{
        isEdit.value ? isEdit.value = false : '';
        isNewVersion.value ? isNewVersion.value = false : '';
    }

    //storeLayout.keepScrollHidden = false
}
const uploadNewVersion = () => {
    isUpload.value = true
    isNewVersion.value = true
    isManageVersion.value = false
    //storeLayout.keepScrollHidden = true
}
const getManageVersion = docu => {
    selectedDocu.value = docu
    isManageVersion.value = true
}
const getInstrumentComment = inst => {
    getSelect(inst)
    isComment.value = true
    isDocument.value = false
    isInstInfo.value = false
}
const closeInstrumentComment = () => {
    selected.value = null
    category.value = null
    storeProgLvl.selected = null
    storeProgLvl.updateSelect = null
    isComment.value = false
}
const showDocument = inst => {
    getSelect(inst)
    isComment.value = false
    isDocument.value = true
    isInstInfo.value = false
}
const CloseDocument = () => {
    selected.value = null
    category.value = null
    storeProgLvl.selected = null
    storeProgLvl.updateSelect = null
    isDocument.value = false
}
const handleInfo = info => {
    getSelect(info)
    isInstInfo.value = true
    isComment.value = false
    isDocument.value = false
}
const closeInstInfo = () => {
    selected.value = null
    category.value = null
    storeProgLvl.selected = null
    storeProgLvl.updateSelect = null
    isInstInfo.value = false
}
const viewDocument = () => {
    openDocument.value = true
}
const closeViewDocument = () => {
    openDocument.value = false
}
const getAssignArea = () => {
    isAssignTf.value = true
}
const closeAssignArea = () => {
    isAssignTf.value = false
}
const getAssignSA = () => {
    isAssignSA.value = true
}
const closeAssignSA = () => {
    isAssignSA.value = false
}
const windowsClick = e => {
    if(!e.target.closest(`#programlvl${storeProgLvl.selected}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal') && !e.target.closest('.itemCard') &&
    !e.target.closest('.ContentEditble') && !e.target.closest('.asideContainer') && !e.target.closest('#optionContainer'))
    {
        storeProgLvl.selected = null
        closeAssignArea()
        closeAssignSA()
        closeInstrumentComment()
        CloseDocument()
        closeViewDocument()

        isInstInfo.value = false
        isComment.value = false
        isDocument.value = false
    }
}
onMounted(()=>{
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
})

</script>