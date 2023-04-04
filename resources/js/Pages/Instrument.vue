<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <NavTabs />
    </template>
    <template #subNav>
        <div class="w-full py-1.5 flex gap-1.5 sticky top-14 subnavBg slate-900 dark:border-primaryDarkBorder border-t border-slate-200 px-2.5 z-20">
            <div class="relative">
                <CreateButton @click="openCreateAs" :disabled="$page.props.createAs > 2" >
                    <AddIcon />
                    <div class="ml-2 mr-1 whitespace-nowrap max-sm:hidden" v-if="$page.props.createAs === 0">Create Instrument</div>
                    <div class="ml-2 mr-1 whitespace-nowrap max-sm:hidden" v-if="$page.props.createAs === 1">Create Level</div>
                    <div class="ml-2 mr-1 whitespace-nowrap max-sm:hidden" v-if="$page.props.createAs === 2">Create Area</div>
                </CreateButton>
            </div>
            <BackButton v-if="$page.props.crumbs.length !== 0" @click="backRoute" title="Back" />
            <Crumbs :crumbs="$page.props.crumbs" @handleCrumbRoute="roteTo" :current="$page.props.current" />

            <TagButton @click="setTagProgram" title="Tag program"
            :disabled="($page.props.current?($page.props.current.category == 'inst' && !storeInstrument.selected?true:$page.props.current.category == 'lvl'?false:!storeInstrument.selected):true)" />
            <EditButton @click="openUpdate" title="Edit" :disabled="!storeInstrument.selected || category == 'ind'" />
            <InfoButton title="Details" :disabled="!storeInstrument.selected || category == 'ind'" @click="isInstInfo = true" />
            <BlockButton @click="isExcludeArea = true" :disabled="!storeInstrument.selected || category == 'lvl' || category == 'inst'" title="Exclude from computation" />
            <DeleteButton title="Delete" :disabled="!storeInstrument.selected || ['lvl', 'inst'].includes(category)" @click="isRemove=true" />
        </div>
    </template>
    
    <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-3" v-if="$page.props.createAs <= 1">
        <InstrumentCard 
            :instrument="instrument" 
            :selected="storeInstrument.selected"
            @handleClick="getSelect(instrument)"
            @dblclick="route(instrument.id, $page.props.createAs, $page.props.current)"
            @handleRoute="route(instrument.id, $page.props.createAs, $page.props.current)"
            v-for="instrument in $page.props.instruments"
        />
    </div>

    <div class="mt-3 pb-3 -z-10" v-if="$page.props.createAs == 2" ref="accordionContainer">
        <div 
            class="mt-2 w-full h-fit rounded-md bg-white dark:bg-secondaryDarkBg p-1.5 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
            
            v-for="(children, index) in instruments"
            :key="index"
        >
            <Accordion :inst="children" :area="children.id" @handleSelect="getSelect" @handleUnselect="closeGetSelect" @handle-create="getCreateBtn"/>
        </div>
    </div>

    <InstrumentModal 
        :isEdit="isEdit"
        @update="update"
        @handle-close="closeCreate"
        v-if="$page.props.createAs === 0 && createAs && !parent" 
    />

    <LevelModal 
        :isEdit="isEdit"
        @update="update"
        @handle-close="closeCreate"
        v-if="$page.props.createAs === 1 && createAs && !parent" 
    />

    <AreaModal
        :isEdit="isEdit"
        :levelInstrument="$page.props.lvlinst"
        @update="update"
        @handle-close="closeCreate"
        v-if="$page.props.createAs === 2 && createAs && !createParamOrInd"
    />

    <ParameterModal
        :parent="parent"
        :isEdit="isEdit"
        :levelInstrument="$page.props.lvlinst"
        @update="update"
        @handle-close="closeCreate"
        v-if="createAs && createParamOrInd === 3"
    />

    <IndicatorModal
        :parent="parent"
        :isEdit="isEdit"
        :levelInstrument="$page.props.lvlinst"
        @update="update"
        @handle-close="closeCreate"
        v-if="createAs && createParamOrInd === 5"
    />
    
    <TagProgramModal 
        @handle-close="closeTagProgram"
        v-if="isTagProgram"
        :level="$page.props.current?$page.props.current.category == 'lvl'?$page.props.current:storeInstrument.updateSelect:storeInstrument.updateSelect"
    />

    <ExcludeModal
        :area="storeInstrument.updateSelect"
        v-if="isExcludeArea"
        @handleClose="closeExclude"
    />

    <InfoInstrument
        :inst="storeInstrument.updateSelect"
        v-if="isInstInfo && storeInstrument.selected"
        @handleClose="isInstInfo = false"
    />

    <RemoveInstrument 
        :inst="storeInstrument.updateSelect"
        v-if="storeInstrument.updateSelect && isRemove"
        @handleDelete="deleteInstrument"
        @handleClose="isRemove = false"
        @handleCancel="cancelDelete"
    />

</Layout>

<input 
    type="hidden" 
    :value="storeInstrument.getInstruments($page.props.instruments)"
>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import Crumbs from '../Components/BreadCrumbs.vue'
import AddIcon from '../Components/Icons/addIcon.vue'
import BackButton from '../Components/Buttons/Back.vue'
import EditButton from '../Components/Buttons/Edit.vue'
import InfoButton from '../Components/Buttons/Info.vue'
import BlockButton from '../Components/Buttons/Block.vue'
import CreateButton from '../Components/Buttons/Create.vue'
import NavTabs from '../Components/Accreditation/NavTab.vue'
import Accordion from '../Components/Instrument/Accordion.vue'
import DeleteButton from '../Components/Buttons/DelButton.vue'
import TagButton from '../Components/Buttons/TagProgramButton.vue'
import AreaModal from '../Components/Instrument/Area/AreaModal.vue'
import InfoInstrument from '../Components/Instrument/InfoModal.vue'
import ExcludeModal from '../Components/Instrument/ExcludeModal.vue'
import LevelModal from '../Components/Instrument/Level/LevelModal.vue'
import TagProgramModal from '../Components/Instrument/TagProgramModal.vue'
import InstrumentCard from '../Components/Accreditation/Instrument/Card.vue'
import RemoveInstrument from '../Components/Instrument/RemoveInstrument.vue'
import ParameterModal from '../Components/Instrument/Parameter/ParameterModal.vue'
import IndicatorModal from '../Components/Instrument/Indicators/IndicatorModal.vue'
import InstrumentModal from '../Components/Instrument/Instruments/InstrumentsModal.vue'
import myMethod from '../Store/Methods.js'
import { Inertia } from '@inertiajs/inertia'
import { onMounted, onUnmounted, ref } from 'vue'
import { useInstrumentStore } from '../Store/storeInstrument'

const storeInstrument = useInstrumentStore()

const props = defineProps({ current: Object, instruments: Array, createAs: Number, crumbs: Array})

const parent = ref(null)
const isEdit = ref(false)
const category = ref(null)
const createAs = ref(false)
const isRemove = ref(false)
const isInstInfo = ref(false)
const cancelToken = ref(null)
const isTagProgram = ref(false)
const isExcludeArea = ref(false)
const createParamOrInd = ref(null)
const accordionContainer = ref(null)
const instruments = ref(props.current ? [...props.instruments.filter(({parent}) => { return parent == props.current.id })] : [])

const route = (id, route, current) => {
    Inertia.get('/accreditation/instrument/inst/'+id)
    storeInstrument.selected = null
}

const roteTo = (instRoute) => {
    Inertia.get('/accreditation/instrument/inst/'+instRoute.id)
    storeInstrument.selected = null
}

const backRoute = () => {
    if(props.crumbs.length <= 1)
    {
        Inertia.get('/accreditation/instrument')
    }else{
        Inertia.get('/accreditation/instrument/inst/'+props.crumbs.at(0).parent)
    }
    storeInstrument.selected = null
}

const getSelect = (inst) => {
    category.value = inst.category
    storeInstrument.selected = inst.id
    storeInstrument.updateSelect = inst
}
const closeGetSelect = () => {
    category.value = null
    storeInstrument.selected = null
    storeInstrument.updateSelect = null
}

const setTagProgram = () => {
    isTagProgram.value = true
}

const closeTagProgram = () => {
    isTagProgram.value = false
}

const getCreateBtn = inst => {
    parent.value = inst
    createAs.value = true
    storeInstrument.selected = inst.id
    storeInstrument.updateSelect = inst
    createParamOrInd.value = inst.category == 'ind' || inst.category == 'item' ? 5 : 
        (inst.category == 'area' ? 3 : null)
}

const openUpdate = () => {
    category.value == 'param' ?
        createParamOrInd.value = 3 :
        (category.value == 'ind' || category.value == 'item'? 
            createParamOrInd.value = 5 : '')
            
    isEdit.value = true
    createAs.value = true

}

const openCreateAs = () => {
    isEdit.value = false
    createAs.value = true
    storeInstrument.selected = null
    storeInstrument.updateSelect = null 
}

const closeCreate = () => {
    parent.value = null
    isEdit.value = false
    createAs.value = false
    createParamOrInd.value = null
}

const closeExclude = () => {
    isExcludeArea.value = false
}

const deleteInstrument = () => {
    Inertia.post('/accreditation/instrument/delete/',{
        id: storeInstrument.selected
    }, {
        onCancelToken: cancelT => {
            cancelToken.value = cancelT
            console.log('canceled')
        },
        onSuccess: page => {
            if(page.props.flash.success)
            {
                isRemove.value = false
                closeGetSelect()
            }
        }
    })
}

const cancelDelete = () => {
    if(cancelToken.value)
        cancelToken.value.cancel()

    isRemove.value = false
}

const update = (updates) => {
    parent.value = null
    isEdit.value = false
    createAs.value = false
    createParamOrInd.value = null
    storeInstrument.selected = null
    storeInstrument.updateSelect = null 
    instruments.value = props.current? [...updates.filter(({parent}) => { return parent == props.current.id })] : ''
    closeTagProgram()
}

const windowsClick = (e) => {
    if(!e.target.closest(`#instrument${storeInstrument.selected}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal') && !e.target.closest('.itemCard'))
    {
        //clear selected
        storeInstrument.selected = null
        //close modal
        update(instruments.value)
        //close tag modal
        closeTagProgram()
        //close exclude
        closeExclude()

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
                console.log()
            }
        })
    }
})
onUnmounted(()=>{
    window.removeEventListener('click', windowsClick)
    storeInstrument.$reset
})
</script>


<style scoped>
.bounce-enter-active {
  animation: bounce-in 0.1s;
}
.bounce-leave-active {
  animation: bounce-in 0.1s reverse;
}
@keyframes bounce-in {
  0% {
    opacity: 0;
    height: 0%;
    width: 0%;
  }

  50% {
    opacity: .50;
    height: 50%;
    width: 50%;
  }
  100% {
    opacity: 1;
    height: 100%;
    width: 100%;
  }
}
</style>