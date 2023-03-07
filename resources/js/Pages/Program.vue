<template>
<Layout :user="$page.props.user">
    <template #subNav>
        <div class="w-full py-1.5 flex gap-3 sticky top-14 subnavBg dark:border-primaryDarkBorder border-t border-slate-200 px-2.5">
            <CreateButton @click="create" :disabled="$page.props.user.auth == 4" />
            <EditButton :disabled="!selected || $page.props.user.auth == 4" @click="edit" />
            <InfoButton :disabled="!selected" v-if="false" />
        </div>
    </template>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-4">
        <ProgramCard 
            :program="program"
            :selected="selected"
            @click="selected = program.id"
            @dblclick="route(program.id)"
            @handleRoute="route(program.id)"
            v-for="program in $page.props.programs"
        />
    </div>

    <Modal 
        :isEdit="isEdit"
        :selected="selected"
        :programs="$page.props.programs"
        v-if="openCreate" 
        @handle-close="openCreate = false" 
        @update="update" 
    />
</Layout>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import InfoButton from '../Components/Buttons/Info.vue'
import EditButton from '../Components/Buttons/Edit.vue'
import ProgramCard from '../Components/Program/Card.vue'
import Modal from '../Components/Program/CreateModal.vue'
import CreateButton from '../Components/Buttons/Create.vue'
import { onMounted, onUnmounted, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const isEdit = ref(false)
const isView = ref(false)
const selected = ref(null)
const openCreate = ref(false)

const create = () => {
    isEdit.value = false
    selected.value = null
    openCreate.value = true
}

const edit = () => {
    isEdit.value = true
    openCreate.value = true
}

const update = () => {
    isEdit.value = false
    openCreate.value = false
}

const route = program => {
    Inertia.get(`/program/level/prog/${program}/level/`)
}

const windowsClick = (e) => {
    if(!e.target.closest(`#program${selected.value}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal'))
    {
        //clear selected
        selected.value = null
        //close modal
        update()

    }
}

onMounted(()=>{
    window.addEventListener('click', windowsClick)
})

onUnmounted(()=>{
    window.removeEventListener('click', windowsClick)
})

</script>