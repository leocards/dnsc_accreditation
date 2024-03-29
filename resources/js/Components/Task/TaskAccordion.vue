<template>
    <AccordionContainer>
        <div class="w-full min-h-[2rem] rounded-md border border-transparent flex items-center justify-between p-2 transition duration-300"
            :id="`taskAccordion${inst.id}`"
            :class="
                {'hover:bg-gray-300/80 hover:dark:bg-white/10' : storeTaskPage.selected != inst.id && !openDropDown},
                [storeTaskPage.selected !== inst.id && !openDropDown && isRoot ? 'dark:border-transparent':'dark:border-white/10 border-gray-200'],
                [storeTaskPage.selected == inst.id && openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeTaskPage.selected == inst.id && !openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeTaskPage.selected != inst.id && openDropDown?'bg-dnscGreen/30 dark:bg- dark:text-green-400 text-dnscGreen dark:border-green-500/80 border-dnscGreen/80':'']
            "
            @contextmenu.self=""
            @click.self="getSelected(inst)"
        >
            <Content :inst="inst" @handleEvidence="getDocument" :active="(storeTaskPage.selected == inst.id)" />

            <div class="w-fit h-fit shrink-0 flex gap-2 items-center ml-1">
                <div class="flex gap-2 items-center" v-if="!storeDocument.attachDocument">
                    <CompleteButton
                        :complete="inst.progress.isComplete"
                        :title="(inst.progress.isComplete?'Unmark as complete':'Mark as complete')" 
                        v-if="inst.category == 'item' && $page.props.areaRole"
                        @click="toggleMarkComplete(inst, inst.progress.isComplete)"
                    />
                    <CommentButton title="Comment" :comments="inst.comment" @click="getComment(inst)" />
                    <div class="relative" v-click-outside="closeOptions">
                        <MoreH title="More" StyleMoreMenu="hover:bg-black/10" @click="openOptions = true"/>
                        <Options 
                            v-if="openOptions" 
                            :isTask="true"
                            :isItem="inst.category == 'item'" 
                            :isParameter="inst.category == 'param' || inst.category == 'item'"
                            :isAssign="(myMethod.role($page.props.areaRole) ?true : false)"
                            @handleDocument="getDocument(inst)"
                            @handleAssign="handleAssign(inst)"
                            @handleInfo="getInstrumentInfo(inst)"
                            @handleEvidence="getEvidence(inst)"
                        />
                    </div>
                </div>
                <AttachButton 
                    v-if="storeDocument.attachDocument && (inst.category == 'item')" 
                    @click="storeDocument.evidenceToAttach($page.props.home.id, inst)"
                />
                <DownButton 
                    title="Show more"
                    @click="openDropDown = !openDropDown" 
                    :disabled="inst.children.length === 0"
                />
            </div>
        </div>

        <div class="w-full h-fit pl-6 relative bg-white dark:bg-secondaryDarkBg"
        v-for="(instrument, index) in inst.children" v-if="openDropDown">
            <div class="absolute top-0 left-2.5 h-7 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-2.5 h-full w-5 border-l-2 border-green-600" 
            v-if="(inst.children.length-1) != index"></div>

            <div class="pt-1.5">
                <TaskAccordion 
                    :inst="instrument" 
                    @resetItem="resets"
                    @handleSelect="getSelected" 
                    @handleComment="getComment"  
                    @handleAssign="handleAssign"
                    @handleEvidence="getEvidence"
                    @handleDocument="getDocument"
                    @handleInfo="getInstrumentInfo"
                    @handleResetSelect="resetSelected"
                    @handleMarkComplete="toggleMarkComplete"
                />
            </div>
        </div>
    </AccordionContainer>
</template>
<script setup>
import Content from '../Instrument/Content.vue'
import AttachButton from '../Buttons/Attach.vue'
import MoreH from '../Buttons/MeatballsMenuH.vue'
import AccordionContainer from '../Accordion.vue'
import DownButton from '../Buttons/DownButton.vue'
import CommentButton from '../Buttons/Comment.vue'
import CompleteButton from '../Buttons/Complete.vue'
import Options from '../Program/ProgramLevel/InstrumentOption.vue'
import { ref } from '@vue/reactivity'
import myMethod from '../../Store/Methods'
import { onMounted, onUnmounted } from '@vue/runtime-core'
import { useTaskPageStore } from '../../Store/storeTaskPage'
import { useDocumentStore } from '../../Store/storeDocument'

const storeTaskPage = useTaskPageStore()
const storeDocument = useDocumentStore()

const props = defineProps({
    inst: Object,
    isRoot: Boolean,
})
const emits = defineEmits([
    'handleSelect', 
    'handleCreate', 
    'handleDocument',
    'handleComment', 
    'handleAssign',
    'handleResetSelect',
    'resetItem',
    'handleMarkComplete',
    'handleInfo',
    'handleEvidence'
])

const openOptions = ref(false)
const openDropDown = ref(false)
const expand = ref(false)

const closeOptions = () => {
    openOptions.value = false
}
const getSelected = (inst) => {
    storeDocument.evidenceIndex = null
    emits('handleSelect', inst)
}
const getComment = inst => {
    emits('handleComment', inst)
}
const resetSelected = inst => {
    emits('handleResetSelect', inst)
}
const getDocument = (inst, evidence = null) => {
    storeDocument.evidenceIndex = evidence
    emits('handleDocument', inst, evidence)
    setTimeout(()=>{
        closeOptions()
    }, 1)
}
const handleAssign = val => {
    emits('handleAssign', val)
    setTimeout(()=>{
        closeOptions()
    }, 1)
}
const resets = val => {
    emits('resetItem', val)
}
const toggleMarkComplete = (inst, bool) => {
    if(!bool || bool == null)
    {
        emits('handleMarkComplete', inst, bool)
    }else{
        emits('handleMarkComplete', inst, bool)
    }
}
const getInstrumentInfo = inst => {
    emits('handleInfo', inst)
    setTimeout(()=>{
        closeOptions()
    }, 1)
}
const getEvidence = inst => {
    emits('handleEvidence', inst)
    setTimeout(()=>{
        closeOptions()
    }, 1)
}

onUnmounted(() => {
    resetSelected(props.inst)
    resets(props.inst.id)
})
</script>