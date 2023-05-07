<template>
    <AccordionContainer>
        <div 
            class="w-full min-h-[2rem] rounded-md border border-transparent flex items-center justify-between p-2 transition duration-300 dark:border-white/10 border-gray-200"
            :id="`taskAccordion${inst.id}`"
            :class="
                {'hover:bg-gray-300/80 hover:dark:bg-white/10' : storeTaskPage.selected != inst.id && !openDropDown},
                [storeTaskPage.selected == inst.id && openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeTaskPage.selected == inst.id && !openDropDown ?'bg-dnscGreen/20 dark:bg-dnscGreen/40 dark:text-green-400 text-dnscGreen dark:border-green-900/80 border-dnscGreen/5':''],
                [storeTaskPage.selected != inst.id && openDropDown?'bg-dnscGreen/30 dark:bg- dark:text-green-400 text-dnscGreen dark:border-green-500/80 border-dnscGreen/80':'']
            "
            @contextmenu.self=""
        >
            <Contents :inst="inst" :showDocuButton="false" />

            <div class="w-fit h-fit shrink-0 flex gap-3 items-center ml-1">
                <div class="shrink-0 w-24" v-if="inst.due">
                    <Time :time="inst.due" :isDateOnly="true" />
                </div>
                <button class="w-7 h-7 rounded shrink-0 flex justify-center group items-center relative text-inherit transition_300
                hover:bg-dnscGreen/80 hover:text-white actionBtn"
                @click="open(inst.id, area, $page.props.currentLevel.id)">
                    <span class="flex items-center justify-center rotate-180">
                        <ForwardIcon /> 
                    </span>
                </button>
            </div>
        </div>
    </AccordionContainer>
</template>

<script setup>
import Time from '../../DateTime.vue'
import ForwardIcon from '../../Icons/backIcon.vue'
import AccordionContainer from '../../Accordion.vue'
import DownButton from '../../Buttons/DownButton.vue'
import Contents from '../../Instrument/Content.vue'
import { ref } from '@vue/reactivity'
import { Inertia } from '@inertiajs/inertia'
import { useTaskPageStore } from '../../../Store/storeTaskPage'

const storeTaskPage = useTaskPageStore()

const props = defineProps({
    inst: Object,
    area: Number,
})
const emits = defineEmits([
    'handleInstrument'
])

const openDropDown = ref(false)
const openOptions = ref(false)

const closeOptions = () => {
    openOptions.value = false
}

const open = (inst, area, accred) => {
    Inertia.get(`/task/navigate/${inst}/${area}/${accred}`)
}
</script>