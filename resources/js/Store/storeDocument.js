import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useDocumentStore = defineStore('Document', () => {
    const documents = ref([])
    const evidenceIndex = ref(null)
    const selectedDocument = ref(null)//index of document
    const setDocuToRemove = ref(null)
    const attachDocument = ref(null)
    const processAttachment = ref(false)
    const attachDocumentMinimize = ref(false)
    const evidenceAttach = ref({
        accred: null,
        inst: null,
        showModal: false,
    })

    const resetValues = () => {
        documents.value = []
        selectedDocument.value = null
    }

    const selectDocument = doc => {//get document using index
        selectedDocument.value = doc
    }

    const preventReload = e => {
        e.returnValue = "Are you sure you want to leave?";
    }

    const download = () => {
        let link = document.createElement("a");
        link.href = `/download/${currentDocument.id}`
        link.setAttribute("download", "");
        link.style.display = "none";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    const evidenceToAttach = (accred, inst) => {
        evidenceAttach.value.accred = accred
        evidenceAttach.value.showModal = true
        evidenceAttach.value.inst = inst
    }

    const attachHere =  async (accred, inst, flash, index) => {
        try {
            evidenceAttach.value.showModal = false
            processAttachment.value = true
            await axios.post('/attach', {
                accredlvl: accred,
                accred: accred,
                instrument: inst,
                document: attachDocument.value.docuCurrentId,
                evidence: [index]
            })

            processAttachment.value = false
            window.onbeforeunload = null
            attachDocument.value = null
            flash.success = "Attached successfully"
            evidenceAttach.value = {
                accred: null,
                inst: null,
                showModal: false,
            }
        } catch (e) {
            processAttachment.value = false
            window.onbeforeunload = null
            attachDocument.value = null
            //console.log(e.response.data)
            flash.error = e.response.data == 'exist'? "Document already attached" : e.response.data == 'Restricted'?"Restricted":"Failed to attach"
        }
    }

    return {
        documents,
        evidenceIndex,
        evidenceAttach,
        attachDocument,
        setDocuToRemove,
        selectedDocument,
        processAttachment,
        attachDocumentMinimize,

        //methods
        attachHere,
        resetValues,
        preventReload,
        selectDocument,
        evidenceToAttach,
    }
})