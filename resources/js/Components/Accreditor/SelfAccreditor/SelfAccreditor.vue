<template>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 pt-3 pb-3" v-if="!$page.props.currentProgram">
        <Card 
            :program="program"
            v-for="program in $page.props.programs"
            @click="openProgram(program, $page.props.accreditor)"
        />
    </div>

    <div class="w-full relative flex">
        <div class="pb-3 w-full" :class="{'mr-[22.3rem]':((isComment || isDocument) && storeAccreditor.selected)}">
            <div class="mt-2 w-full h-fit rounded-md bg-white dark:bg-secondaryDarkBg p-1.5 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            v-for="instrument in $page.props.instruments">
                <Accordion 
                    :inst="instrument"
                    @handleRate="rate"
                    @handleComment="instrumentComment"
                    @handleDocument="showDocument"
                />
            </div>

        </div>

        <InstrumentComment
            :top="'top-[4rem] h-[calc(100vh-4.5rem)]'"
            :instrument="storeAccreditor.selectedInst"
            :accredlvl="$page.props.accred.id"
            v-if="isComment && storeAccreditor.selected"
            @handleClose="closeInstrumentComment"
        />

        <DocumentAside
            :evidence="storeDocument.evidenceIndex"
            :user="$page.props.user.userId"
            :accredlvl="$page.props.accred.id"
            :top="'top-[4rem] h-[calc(100vh-4.5rem)]'"
            :instrument="storeAccreditor.selectedInst"
            v-if="isDocument && storeAccreditor.selected"
            @handleClose="CloseDocument"
            @handleView="viewDocument"
        />
    </div>

    <RateModal 
        :inst="storeAccreditor.selectedInst"
        v-if="storeAccreditor.selected && isRate"
        @handleClose="closeRate"
    />

    <DocumentViewer
        :instrument="storeAccreditor.selectedInst"
        :accredlvl="$page.props.accred.id"
        v-if="openDocument && storeAccreditor.selected"
        @handleClose="closeViewDocument"
    />
</template>

<script setup>
import Card from '../Card.vue'
import Accordion from '../Accordion.vue'
import RateModal from './RatingModal.vue'
import DocumentViewer from '../../Document/DocumentViewerModal.vue'
import DocumentAside from '../../Program/ProgramLevel/DocumentAside.vue'
import InstrumentComment from '../../Program/ProgramLevel/InstrumentComment.vue'
import { Inertia } from '@inertiajs/inertia'
import { useAccreditorStore } from '../../../Store/storeAccreditor'
import { ref } from '@vue/reactivity'
import { useDocumentStore } from '../../../Store/storeDocument'

const storeAccreditor = useAccreditorStore()
const storeDocument = useDocumentStore()

const isRate = ref(false)
const isComment = ref(false)
const isDocument = ref(false)
const openDocument = ref(false)

const openProgram = (program, isAccreditor) => {
    if(!isAccreditor)
        Inertia.get('/self_accreditor', {
            program: program.programId,
            level: program.instrumentId,
            survey: program.id
        })
    else
        Inertia.get('/accreditor', {
            program: program.programId,
            level: program.instrumentId,
            survey: program.id
        })
}

const rate = inst => {
    isRate.value = true
    storeAccreditor.selected = inst.id
    storeAccreditor.selectedInst = inst
}
const instrumentComment = inst => {
    isComment.value = true
    isDocument.value = false
    storeAccreditor.selected = inst.id
    storeAccreditor.selectedInst = inst
}

const closeRate = () => {
    isRate.value = false
}

const closeInstrumentComment = () => {
    isComment.value = false
    storeAccreditor.selected = null
    storeAccreditor.selectedInst = null
}

const showDocument = inst => {
    isComment.value = false
    isDocument.value = true
    storeAccreditor.selected = inst.id
    storeAccreditor.selectedInst = inst
}

const CloseDocument = () => {
    storeAccreditor.selected = null
    storeAccreditor.selectedInst = null
    isDocument.value = false
}

const viewDocument = () => {
    openDocument.value = true
}

const closeViewDocument = () => {
    openDocument.value = false
}

</script>