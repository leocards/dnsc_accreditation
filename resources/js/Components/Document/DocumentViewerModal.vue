<template>
    <Teleport to='body'>
        <div class="fixed top-0 left-0 w-full h-full bg-black/70 z-[9999] modal" @contextmenu.prevent>

            <div class="h-screen overflow-auto relative"
            :class="[minimizeOrMaximize?' w-[calc(100%-23rem)]':' w-[calc(100%)]']">
                <div class="sticky top-0 right-0 flex justify-end items-center w-full z-20 p-2 gap-2 text-white/80">
                    <ZoomIn @click="zoomIn(currentDocument.type)" :disabled="disableZIn" title="Zoom in" />
                    <ZoomOut @click="zoomOut(currentDocument.type)" :disabled="disableZOut" title="Zoom out" />
                    <FullScreen title="Full Screen" v-if="minimizeOrMaximize" @click="minimizeOrMaximize = false" />
                    <MinimizeScreen title="Minimize Screen" v-else  @click="minimizeOrMaximize = true" />
                    <Close title="Close" v-if="!minimizeOrMaximize" :newBg="'bg-gray-300/20 hover:bg-dnscGreen h-10 w-10'" @click="close" />
                </div>

                <div class="h-fit flex justify-center" v-if="(supportedFiles.includes(currentDocument.type))">
                    <!-- <iframe :src="'/storage/files/20221227131524.pdf'" v-if="false" class="w-full h-screen grow" frameborder="0"></iframe> -->
                    <div class="grow flex justify-center" v-if="currentDocument.type == 'pdf'">
                        <VuePdfEmbed :source="`/storage/files/${currentDocument.file}`" @rendered="loaded" :width="pdfWidth" />
                    </div>
                    
                    <div class="max-w-[120vh] w-full mt-5"
                    v-if="supportedImages.includes(currentDocument.type)">
                        <img :src="`/storage/files/${currentDocument.file}`" alt="" class="object-cover w-full">
                    </div>

                    <div class="flex items-center w-full justify-center mt-10" v-if="currentDocument.type == 'mp4'">
                        <video class="w-[130vh]" controlsList controls :src="`/storage/files/${currentDocument.file}`"></video>
                    </div>
                </div>
                <div 
                    class="w-[28rem] py-4 rounded-md subnavBg h-[15rem] mx-auto flex flex-col justify-center items-center"
                    v-else
                >

                    <span v-if="currentDocument.type != 'link'">Can't preview file </span>

                    <a :href="`/download/${currentDocument.id}`" v-if="currentDocument.type != 'link'">
                        <button class="text-white mt-5 rounded-md bg-dnscGreen p-2 px-3">
                            Download
                        </button>
                    </a>

                    <a :href="currentDocument.file" target="_blank" v-if="currentDocument.type == 'link'">
                        <button class="text-white rounded-md bg-dnscGreen p-2 px-3">
                            Open link
                        </button>
                    </a>

                </div>
            </div>

            <div class="w-[23rem] fixed h-screen top-0 right-0 shrink-0 border-l border-slate-300/80 dark:border-primaryDarkBorder subnavBg flex flex-col" v-if="minimizeOrMaximize">
                <Header 
                    :current="storeDocument.selectedDocument"
                    :documentlength="storeDocument.documents.length"
                    @handleClose="close" 
                    @handleNext="nextDocument" 
                    @handlePrevious="prevDocument"
                />

                <div class="min-h-[7.5rem] border-b shrink-0 border-slate-300/80 dark:border-primaryDarkBorder pt-1 flex flex-col"
                :class="[showComment?'max-h-[19rem]':(!isViewDetails ? '':'grow')]">
                    <div class="p-1.5 grow h-full overflow-y-auto">
                        <div>
                            <div class="text-xs Segoe font-medium">Title</div>
                            <div class="dark:text-white/60 inter-font mt-1">{{currentDocument.title}}</div>
                        </div>
                        <div v-if="isViewDetails">
                            <div class="mt-4">
                                <div class="text-xs Segoe font-medium">Description</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    {{currentDocument.description}}
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-xs Segoe font-medium">Type</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    {{currentDocument.type}}
                                </div>
                            </div>
                            <div class="mt-4" v-if="$page.props.user.auth != 5">
                                <div class="text-xs Segoe font-medium">Review</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    {{(currentDocument.review??'not reviewed')}}
                                </div>
                            </div>
                            <div class="mt-4" v-if="$page.props.user.auth != 5">
                                <div class="text-xs Segoe font-medium">Uploaded by</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    {{currentDocument.get_user.first_name+' '+currentDocument.get_user.last_name}}
                                </div>
                            </div>
                            <div class="mt-4" v-if="$page.props.user.auth != 5">
                                <div class="text-xs Segoe font-medium">Date Uploaded</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    <DateTime :time="currentDocument.created_at" />
                                </div>
                            </div>
                            <div class="mt-4" v-if="$page.props.user.auth != 5">
                                <div class="text-xs Segoe font-medium">Date Modified</div>
                                <div class="dark:text-white/60 inter-font mt-1">
                                    <DateTime :time="currentDocument.updated_at" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 p-1 flex gap-1 actionBtn">
                        <button
                            class="text-xs w-full py-1.5 rounded hover:text-white/80 transition_300 
                             hover:bg-dnscGreen hover:dark:bg-dnscGreen actionBtn"
                            :class="[isViewDetails?'bg-dnscGreen text-white/80':'border border-slate-300/80 dark:border-primaryDarkBorder hover:dark:border-transparent hover:border-transparent']"
                            @click="toggleViewDetails"
                            v-text="(isViewDetails?'Hide Details':'View Details')"
                        ></button>
                    </div>
                </div>

                <div class="flex flex-col overflow-y-auto"
                :class="[showComment || !isViewDetails?'grow':'h-fit']">
                    <div class="p-1 flex justify-between items-center">
                        <div>Comments</div>    
                        <button 
                            type="buttton" 
                            class="p-1 px-2 rounded hover:bg-dnscGreen transition_300 hover:text-white/80 actionBtn" 
                            @click="toggleComments"
                            v-text="(showComment ? 'Hide' : 'Show')"
                        ></button>

                    </div>
                    <div class="flex flex-col-reverse p-1 gap-2" v-if="showComment">
                        <CommentCard :comment="comment" v-for="comment in comments" />
                    </div>
                    <div class="dark:text-white/50 text-center" v-if="myMethod.ifArray(comments, '==', 0) && showComment">
                        No comments
                    </div>
                    <div  v-if="!showComment" class="dark:text-white/50 text-center pb-5 pt-2">Comments hidden</div>
                </div>

                <div 
                    class="w-full min-h-[3.1rem] max-h-[9rem] p-2 shrink-0 border-t dark:border-primaryDarkBorder border-slate-300 relative"
                    v-if="showComment"
                >
                    <InputMessage placeholder="Comment" @handleMesage="send" @submitButton="send" />
                </div>
            </div>

        </div>
    </Teleport>
</template>

<script setup>
import VuePdfEmbed from 'vue-pdf-embed'
import ZoomIn from '../Buttons/ZoomIn.vue'
import Close from '../Buttons/XButton.vue'
import ZoomOut from '../Buttons/ZoomOut.vue'
import CommentCard from '../CommentCard.vue'
import Header from './DocumentViewer/Header.vue'
import InputMessage from '../ContentEditable.vue'
import FullScreen from '../Buttons/FullScreen.vue'
import MinimizeScreen from '../Buttons/MinimizeScreen.vue'
import myMethod from '../../Store/Methods'
import DateTime from '../DateTime.vue'
import { onBeforeMount, onUnmounted, ref } from '@vue/runtime-core'
import { useDocumentStore } from '../../Store/storeDocument'

const storeDocument = useDocumentStore()

const props = defineProps({
    instrument: Object,
    accredlvl: Number,
    area: Number
})
const emits = defineEmits(['handleClose'])

const showComment = ref(true)
const isViewDetails = ref(false)
const isViewActivty = ref(false)
const currentDocument = ref(null)
const pdfWidth = ref(1000)
const imgW = ref(100)
const minimizeOrMaximize = ref(true)
const comments = ref(null)
const disableZIn = ref(true)
const disableZOut = ref(true)

const supportedImages = ['png', 'jpeg', 'jpg', 'jfif']
const supportedFiles = ['png', 'jpeg', 'jpg', 'jfif', 'pdf', 'mp4']

const close = () => {
    emits('handleClose')
}

const toggleViewDetails = () => {
    isViewDetails.value = !isViewDetails.value
}
const toggleComments = () => {
    showComment.value = !showComment.value
}
const zoomIn = type => {
    if(type == 'pdf'){
        pdfWidth.value += 100
        if(pdfWidth.value == 1100){
            disableZIn.value = true
        }else{
            disableZIn.value = false
        }
        disableZOut.value = false
    }
    disableZIn.value = true
}

const zoomOut = type => {
    if(type == 'pdf'){
        pdfWidth.value -= 100
        if(pdfWidth.value == 900){
            disableZOut.value = true
        }else{
             disableZOut.value = false
        }
        disableZIn.value = false
    }
    disableZOut.value = true
}

currentDocument.value = myMethod.findElement(storeDocument.documents, storeDocument.selectedDocument)

const nextDocument = () => {
    
    let docu = storeDocument.documents.length
    if(storeDocument.selectedDocument <= docu)
    {
        if(docu == 1){
            return null
        }

        if(storeDocument.selectedDocument != --docu){
            let doc = myMethod.findElement(storeDocument.documents, ++storeDocument.selectedDocument)

            if(doc.type == 'link')
                window.open(doc.file, "_blank");

            currentDocument.value = doc
        }
    }else{
        return true
    }
    getComments()
    disableZIn.value = true
    disableZOut.value = true
}
const prevDocument = () => {
    if(storeDocument.selectedDocument != 0)
    {
        currentDocument.value = myMethod.findElement(storeDocument.documents, --storeDocument.selectedDocument)
    }else{
        return true
    }
    getComments()
    disableZIn.value = true
    disableZOut.value = true
}
const send = val => {
    if(val){
        if(val.trim().length > 0){
            axios.post('/document/comment', {
                instrumentId: props.instrument.id,
                accredlvl: props.accredlvl,
                documentId: currentDocument.value.docuCurrentId,
                comment: val,
                area: props.area
            }).then(res => {
                comments.value.push(res.data.response)
            }).catch(error => {
                //console.log(error.response.data)
            })
        }
    }
}
const getComments = () => {
    try {
        axios.post('/document/get/comment', {
            documentCurrentId: currentDocument.value.docuCurrentId,
        }).then(res => {
            comments.value = res.data.comments
        }).catch(error => {

        })
    } catch (e) {

    }
}
getComments()

document.body.style.overflow = 'hidden'
onUnmounted(() => {
    document.body.style.overflow = ''
})

function loaded(e) {
    if(pdfWidth.value != 1100 && pdfWidth.value != 900){
        disableZIn.value = false
        disableZOut.value = false
    }
}

</script>
