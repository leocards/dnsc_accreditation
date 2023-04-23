<template>
    <Aside :top="top" @handleClose="$emit('handleClose')">
        <template #head>
            <div class="w-full grow flex justify-between items-center pr-2">
                <div>Document </div> 
                <button class="text-xs hover:bg-dnscGreen transition_300 rounded p-0.5 px-1 hover:text-white actionBtn"
                @click="getDocuments">Refresh</button>
            </div>
        </template>

        <div class="flex-col flex p-1.5 gap-1">

            <div 
                :id="`docu${document.id}`"
                class="w-full h-[5.5rem] rounded-md transition_300 flex flex-col cursor-pointer documents"
                :class="[(selectedDocument?
                    selectedDocument.id == document.id ? 'bg-dnscGreen/20 dark:bg-dnscGreen/70' : 'bg-whiteBg dark:bg-tertiaryDarkBg/40 hover:bg-gray-300/80 dark:hover:bg-white/10':
                    'bg-whiteBg dark:bg-tertiaryDarkBg/40 hover:bg-gray-300/80 dark:hover:bg-white/10')]"
                @click.self="viewDocument(index)" v-for="(document, index) in storeDocument.documents"
                @contextmenu="selectDocu(document, index, $page.props.user.auth)"
            >
                <div class="grow flex items-center px-1.5 pointer-events-none">
                    <div class="grow">
                        <div class="Twoline break-words whitespace-pre-wrap max-w-[calc(21rem-1.5rem)]">{{document.title}}</div>
                    </div>

                    <!-- <button type="button" class="w-7 h-7 rounded shrink-0 flex items-center justify-center ml-1.5 
                    hover:bg-dnscGreen/20 hover:text-dnscGreen dark:hover:text-white/70 pointer-events-auto"
                    @click="$emit('handleView')">
                        <View />
                    </button> -->
                </div>
                <div class="w-full h-8 border-t pointer-events-none dark:border-primaryDarkBorder border-slate-300/70 flex items-center justify-between px-1.5 select-none">
                    <div class="flex items-center text-xs gap-1.5">
                        <button 
                        type="button" 
                        class="flex items-center gap-1.5 rounded h-fit p-0.5 select-none px-1.5 pr-2 dark:hover:text-white/70">
                            <div>Comment <span v-if="document.comment_count">∙ {{document.comment_count}}</span></div>
                        </button>
                        <div v-if="$page.props.user.auth != 5" :class="[generateReview(document.review) ?? 'text-gray-400']" v-text="(document.review??'not reviewed')"></div>
                    </div>
                    <div class="select-none">{{document.type}}</div>
                </div>
            </div>

            <div class="flex justify-center py-3" v-if="!documents">
                <Loading />
            </div>

            <div class="dark:text-white/50 text-center" v-if="myMethod.ifArray(storeDocument.documents, '==', 0) && documents">
                No documents
            </div>

        </div>
        
        <Teleport to='body' v-if="auth_ != 5">
            <Options
                :selectedDocument="selectedDocument"
                :isMember="role"
                :isReview="(selectedDocument ?
                    ( isAuthorizeToVerify(selectedDocument.userId == $page.props.user.userId, $page.props.user.auth, $page.url.startsWith('/program')) )
                : false )"
                v-if="auth_ != 5"
                @handleReview="validateDocument"
                @handleViewDocument="viewFromOptions"
                @handleEdit="$emit('handleEdit', selectedDocument)"
                @handleShare="$emit('handleShare', selectedDocument)"
                @handleManageVersion="getManageVersion(selectedDocument)"
            />
        </Teleport>
    </Aside>
    <input type="hidden" :value="getDocuments()">
    
</template>
<script setup>
import Aside from '../../Aside.vue'
import Loading from '../../Loading.vue'
import View from '../../Icons/eyeOpenIcon.vue'
import Options from '../../Document/Context.vue'
import CommentIcon from '../../Icons/smallComment.vue'
import myMethod from '../../../Store/Methods'
import { useDocumentStore } from '../../../Store/storeDocument'
import { onMounted, ref, watchEffect } from 'vue'

const storeDocument = useDocumentStore()

const props = defineProps({
    //documents: Array,
    instrument: Object,
    accredlvl: Number,
    role: Boolean,
    top: String,
    user: Number,
    evidence: Number
})

const emits = defineEmits([
    'handleClose', 
    'handleView',
    'handleEdit',
    'handleManageVersion',
    'handleShare'
])

const auth_ = ref(null)
const documentIndex = ref(null)
const documents = ref(false)
const contextOption = ref(null)
const selectedDocument = ref(null)
//props.documents
//storeDocument.documents = [...(documents.value??)]

const viewDocument = doc => {
    emits('handleView')
    storeDocument.selectDocument(doc)
}
const selectDocu = (docu, index, auth = null) => {
    auth_.value = auth
    if(auth != 5){
        selectedDocument.value = docu
        documentIndex.value = index
    }
}
const viewFromOptions = () => {
    emits('handleView')
    storeDocument.selectDocument(documentIndex.value)
}
const validateDocument = review => {
    axios.post('/document/validate', {
        documentId: selectedDocument.value.id,
        review: review
    }).then(res => {
        storeDocument.documents.find(docu => {
            if(docu.id == selectedDocument.value.id)
                return docu.review = review
        })
        document.getElementById('optionContainer').classList.add('hidden')
    }).catch(error => {
         console.log(error);
    })
}
const getManageVersion = docu => {
    emits('handleManageVersion', docu)
}
const generateReview = value => {
    return value == 'Approve' ? 'text-green-500' :
                value == 'Revise' ? 'text-yellow-600' : 
                    value == 'Reject' ? 'text-red-400' : null
}

const refresh = () => {

}

const getDocuments = () => {
    try {
        storeDocument.documents = []
        documents.value = false
        axios.post('/document/getDocuments', {
            instrument: props.instrument.id,
            accredlvl: props.accredlvl
        }).then(res => {
            //console.table(res.data.documents)
            documents.value = true
            let docus = res.data.documents
            if(storeDocument.evidenceIndex != null) {
                storeDocument.documents = docus.filter((docs, index) => {
                    if(docs.evidence)
                    {
                        return myMethod.isValidJson(docs.evidence).includes(storeDocument.evidenceIndex)
                    }
                })
            }else{
                storeDocument.documents = docus
            }
        })

    } catch (e) {
        console.log(e)
    }
}

const isAuthorizeToVerify = (uploader, auth, url) => {
    if(uploader && ([1,6,4].includes(auth) && url))
        return false
}

const windowsClick = e => {
    try {
        if(!e.target.closest(`#docu${selectedDocument.value.id}`) && !e.target.closest(`#optionContainer`) && !e.target.closest('.modal') && !e.target.closest('.actionBtn')){
            selectedDocument.value = null
        }
    } catch (e) {
        
    }
}

watchEffect(() => {
    if(props.evidence != null){
        getDocuments()
    }
})

onMounted(()=>{
    window.addEventListener('click', windowsClick)
    
})

onMounted(()=> {
    contextOption.value = document.getElementById('optionContainer')
    myMethod.documentContextMenus(contextOption.value)
    storeDocument.documents = []
})

try {
        Echo.private('attach.'+props.user)
            .listen('Attachment', (e) => {
                if(e.attachment.instrument == props.instrument.id && e.attachment.accred == props.accredlvl)
                    storeDocument.documents.push(e.attachment)
            })

    } catch (e) {
        console.log(e)
    }

</script>