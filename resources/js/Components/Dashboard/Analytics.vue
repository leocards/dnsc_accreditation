<template>
    <div class="w-full h-[80vh] flex mb-2 gap-2.5 analytics">
        <div class="w-full rounded subnavBg grow h-full flex-col p-2 pt-0 analytics" :class="[!clusterContainer.hasClustered?'flex':'hidden']">
            <div class="h-12 flex items-center shrink-0">Select to cluster</div>

            <div class="border rounded border-slate-200 dark:border-primaryDarkBorder grow p-1.5 pb-0.5 overflow-y-auto overscroll-contain relative"
            >
                <Card 
                    :survey="survey"
                    v-for="(survey, index) in $page.props.surveys" :key="index"
                    @handleClusters="getAreaIndicators"
                    @handleAccred="toggleLevel"
                />

                <ErrorCluster 
                    :errorMsg="errorCluster"
                    v-if="errorCluster"
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

const clustered = ref({
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
const clusterContainer = useForm({
    hasClustered: false,
    ViewCluster: true
})

function findBestPractices(clustered){
    let cluster = [];
    for (const key in clustered) {
        cluster.push(key)
    }
    for (let i = 1; i < cluster.length; i++) {
        let key = cluster[i];
        let j = i - 1;
        while (j >= 0 && cluster[j] > key) {
            cluster[j + 1] = cluster[j];
            j = j - 1;
        }
        cluster[j + 1] = key;
    }
    cluster = cluster.map((val) => {
        let obj = {}
        for (const key in clustered) {
            if(key == val)
                obj[key] = clustered[key]
        }
        return obj;
    })
    return cluster;
}

const getUniqueMean = (data) => {
    let means = [data[0]]

    for (let i = 1; i < data.length; i++) {
        if (means.length == 3) {
            break
        } else if (!means.includes(data[i])) {
            means.push(data[i])
        }
    }

    return means
}

const getCluster = data => {
    try {
        const dataset = ref(data)
        const initalCentroids = ref(getUniqueMean(data)) 

        if(dataset.value == null || dataset.value.length == 0)
            throw new Error('Empty data')

        const result = myMethod.K_means(initalCentroids.value, dataset.value)
        result.result = findBestPractices(result.result)

        return result
    } catch (e) {
        return e.message
    }
}

const mapRates = ind => {
    return ind.map(inst => {
        let rate = isNaN(inst.rate) || !inst.rate ? 0 : parseFloat(inst.rate)

        return rate
    })
}

const toUniqueData = data => {
    data.forEach((el, index) => {
        let key = parseFloat(Object.keys(el))//get the key of a cluster
        let clus = Array.from(new Set(el[key]))//generate unique values
        
        //create new object then change the object of clustered data to unique data
        let obj = {}
        obj[key] = clus
        resultClus.value.result.splice(index, 1, obj)
    });
}

const assignClusters = inst => {
    clusters.reset()

    if(!resultClus.value.result || resultClus.value.result.length < 3)
        throw new Error('Empty data or not enough data to cluster')

    let res = [...resultClus.value.result]
    
    let low = res[0][(Object.keys(res[0]))]
    let avg = res[1][Object.keys(res[1])]
    let best = res[2][Object.keys(res[2])]
    inst.forEach(ins => {
        if(!isNaN(ins.rate))
        {
            if(best.includes(parseFloat(ins.rate))){
                clusters.best.push(ins)
            }else if(avg.includes(parseFloat(ins.rate))){
                clusters.avg.push(ins)
            }else{
                clusters.low.push(ins)
            }
        }else{
            clusters.low.push(ins)
        }
    })

}

const displayCluster = (val, clus = false) => {
    if(clustered.value.cluster.length !== 0 || clus)
    {
        clustered.value.val = val
        clustered.value.cluster = clusters[val]
    }
}

const getAreaIndicators = async inst => {
    try {
        //let start = performance.now();

        //generate cluster
        resultClus.value = await getCluster(mapRates(inst))
/* 
        console.log('mean', resultClus.value) */
        
        if(!resultClus.value.result || resultClus.value.result.length < 3)
            throw new Error('Empty data or not enough data to cluster')

        //convert the data of each centroids to unique value
        if(resultClus.value.result)
            toUniqueData(resultClus.value.result)

        assignClusters(inst)

        displayCluster('best', true)
        //proccess speed
        // let end = performance.now();
        // let time = end - start;
        // console.log('Time taken: ' + ((time / 1000).toFixed(1)) + ' seconds.');

        if(window.innerWidth <= 848){
            clusterContainer.hasClustered = true
            clusterContainer.ViewCluster = true
        }
    } catch (e) {
        errorCluster.value = e.message
        toggleLevel(null)
    }
}

const toggleLevel = val => {
    if(!val){
        clustered.value.val = null
        clustered.value.cluster = []
    }
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