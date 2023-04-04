<template>
    <AccordionContainer>
        <div class="w-full min-h-[2rem] rounded-md border border-transparent flex items-center justify-between p-2 transition duration-300"
            :id="`programlvl${inst.id}`"
            :class="
                {'hover:bg-gray-300/80 hover:dark:bg-white/10' : storeProgLvl.selected != inst.id && !openDropDown},
                [storeProgLvl.selected !== inst.id && !openDropDown && inst.category == 'param' ? 'dark:border-transparent':'dark:border-white/10 border-gray-200'],
                [storeProgLvl.selected == inst.id && openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeProgLvl.selected == inst.id && !openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeProgLvl.selected != inst.id && openDropDown ?'bg-dnscGreen/30 dark:bg- dark:text-green-400 text-dnscGreen dark:border-green-500/80 border-dnscGreen/80':'']
            "
            @contextmenu.self=""
            @click.self="getSelected(inst)"
        >
            <Content :inst="inst" />

            <div class="w-fit h-fit shrink-0 flex gap-2 items-center ml-1">
                <div class="flex gap-2 items-center" v-if="!storeDocument.attachDocument">
                    <CommentButton title="Comment" :comments="inst.comment" @click="getComment(inst)"/>
                    <div class="relative" v-click-outside="closeOptions">
                        <MoreH title="More" StyleMoreMenu="hover:bg-black/10" @click="openOptions = true" />
                        <Options 
                            v-if="openOptions" 
                            :isItem="inst.category == 'item'" 
                            @handleInfo="getInsInfo(inst)"
                            @handleDocument="getDocument(inst)" 
                        />
                    </div>
                </div>
                <AttachButton 
                    v-if="storeDocument.attachDocument && (inst.category == 'item')" 
                    @click="storeDocument.attachHere($page.props.currentlvl.accredId, inst.id, $page.props.flash)" 
                />
                <DownButton 
                    title="Show more"
                    @click="openDropDown = !openDropDown" 
                    :disabled="[...$page.props.instruments.filter(({parent}) => { return parent == inst.id})].length === 0"
                />
            </div>
        </div>
        
        <div class="w-full h-fit pl-6 relative bg-white dark:bg-secondaryDarkBg"  v-if="openDropDown"
        v-for="(instrument, index) in [...$page.props.instruments.filter(({parent}) => { return parent == props.inst.id})]">

            <div class="absolute top-0 left-2.5 h-7 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-2.5 h-full w-5 border-l-2 border-green-600" 
            v-if="[...$page.props.instruments.filter(({parent}) => { return parent == inst.id})].length-1 != index"></div>

            <div class="pt-1.5">
                <Accordion 
                    :inst="instrument" 
                    @handleInfo="getInsInfo"
                    @handleSelect="getSelected" 
                    @handleComment="getComment"  
                    @handleDocument="getDocument"
                />
            </div>
        </div>
    </AccordionContainer>
</template>

<script setup>
import Options from './InstrumentOption.vue'
import Content from '../../Instrument/Content.vue'
import AttachButton from '../../Buttons/Attach.vue'
import AccordionContainer from '../../Accordion.vue'
import MoreH from '../../Buttons/MeatballsMenuH.vue'
import DownButton from '../../Buttons/DownButton.vue'
import CommentButton from '../../Buttons/Comment.vue'
import myMethod from '../../../Store/Methods'
import { ref } from '@vue/reactivity'
import { useProglevelStore } from '../../../Store/storeProgramLvl'
import { useDocumentStore } from '../../../Store/storeDocument'
import { onUnmounted } from 'vue'

const storeProgLvl = useProglevelStore()
const storeDocument = useDocumentStore()

const props = defineProps({
    inst: Object,
})
const emits = defineEmits(['handleSelect', 'handleCreate', 'handleDocument', 'handleComment', 'handleInfo'])

const openDropDown = ref(false)
const openOptions = ref(false)
const expand = ref(false)

const getSelected = (inst) => {
    emits('handleSelect', inst)
}
const getComment = inst => {
    emits('handleComment', inst)
}
const getDocument = inst => {
    emits('handleDocument', inst)
    setTimeout(()=>{
        closeOptions()
    }, 100)
}
const closeOptions = () => {
    openOptions.value = false
}
const getInsInfo = info => {
    emits('handleInfo', info)
    setTimeout(()=>{
        closeOptions()
    }, 100)
}

onUnmounted(()=>{
    storeProgLvl.selected = null
})

</script>