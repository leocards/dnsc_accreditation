<template>
    <AccordionContainer>
        <div class="w-full min-h-[2rem] rounded-md border border-transparent flex items-center justify-between p-2 transition duration-300"
        :id="`instrument${inst.id}`"
        :class="
            [inst.category != 'area'?'border-gray-200':(storeInstrument.selected !== inst.id && !openDropDown?'dark:border-transparent':'')],
            {'hover:bg-gray-300/80 hover:dark:bg-white/10' : storeInstrument.selected != inst.id && !openDropDown},
            [storeInstrument.selected !== inst.id && !openDropDown && inst.category == 'area' ? 'dark:border-transparent':'dark:border-white/10'],
            [storeInstrument.selected == inst.id && openDropDown ?'accordionBg1':''],
            [storeInstrument.selected == inst.id && !openDropDown ?'accordionBg2':''],
            [storeInstrument.selected != inst.id && openDropDown ?'accordionBg3':'']
        "
        @click.self="getSelected(inst)">
            <Content :inst="inst" />
            
            <div class="w-fit shrink-0 flex gap-2">
                <UnblockButton @click="include" v-if="inst.exclude_rate" title="Remove exclude from computation" />
                <AddButton 
                    title="Add"
                    v-if="inst.category != 'param'" 
                    :active="(openDropDown || (storeInstrument.selected == inst.id?true:false))" 
                    @click="getCreate(inst)"
                />
                <DownButton 
                    title="Show more"
                    @click="openDropDown = !openDropDown"
                    :active="openDropDown || (storeInstrument.selected == inst.id?true:false)" 
                    :disabled="[...storeInstrument.instruments.filter(({parent}) => { return parent == props.inst.id})].length === 0"
                />
            </div>
        </div>

        <div class="w-full h-fit pl-6 relative bg-white dark:bg-secondaryDarkBg slate-700" 
        v-for="(inst, index) in [...storeInstrument.instruments.filter(({parent}) => { return parent == props.inst.id})]" 
        :key="index" v-if="openDropDown">
            <div class="absolute top-0 left-2.5 h-7 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-2.5 h-full w-5 border-l-2 border-green-600" 
            v-if="[...storeInstrument.instruments.filter(({parent}) => { return parent == props.inst.id})].length-1 != index"></div>
            <div class="pt-1.5">
                <Accordion :inst="inst" @handleSelect="getSelected" @handleCreate="getCreate" @handleUnselect="unselect()" :area="area"/>
            </div>
        </div>
    </AccordionContainer>
</template>

<script setup>
import Content from './Content.vue'
import AddButton from '../Buttons/AddButton.vue'
import AccordionContainer from '../Accordion.vue'
import DownButton from '../Buttons/DownButton.vue'
import UnblockButton from '../Buttons/Unblock.vue'
import { ref } from '@vue/reactivity'
import { useInstrumentStore } from '../../Store/storeInstrument'
import { Inertia } from '@inertiajs/inertia'
import { onUnmounted } from 'vue'

const storeInstrument = useInstrumentStore()

const props = defineProps({
    inst: Object,
    area: Number,
})
const emits = defineEmits(['handleSelect', 'handleCreate', 'handleUnselect'])

const openDropDown = ref(false)

const getSelected = (inst) => {

    emits('handleSelect', inst)
}

const getCreate = val => {
    emits('handleCreate', val)
    storeInstrument.area = props.area
}
const unselect = () => {
    emits('handleUnselect')
}

const include = () => {
    Inertia.visit(`/accreditation/instrument/exclude/${props.inst.id}?include=true`,{
        method: 'get',
        preserveState: true,
        preserveScroll: true,
    })
}

onUnmounted(()=>{
    unselect()
})
</script>