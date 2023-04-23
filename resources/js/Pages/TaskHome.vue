<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <NavTabs />
    </template>
    <template #subNav>
        <div class="w-full py-1.5 flex gap-1.5 sticky top-14 subnavBg border-t dark:border-primaryDarkBorder border-slate-200 px-2.5">
            <button type="button" class="h-10 rounded-full transition_300 px-3 
            dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            :class="[$page.props.currentLevel.id == level.accredId ? 'bg-dnscGreen text-white' : 'dark:bg-tertiaryDarkBg bg-black/10 hover:bg-black/20 dark:hover:bg-white/20']"
            v-for="level in $page.props.programLevels"
            @click="route($page.props.currentProgram.id, level.accredId)">
                {{level.title}}
            </button>
        </div>
    </template>

    <div class="w-full" ref="accordionContainer">
        <div class="w-full mt-3 subnavBg rounded-md p-2 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] taskContainer"
        v-for="area in $page.props.areas">
            
            <AreaContent :area="area" @handleDrillDown="getDrillDown" />

            <div class="mt-4" v-if="!area.role">
                <Tasks
                    :area="area.id"
                    :assigned="area.assigned"
                />
            </div>
            
        </div>
    </div>

    <DrilledDown
        :area="areaDrillDown"
        :accredId="$page.props.currentLevel.id"
        v-if="isDrillDown && areaDrillDown"
        @handleClose="closeDrillDown"
    />
</Layout>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import NavTabs from '../Components/Task/NavTabs.vue'
import Tasks from '../Components/Task/TaskHome/Tasks.vue'
import DownButton from '../Components/Buttons/DownButton.vue'
import DrilledDown from '../Components/Task/TaskPage/DrillDown.vue'
import AreaContent from '../Components/Task/TaskHome/AreaContent.vue'
import DocumentUploadModal from '../Components/Document/Upload/DocumentModal.vue'
import myMethod from '../Store/Methods'
import { onMounted, onUnmounted, ref } from 'vue'
import { useTaskPageStore } from '../Store/storeTaskPage'
import { Inertia } from '@inertiajs/inertia'

const storeTaskPage = useTaskPageStore()

const props = defineProps({
    currentLevel: Object
})

const isDrillDown = ref(false)
const areaDrillDown = ref(null)
const accordionContainer = ref(null)

const getDrillDown = area => {
    isDrillDown.value = true
    areaDrillDown.value = area
}
const closeDrillDown = () => {
    isDrillDown.value = false
    areaDrillDown.value = null
}

const route = (program, level) => {
    Inertia.get(`/task?program=${program}&level=${level}`)
}

const windowsClick = e => {
    if(!e.target.closest(`#taskAccordion${storeTaskPage.selected}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal') && !e.target.closest('.itemCard'))
    {
        areaDrillDown.value = null
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
    storeTaskPage.reset()
})

</script>