<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            <span v-if="!isEdit && !isNewVersion">Upload</span>
            <span v-if="isEdit && !isNewVersion">Edit</span>
            <span v-if="!isEdit && isNewVersion">New Version of</span>
            Document
        </template>

        <div class="absolute top-2 h-10 w-[15rem] left-1/2 -translate-x-1/2 bg-red-400 rounded-md py-2 text-center" v-if="isRestricted">
            {{(isRestricted.replace(/"/g,''))}}
        </div>

        <div class="text-xl" v-if="!isEdit">
            " {{instrument.title}} "
        </div>

        <form @submit.prevent="submit" class="mt-5">
            <div v-if="!isEdit">
                <div class="mb-3">
                    <button type="button" 
                    class="bg-dnscGreen/80 p-1 px-2 dark:text-white/80 text-white hover:bg-dnscGreen transition_300 hover:shadow-md rounded actionBtn"
                    v-if="!isUrl"
                    @click="isUrl=true">Use link</button>

                    <button type="button" 
                    class="bg-dnscGreen/80 p-1 px-2 dark:text-white/80 text-white hover:bg-dnscGreen transition_300 hover:shadow-md rounded actionBtn"
                    v-else
                    @click="isUrl=false">Choose file</button>
                </div>

                <!-- file input -->
                <div class="relative" >
                    <FilePond
                        className="filepond--panel-root filepond--drop-label"
                        ref="pond"
                        max-files="1"
                        credits="false"
                        name="fileUpload"
                        maxFileSize="20MB"
                        allow-multiple="false"
                        :server="{
                            url: '',
                            timeout: 7000,
                            process: {
                                url: '/document/tmp/'+accredlvl,
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $page.props.ct,
                                },
                                withCredentials: false,
                                onload: handleFilepondLoad,
                                onerror: (err) => { errorFilePond(err) }
                            },
                            revert: handleFilepondRevert
                        }"

                        v-if="!isUrl"
                    />
                </div>

                <!-- Url input -->
                <div class="" v-if="isUrl">
                    <span class="errorMessage ml-2" v-show="documentForm.errors.link">{{documentForm.errors.link}}</span>
                    <input type="url" class="formInput" v-model="documentForm.link" placeholder="Attach link here">
                </div>
            </div>

            <div class="mt-3 w-[30rem]" v-if="(myMethod.isValidJsonAndEmpty(instrument.attachment) || instrument.attachment) && !isNewVersion">
                <label for="title">Selected evidence to attach</label>
                <div 
                    class="w-full mt-2 h-10 pr-12 pl-3 select-none items-center flex border-slate-300 rounded border dark:bg-secondaryDarkBg dark:border-primaryDarkBorder cursor-pointer relative"
                    v-click-outside="closeOption"
                    @click.self="isShowOpetion = !isShowOpetion"
                    v-if="myMethod.isValidJson(instrument.attachment)"
                >
                    <div class="Oneline pointer-events-none" v-if="documentForm.evidenceIndex.evidence"> {{ documentForm.evidenceIndex.evidence }} </div>
                    <div class="text-slate-400 pointer-events-none" v-else>Select</div>

                    <div 
                        class="absolute top-10 left-0 bg-white w-full rounded shadow-md border border-slate-300 dark:bg-secondaryDarkBg dark:border-primaryDarkBorder select-none"
                        v-if="isShowOpetion"
                    >
                        <div 
                            class="py-1.5 hover:bg-gray-100 itemCard" 
                            v-for="(item, index) in myMethod.isValidJson(instrument.attachment)" 
                            :key="index"
                            @click="selectOptionEvidence(item.evidence, index)"
                        >
                            <div 
                                class="Oneline px-1.5 h-fit" 
                            >{{ item.evidence }}</div>
                        </div>
                    </div>

                    <div class="absolute top-2.5 right-2 pointer-events-none">
                        <DownIcon />
                    </div>
                </div>

                <div v-else
                    class="w-full mt-2 h-10 pr-12 pl-3 select-none items-center flex border-slate-300 rounded border dark:bg-secondaryDarkBg dark:border-primaryDarkBorder"
                >
                    {{ instrument.attachment }}
                </div>
            </div>

            <div class="mt-3">
                <label for="title">Title</label>
                <span class="errorMessage ml-2" v-show="documentForm.errors.title">{{documentForm.errors.title}}</span>
                <input type="text" class="formInput mt-2" id="title" placeholder="Title" v-model="documentForm.title">
            </div>

            <div class="mt-4">
                <label for="description">Description</label>
                <span class="errorMessage ml-2" v-show="documentForm.errors.description">{{documentForm.errors.description}}</span>
                <textarea id="description" placeholder="Description" class="textareaForm mt-2" v-model="documentForm.description"></textarea>
            </div>

            <div class="mt-7 mb-3 flex justify-end gap-2">
                <Submit width="min-w-[8rem]">
                    <span v-if="isEdit && !isNewVersion">Update</span>
                    <span v-if="!isEdit && !isNewVersion">Upload</span>
                    <span v-if="!isEdit && isNewVersion" class="whitespace-nowrap">Upload new version</span>
                </Submit>
            </div>

        </form>
        
    </Modal>
</template>
<script setup>
import "filepond/dist/filepond.min.css"
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css"
import Modal from '../../Modal.vue'
import vueFilePond from "vue-filepond"
import Submit from '../../Buttons/Submit.vue'
import Cancel from '../../Buttons/Cancel.vue'
import DownIcon from '../../Icons/downIcon.vue'
import FilePondPluginImagePreview from "filepond-plugin-image-preview"
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type"
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
import axios from "axios"
import myMethod from '../../../Store/Methods.js'
import { useForm } from "@inertiajs/inertia-vue3"
import { onMounted, ref } from "@vue/runtime-core"
import { Inertia } from "@inertiajs/inertia"

const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

const props = defineProps({
    instrument: Object,
    accredlvl: Number,
    document: Object,
    isEdit: Boolean,
    isNewVersion: Boolean,
})
const emits = defineEmits(['handleClose'])//;console.log(props.instrument)

const isShowOpetion = ref(false)
const isUrl = ref(false)
const documentForm = useForm({
    link: null,
    title: null,
    tempId: null,
    parent: null,
    description: null,
    documentType: null,
    docuCurrentId: null,
    accredlvl: props.accredlvl, 
    instrument: props.instrument.id,
    evidenceIndex: {
        index: [],
        evidence: null
    }
})
const isRestricted = ref(null)

const closeOption = () => {
    isShowOpetion.value = false
}
const selectOptionEvidence = (evidence, indx) => {
    documentForm.evidenceIndex.index[0] = indx
    documentForm.evidenceIndex.evidence = evidence
    closeOption()
}

const create = () => {
    documentForm.documentType = isUrl.value ? 'link' : documentForm.documentType
    documentForm.post('/document/upload', {
        onSuccess: page => {
            if(page.props.flash.success)
            {
                emits('handleClose', true)
            }else{
                console.log(page.props.flash.error)
            }
        }
    })
}

const update = () => {
    Inertia.post('/document/update',
    {
        id: props.document.id,
        title: documentForm.title,
        description: documentForm.description,
        accredlvl: documentForm.accredlvl,
        instrument: documentForm.instrument,
        evidenceIndex: documentForm.evidenceIndex
    }, {
        onSuccess: () => {
            emits('handleClose', true)
        }
    })
}

const newVersion = () => {
    documentForm.documentType = isUrl.value ? 'link' : documentForm.documentType
    documentForm.parent = props.document.id
    documentForm.docuCurrentId = props.document.docuCurrentId
    documentForm.post('/document/upload/ne_version', {
        onSuccess: () => {
            emits('handleClose', true)
        }
    })
}

const submit = () => {
    if(!props.isEdit && !props.isNewVersion){
        create()
    }else if(props.isEdit && !props.isNewVersion){
        update()
    }else if(!props.isEdit && props.isNewVersion){
        newVersion()
    }
}

const handleFilepondLoad = (res) => {
    let result = JSON.parse(res)

    if(!documentForm.title) {
        documentForm.title = result.file_name
    }
    documentForm.tempId = result.id
    documentForm.documentType = result.file_type
}

const handleFilepondRevert = (uniqueId, load, error) => {
    axios.post(`/document/tmp_revert`, documentForm)
    .then(res => {
        if(res.data.revert == 'success')
        {
            documentForm.reset('tempId', 'title', 'documentType')
        }else{
            console.log(res.data.revert)
        }
    })
}

const errorFilePond = error => {
    isRestricted.value = error
    setTimeout(()=>{
        isRestricted.value = null
    }, 3000)
}

onMounted(() => {
    try{
        if(!props.isNewVersion) {
            if(props.isEdit){
                if(props.document.evidence){
                    let evd = JSON.parse(props.document.evidence)[0]
                    if(myMethod.isValidJsonAndEmpty(props.instrument.attachment)) {
                        documentForm.evidenceIndex.index[0] = evd
                        documentForm.evidenceIndex.evidence = myMethod.isValidJson(props.instrument.attachment)[evd].evidence
                    }
                }else{
                    if(myMethod.isValidJsonAndEmpty(props.instrument.attachment)) {
                        documentForm.evidenceIndex.index[0] = 0
                        documentForm.evidenceIndex.evidence = myMethod.isValidJson(props.instrument.attachment)[0].evidence
                    }
                }
            } else if(myMethod.isValidJsonAndEmpty(props.instrument.attachment)) {
                documentForm.evidenceIndex.index[0] = 0
                documentForm.evidenceIndex.evidence = myMethod.isValidJson(props.instrument.attachment)[0].evidence
                console.log()
            }
        }
    } catch (e) {
    }
})
//if edit
if(props.isEdit){
    documentForm.title = props.document.title
    documentForm.description = props.document.description
}else{
    documentForm.reset()
}
</script>

<style scoped>
select option {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>