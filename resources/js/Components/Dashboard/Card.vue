<template>
    <div class="w-full">
        <div class="rounded w-full bg-gray-200 hover:bg-gray-300/80 transition_300 hover:dark:bg-white/20 dark:bg-tertiaryDarkBg h-11 mb-1 flex items-center justify-between px-3 cursor-pointer"
        :id="`analytic${survey.surveyId}`" 
        @click.self="toggleLevel">
            <div class="pointer-events-none">{{`${survey.program} ${survey.level}`}} <span v-if="survey.rate">- {{survey.rate}} </span></div>

            <button @click="getAreas(true, survey.surveyId)" class="text-xs p-0.5 px-1 rounded transition_300 hover:bg-dnscGreen hover:text-white">
                reload
            </button>
        </div>

        <div class="w-full flex items-center justify-center py-3" v-if="!loaded && isShow">
            <Loading />
        </div>

        <div 
            class="w-full pl-6 relative text-xs"
            v-for="(area, index) in areas" 
            v-if="areas.length > 0 && isShow"
        >
            <div class="absolute top-0 left-2.5 h-[1.50rem] w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-2.5 h-full w-5 border-l-2 border-green-600" 
            v-if="(areas.length-1) != index"></div>

            <div class="pb-1">
                <div 
                    class="rounded w-full cursor-pointer transition_300 h-11 flex items-center justify-between px-3"
                    :class="[selectedArea == area.id? 'dark:bg-dnscGreen bg-dnscGreen/20 text-dnscGreen dark:text-white' : 'bg-gray-200 hover:bg-gray-300/80 hover:dark:bg-white/20 dark:bg-tertiaryDarkBg']"
                    @click="selectAreaCluster(area)"
                >
                    <div>{{area.title}} {{area.description}}</div>
                    <!-- <div class="flex justify-between max-w-[9rem]">
                        <div>show more</div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Loading from '../Loading.vue'
import { ref } from "@vue/reactivity"
import { onMounted, onUnmounted } from '@vue/runtime-core'

const areas = ref([])
const isShow = ref(false)
const loaded = ref(false)
const selectedArea = ref(null)

const props = defineProps({
    survey: Object,
    errorCluster: Boolean
})

const emits = defineEmits([
    'handleClusters',
    'handleAccred'
])

const winClick = e => {
    if(!e.target.closest('.analytics')){
        getClickedOutside()
    }
}
const getClickedOutside = () => {
    isShow.value = false
    emits('handleAccred', isShow.value)
}
const toggleLevel = () => {
    isShow.value = !isShow.value
    selectedArea.value = null
    emits('handleAccred', isShow.value)
}

const getAreas = async (show = false, id) => {
    try {
        loaded.value = false
        areas.value = []
        isShow.value = show
        emits('handleAccred', false)
        
        let res = await axios.get(`/data_to_cluster/${id}`)

        loaded.value = true
        areas.value = res.data

    } catch (e) {
        console.log(e)
    }
}

const selectAreaCluster = area => {
    selectedArea.value = area.id
    emits('handleClusters', area.indicators)
}

try {
    if(areas.value.length == 0)
        getAreas(false, props.survey.surveyId)

} catch (e) {

}

onMounted(() => {
    window.addEventListener('click', winClick)
})
onUnmounted(() => {
    window.removeEventListener('click', winClick)
})
</script>