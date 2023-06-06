<template>
    <div class="w-full h-[80vh] flex mb-2 gap-2.5 analytics">
        <div class="w-full rounded subnavBg grow h-full flex-col p-2 pt-0 analytics" :class="[!clusterContainer.hasClustered?'flex':'hidden']">
            <div class="h-12 flex items-center shrink-0">Select to generate</div>

            <div class="border rounded border-slate-200 dark:border-primaryDarkBorder grow p-1.5 pb-0.5 overflow-y-auto overscroll-contain relative"
            >
                <Card 
                    :survey="survey"
                    v-for="(survey, index) in $page.props.surveys" :key="index"
                    @handleClusters="assignClusters"
                    @handleAccred=""
                    @handleloadCluster=""
                    @handleLoadData="loadsData"
                    @handleError="errorKmeans"
                />

                <ErrorCluster 
                    :errorMsg="errorCluster"
                    :loads="isLoadingCluster"
                    v-if="errorCluster || isLoadingCluster"
                    @handleClose="errorCluster = null"
                />
            </div>
        </div>

        <div class="w-full rounded subnavBg grow h-full flex-col p-2 pt-0 analytics" :class="[clusterContainer.ViewCluster?'flex':'hidden']">
            <div class="h-12 flex items-center shrink-0 gap-1">
                <button 
                    class="rounded w-fit px-2 transition_300 hover:bg-dnscGreen/20 hover:text-dnscGreen py-1.5 dark:hover:bg-dnscGreen dark:text-white/80" 
                    @click="displayCluster('best')"
                    :class="[clustered.val == 'best'? 'bg-dnscGreen/20 text-dnscGreen dark:bg-dnscGreen':'']"
                >Best practices</button>
                <button 
                    class="rounded w-fit px-2 transition_300 hover:bg-dnscGreen/20 hover:text-dnscGreen py-1.5 dark:hover:bg-dnscGreen dark:text-white/80" 
                    @click="displayCluster('avg')"
                    :class="[clustered.val == 'avg'? 'bg-dnscGreen/20 text-dnscGreen dark:bg-dnscGreen':'']"
                >Average</button>
                <button 
                    class="rounded w-fit px-2 transition_300 hover:bg-dnscGreen/20 hover:text-dnscGreen py-1.5 dark:hover:bg-dnscGreen dark:text-white/80" 
                    @click="displayCluster('low')"
                    :class="[clustered.val == 'low'? 'bg-dnscGreen/20 text-dnscGreen dark:bg-dnscGreen':'']"
                >Needs improvement</button>

                <button 
                    v-if="clusterContainer.ViewCluster && clusterContainer.hasClustered"
                    class="flex items-center justify-center w-6 h-6 rounded-full bg-dnscGreen/20 pl-px pt-px 
                    text-dnscGreen hover:bg-dnscGreen hover:text-white transition_300 ml-auto" @click="clusterContainer.ViewCluster = false, clusterContainer.hasClustered = false">
                    <XIcon/>
                </button>
            </div>

            <div class="border rounded border-slate-200 dark:border-primaryDarkBorder grow p-1.5 pb-0.5 overflow-y-auto overscroll-contain">
                <div class="rounded w-full bg-gray-300/80 dark:bg-tertiaryDarkBg h-11 mb-1 flex items-center justify-between px-3"
                v-for="inst in clustered.cluster"
                >
                    <div class="flex">{{inst.title}} <span class="Oneline h-fit pl-2"> {{inst.description}} </span></div>
                    <!-- <div class="flex justify-between max-w-[9rem]">
                        <div>show more</div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import Card from './Card.vue'
import ErrorCluster from './ErrorCluster.vue'
import XIcon from '../Icons/closeIcon.vue'
import axios from 'axios';
import { ref } from '@vue/reactivity'
import myMethod from '../../Store/Methods'
import { useForm } from '@inertiajs/inertia-vue3';
import { onMounted, onUnmounted } from 'vue';

const clustered = useForm({
    val: null,
    cluster: []
});
const resultClus = ref(null)
const clusters = useForm({
    best: [],
    avg: [],
    low: []
})
const errorCluster = ref(null)
const isLoadingCluster = ref(false)
const clusterContainer = useForm({
    hasClustered: false,
    ViewCluster: true
})

const assignClusters = inst => {
    clusters.reset()

    inst.areaData.indicators.forEach(data => {
        let inddata = !data.rate?0:parseFloat(data.rate)
        if(inst.clusters.best.includes(inddata)) {
            clusters.best.push(data)
        }else if(inst.clusters.avg.includes(inddata)){
            clusters.avg.push(data)
        }else clusters.low.push(data)
    })

    displayCluster('best')

}

const displayCluster = (val, clus = false) => {
    clustered.val = val
    clustered.cluster = clusters[val]
}

const loadsData = val => {
    clustered.reset()
    isLoadingCluster.value = val
}
const errorKmeans = msg => {
    errorCluster.value = msg
}

const pageResize = () => {
    if(window.innerWidth <= 848){
        if(!clusterContainer.hasClustered)
            clusterContainer.ViewCluster = false
        else{
            clusterContainer.ViewCluster = true
        }
    }else{
        clusterContainer.hasClustered = false
        clusterContainer.ViewCluster = true
    }
}

onMounted(() => {
    window.addEventListener('resize', pageResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', pageResize)
})
pageResize()

</script>