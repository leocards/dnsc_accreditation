<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <Link 
            :href="'/document/uploads'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url.startsWith('/document/uploads') ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-2 py-2.5 flex gap-2" title="Your uploads">
                <Folder /> <span class="max-sm:hidden">Your uploads</span>
            </div>
        </Link>
        <Link 
            :href="'/document/share'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url.startsWith('/document/share') ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-2 py-2.5 flex gap-2" title="Shared">
                <Shared /> <span class="max-sm:hidden">Shared</span>
            </div>
        </Link>
    </template>
    <template #subNav>
        <div class="subnavBg border-t border-slate-200 dark:border-primaryDarkBorder py-1.5 px-2 sticky top-14">
            <Search 
                v-model="modelValue"
                placeholder="Search document"
            />
        </div>
    </template>

    
    <div class="pb-2.5">
        <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-2.5 mt-3">
            <Card   
                :selected="(selectedDocu?selectedDocu.id:null)"
                :document="document"
                v-for="(document, index) in documents"
                @click="viewDocument(document, index)"
                @contextmenu="selectDocument(document, index)"
            />
        </div>

        <div class="w-full py-5 flex justify-center" v-if="isLoaded">
            <Loading />
        </div>
    </div>
    
    <DocumentViewerModal
        :area="selectedDocu.area"
        :accredlvl="accredlvl"
        :instrument="instrument"
        v-if="openFile && selectedDocu"
        @handleClose="closeViewDocument"
    />

    <DocumentUploadModal
        :isEdit="isEdit"
        :instrument="(selectedDocu?selectedDocu.instrumentId:null)"
        :document="selectedDocu"
        :isNewVersion="isNewVersion"
        :accredlvl="(selectedDocu?selectedDocu.accredId:null)"
        v-if="isOpenDocumentModal && selectedDocu"
        @handleClose="closeUploadModal"
    />

    <ManageVersionModal
        :document="selectedDocu"
        v-if="isManageVersion && selectedDocu"
        @handleClose="isManageVersion = false"
        @handleNewVersion="uploadNewVersion"
    />

    <Teleport to='body'>
        <Options
            :selectedDocument="selectedDocu"
            @handleViewDocument="viewFromOptions"
            @handleEdit="openDocuModal"
            @handleManageVersion="manageVersion"
        />
    </Teleport>
</Layout>
<input type="hidden" :value="getDocuments($page.props.documents)">
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Search from '../Components/Search.vue'
import Loading from '../Components/Loading.vue'
import Card from '../Components/Document/Card.vue'
import Folder from '../Components/Icons/folder.vue'
import Shared from '../Components/Icons/sharedIcon.vue'
import Options from '../Components/Document/Context.vue'
import ManageVersionModal from '../Components/Document/Upload/ManageVersion.vue'
import DocumentViewerModal from '../Components/Document/DocumentViewerModal.vue'
import DocumentUploadModal from '../Components/Document/Upload/DocumentModal.vue'
//import ShareModal from '../Components/Document/Share.vue'
import { throttle } from 'lodash'
import myMethod from '../Store/Methods'
import { Inertia } from '@inertiajs/inertia'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useDocumentStore } from '../Store/storeDocument'

const storeDocument = useDocumentStore()

const props = defineProps({
    documents: Array,
    search: String
})

const page = ref(2)
const isEdit = ref(false)
const openFile = ref(false)
const isLoaded = ref(props.documents.length < 50 ? false:true)//temporary false, must be true
const accredlvl = ref(null)
const instrument = ref(null)
const element = document.body
const selectedDocu = ref(null)
const isNewVersion = ref(false)
const contextOption = ref(null)
const isManageVersion = ref(false)
const selectedDocuIndex = ref(null)
const modelValue = ref(props.search)//props.searches.search
const isOpenDocumentModal = ref(false)
const lazyLoadIsEmpty = ref(props.documents.length < 50 ? true:false)
const prevPageCount = ref(0);

const loadDocuments = async () => {
    if(!lazyLoadIsEmpty.value)
        isLoaded.value = true

    try {
        let res = null;

        if(props.search){
            res = await axios.get(`/document/load?page=${page.value}&search=${props.search}`)
        }else{
            res = await axios.get(`/document/load?page=${page.value}`)
        }

        const data = await res.data

        if(res.data.length !== 0){
            lazyLoadIsEmpty.value = false

            if(data.length == 50){
                page.value++
            }

            if(prevPageCount.value != page.value){
                isLoaded.value = true
                props.documents.push(...data)
            }

            prevPageCount.value = page.value

            if(data.length < 50){
                lazyLoadIsEmpty.value = true
                isLoaded.value = false
            }

        }else{
            lazyLoadIsEmpty.value = true
            isLoaded.value = false
        }

    } catch (e) {
        //console.log(e.message)
    }
}

const getSelectDocument = docu => {
    selectedDocu.value = docu
    accredlvl.value = docu.accredId
    instrument.value = docu.instrumentId
}

const selectDocument = (docu, index) => {
    getSelectDocument(docu)
    storeDocument.selectDocument(index)
    selectedDocuIndex.value = index
}

const viewFromOptions = () => {
    openFile.value = true
    storeDocument.selectDocument(selectedDocuIndex.value)
}

const viewDocument = (docu, index) => {
    openFile.value = true
    getSelectDocument(docu)
    storeDocument.selectDocument(index)
}

const getDocuments = docus => {
    storeDocument.documents = docus
}

const closeViewDocument = () => {
    openFile.value = false
}

const closeUploadModal = () => {
    isEdit.value = false
    isOpenDocumentModal.value = false
}

const openDocuModal = () => {
    isEdit.value = true
    isOpenDocumentModal.value = true
}

const uploadNewVersion = () => {
    isNewVersion.value = true
    isManageVersion.value = false
    isOpenDocumentModal.value = true
}

const manageVersion = () => {
    isManageVersion.value = true
}

const windowClick = e => {
    if(!e.target.closest(`#docsPage${(selectedDocu.value?selectedDocu.value.id:'')}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal') && !e.target.closest('.optionList   '))
    {
        selectedDocu.value = null, selectedDocuIndex.value = null
        closeUploadModal()
    }
}
const handleScroll = () => {
    if(element.getBoundingClientRect().bottom < window.innerHeight && props.documents.length >= 50){
        loadDocuments()
    }
}
watch(modelValue, throttle(search => {
    if(window.location.pathname == '/document/uploads')
        Inertia.get('/document/uploads', {search: search}, {
            preserveState: true
        })
    else if(window.location.pathname == '/document/share')
        Inertia.get('/document/share', {search: search}, {
            preserveState: true
        })
}, 900))

onMounted(() => {
    window.addEventListener('click', windowClick)
    window.addEventListener("scroll", handleScroll)
    contextOption.value = document.getElementById('optionContainer')
    myMethod.documentContextMenus(contextOption.value)
})
onUnmounted(() => {
    window.removeEventListener("click", windowClick)
    window.removeEventListener("scroll", handleScroll)
})


</script>