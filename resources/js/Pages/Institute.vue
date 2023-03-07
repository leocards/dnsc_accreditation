<template>
<Layout :user="$page.props.user">
    <template #subNav>
        <div class="w-full py-1.5 flex gap-3 sticky top-14 subnavBg dark:border-primaryDarkBorder border-t border-slate-200 px-2.5">
            <CreateButton @click="openCreate = true" />
            <EditButton :disabled="!selected" @click="edit" />
            <InfoButton :disabled="!selected" @click="isView = true" v-if="false" />
        </div>
    </template>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-4">
        <InstituteCard 
            :selected="selected" 
            :institute="institute"
            @click="selected = institute.id"
            v-for="institute in $page.props.institutes" 
        />
    </div>

    <Modal 
        v-if="openCreate" 
        @handle-close="openCreate = false, selected = null, isEdit = false" 
        :isEdit="isEdit" 
        :selected="selected" 
        :institutes="$page.props.institutes"
    />

    <InfoModal
        v-if="isView"
        @handle-close="isView = false"
    />
</Layout>
</template>

<script setup>  
import Layout from '../Components/Layout.vue'
import InfoButton from '../Components/Buttons/Info.vue'
import EditButton from '../Components/Buttons/Edit.vue'
import Modal from '../Components/Institute/CreateModal.vue'
import CreateButton from '../Components/Buttons/Create.vue'
import InstituteCard from '../Components/Institute/Card.vue'
import InfoModal from '../Components/Institute/InstituteDetails.vue'
import { onMounted, onUnmounted, ref } from 'vue'

const isEdit = ref(false)
const isView = ref(false)
const selected = ref(null)
const openCreate = ref(false)

const edit = () => {
    isEdit.value = true
    openCreate.value = true
}

const windowClick = (e) => {
    if(!e.target.closest(`#institute${selected.value}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal'))
    {
        //clear selected
        selected.value = null
    }
}

onMounted(()=>{
    window.addEventListener('click', windowClick)
})

onUnmounted(()=>{
    window.removeEventListener('click', windowClick)
})

</script>