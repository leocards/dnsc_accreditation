<template>
    <div class="h-10 w-full rounded relative"
    :class="[crumbs.length > 0 || current ? 'bg-slate-200/40 dark:bg-white/5 slate-800' : '']">

        <div class="w-full h-full overflow-hidden flex items-center rounded px-1.5 dark:text-white/60">
            <ul class="h-full flex items-center flex-row-reverse max-sm:hidden">
                
                <li class="" v-for="crumb in crumbs">
                    <div class="flex item-center" v-if="crumb.id != current.id && crumbs.length <= 3">
                        <div class="max-w-[9rem] min-w-[3rem] p-1 px-1.5 rounded hover:bg-slate-300/70 hover:dark:bg-white/10 slate-700" :title="crumb.title">
                            <div class="cursor-pointer" @click="crumbRoute(crumb)">
                                <div class="Oneline w-full h-fit">{{crumb.title}}</div>
                            </div>
                        </div>
                        <div  class="h-fit self-center">
                            <CrumbsRightIcon />
                        </div>
                    </div>
                </li>

                <li class="" v-if="crumbs.length > 3">
                    <div class="flex item-center">
                        <div class="max-w-[9rem] min-w-[3rem] p-1 px-1.5 rounded hover:bg-slate-300/70 hover:dark:bg-white/10 slate-700" :title="crumbs.at(0).title">
                            <div class="cursor-pointer" @click="crumbPrevRoute(crumbs.at(0))">
                                <div class="Oneline w-full h-fit">{{crumbs.at(0).title}}</div>
                            </div>
                        </div>
                        <div  class="h-fit self-center">
                            <CrumbsRightIcon />
                        </div>
                    </div>
                </li>

                <div class="flex items-center crumbsMore" v-if="crumbs.length > 3" @click="openCumbsList = !openCumbsList">
                    <MoreHorButton />
                    <div class="h-fit self-center">
                        <CrumbsRightIcon />
                    </div>
                </div>

            </ul>

            <div class="Oneline min-w-[5rem] max-w-[16rem] h-fit p-1 px-1.5 rounded hover:bg-slate-300/70 hover:dark:bg-white/10 slate-700 select-none" 
            v-if="crumbs.length > 0 || current" :title="current.title+titleDesc">
                <div class="flex h-fit gap-0.5">
                    <div class="whitespace-nowrap">{{current.title}} </div> <span v-show="current.description" class="h-fit Oneline"> - {{current.description}}</span>
                </div>
            </div>
        </div>

        <div class="absolute top-9 myshadow bg-white rounded w-[12.5rem] crumbsList" v-if="openCumbsList">
            <ul class="w-full py-2.5">
                <li class="hover:bg-slate-200 " v-for="(crumb, index) in subCrumbs" :key="index" :title="crumb.title">
                    <div class="h-fit py-1.5 cursor-pointer" @click="crumbRoute(crumb)">
                        <div class="">
                            <div class="w-full h-fit px-1.5 Oneline">
                                {{crumb.title}}
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>   

    </div>
</template>

<script setup>
import MoreHorButton from './Buttons/MeatballsMenuH.vue';
import CrumbsRightIcon from './Icons/crumbsRightIcon.vue';
import { ref } from 'vue';

const props = defineProps({
    crumbs: Array,
    current: Object,
})

const emits = defineEmits(['handleCrumbRoute', 'handleFirstRoute'])

const titleDesc = ref(props.current ? props.current.description ? ' - '+props.current.description : '' : '')

const crumbRoute = (instRoute) => {
    emits('handleCrumbRoute', instRoute)
}

const crumbPrevRoute = (instRoute) => {
    emits('handleFirstRoute', instRoute)
}

const subCrumbs = ref(props.crumbs.filter(el =>{
    return el.id != props.crumbs.at(0).id
}))

const openCumbsList = ref(false)

window.addEventListener('click', e => {
    if(!e.target.closest('.crumbsMore') && !e.target.closest('.crumbsList')){
        openCumbsList.value = false
    }
})

</script>