import axios from "axios";
import { ref } from "vue";
import { defineStore } from "pinia"

export const useDocumentStore = defineStore('Document', () => {
    const documents = ref([])
    const selectedDocument = ref(null)//index of document
    const setDocuToRemove = ref(null)
    const attachDocument = ref(null)
    const processAttachment = ref(false)
    const attachDocumentMinimize = ref(false)

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

    const attachHere =  async (accred, inst, flash) => {

        try {
            processAttachment.value = true
            let res = await axios.post('/attach', {
                accredlvl: accred,
                accred: accred,
                instrument: inst,
                document: attachDocument.value.docuCurrentId,
            })

            processAttachment.value = false
            window.onbeforeunload = null
            attachDocument.value = null
            flash.success = "Attached successfully"
        } catch (e) {
            processAttachment.value = false
            window.onbeforeunload = null
            attachDocument.value = null
            flash.error = e.response.data == 'exist'? "Document already attached" : e.response.data == 'Restricted'?"Restricted":"Failed to attach"
        }
    }

    return {
        documents,
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
    }
})