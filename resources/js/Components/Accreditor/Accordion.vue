<template>
    <AccordionContainer>
        
        <div class="w-full min-h-[2rem] rounded-md border border-transparent flex items-center justify-between p-2 transition duration-300"
            :id="`taskAccordion${inst.id}`"
            :class="
                {'hover:bg-gray-300/80 hover:dark:bg-white/10' : storeAccreditor.selected != inst.id && !openDropDown},
                [storeAccreditor.selected !== inst.id && !openDropDown && inst.category == 'param' ? 'dark:border-transparent':'dark:border-white/10 border-gray-200'],
                [storeAccreditor.selected == inst.id && openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeAccreditor.selected == inst.id && !openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeAccreditor.selected != inst.id && openDropDown?'bg-dnscGreen/30 dark:bg- dark:text-green-400 text-dnscGreen dark:border-green-500/80 border-dnscGreen/80':'']
            "
            @click.self=""
        >
            <Content :inst="inst" @handleEvidence="document" :active="(storeAccreditor.selected == inst.id)" :accreditor="true" />
            <div class="w-fit h-fit shrink-0 flex gap-2 items-center ml-1">
                <!-- <DocumentButton v-if="inst.category == 'item'" title="Documents" @click="document(inst)" /> -->
                <RateButton 
                    v-if="$page.props.rate" 
                    :disabled="inst.category != 'item'"
                    :disable="inst.category != 'item'"
                    :class="{'disabled:text-dnscGreen':openDropDown}"
                    @click="rateInstrument(inst)" 
                    title="Rate" 
                    :rate="inst.rate.rate" 
                />
                <CommentButton title="Comment" :comments="inst.comment" @click="$emit('handleComment', inst)"/>
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
                <Accordion 
                    :inst="instrument" 
                    @handleRate="rateInstrument"
                    @handleComment="comment"
                    @handleDocument="document"
                />
            </div>
        </div>
    </AccordionContainer>
</template>
<script setup>
import Content from '../Instrument/Content.vue'
import AccordionContainer from '../Accordion.vue'
import DownButton from '../Buttons/DownButton.vue'
import RateButton from '../Buttons/RateButton.vue'
import CommentButton from '../Buttons/Comment.vue'
import DocumentButton from '../Buttons/Document.vue'
import { useAccreditorStore } from '../../Store/storeAccreditor'
import { ref } from '@vue/reactivity'
import { useDocumentStore } from '../../Store/storeDocument'

const storeAccreditor = useAccreditorStore()
const storeDocument = useDocumentStore()

const props = defineProps({
    inst: Object,
    selected: Number
})
const emits = defineEmits([
    'handleRate',
    'handleComment',
    'handleDocument'
])

const openDropDown = ref(false)

const rateInstrument = inst => {
    if(inst.category == 'item')
        emits('handleRate', inst)
}

const comment = inst => {
    emits('handleComment', inst)
}

const document = (inst, evidence = null) => {
    storeDocument.evidenceIndex = evidence
    emits('handleDocument', inst, evidence)
}

</script>