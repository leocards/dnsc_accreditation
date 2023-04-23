<template>
    <Remove @handleClose="storeDocument.setDocuToRemove = null" @handleCancel="storeDocument.setDocuToRemove = null" @handleDelete="deleteDocu">
        
        <div class="mx-auto flex h-24 items-center ">
            You're about to remove " {{storeDocument.setDocuToRemove.title}} "
        </div>

        <template #deleteLabel>
            <div>
                Remove
            </div>
        </template>
    </Remove>
</template>
<script setup>
import Remove from '../RemoveModal.vue'
import { useDocumentStore } from '../../Store/storeDocument'
import { onMounted, onUnmounted } from '@vue/runtime-core'
import { Inertia } from '@inertiajs/inertia'

const storeDocument = useDocumentStore()

const deleteDocu = () => {
    Inertia.post('/document/remove', {
        id: storeDocument.setDocuToRemove.docuCurrentId,
        isAttached: 'isAttached' in storeDocument.setDocuToRemove ? storeDocument.setDocuToRemove.isAttached : null
    }, {
        preserveScroll: true,
        onSuccess: page => {
            if(page.props.flash.success){
                storeDocument.documents = storeDocument.documents.filter(({id}) => id != storeDocument.setDocuToRemove.id)
                storeDocument.setDocuToRemove = null
            }
        }
    })
}

const windowsClick = (e) => {
    if(!e.target.closest('.modal') && !e.target.closest('#optionContainer'))
    {
        storeDocument.setDocuToRemove = null
    }
}
onMounted(()=>{
    window.addEventListener('click', windowsClick)
})
onUnmounted(()=>{
    window.removeEventListener('click', windowsClick)
})

</script>