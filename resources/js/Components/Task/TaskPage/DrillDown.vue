<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Drilled down
        </template>
        
        <div class="w-full pb-4">

            <div class="w-full h-fit">
                <div class="flex justify-center">
                    <div class="w-20 h-20 bg-dnscGreen/90 shrink-0 rounded-full relative grid place-items-center progress dark:before:bg-tertiaryDarkBg before:bg-white" ref="progressBar">
                        <div class=" relative progValue" ref="valueContainer">{{progressEndValueLabel}}%</div>
                    </div>
                </div>

                <div class="w-full py-3.5 pb-3 text-center font-semibold">{{area.title}}</div>

                <div class="w-full py-3 flex items-center">
                    <div class="px-2.5 flex items-center gap-3 w-full justify-center text-[#4d5bf9] dark:text-[#4d5bf9]/80">
                        <span><ProgressIcon /></span>
                        <div>
                            <span>{{inProgress }} </span>
                            <span> In progress </span>
                        </div>
                    </div>
                    <div class="px-3.5 flex items-center gap-2 w-full justify-center text-green-600">
                        <span><CompletedIcon /></span>
                        <div>
                            <span>{{complete }} </span>
                            <span> Completed </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col items-center gap-2 max-h-[24rem] mt-4 overflow-y-auto mb-3.5">
                
                <ParameterProgress v-for="(param, index) in params" :key="index" :param="param" />
                <!--  -->
                
            </div>

            <div class="w-full flex justify-center items-center gap-2 max-h-[24rem] py-3" v-if="false">
                
                <Loading />
                
            </div>

        </div>

    </Modal>
</template>

<script setup>
import Modal from '../../Modal.vue'
import Loading from '../../Loading.vue'
import ProgressIcon from '../../Icons/progressIcon.vue'
import ParameterProgress from './ParameterProgress.vue'
import CompletedIcon from '../../Icons/completedIcon.vue'
import { ref } from '@vue/reactivity'

const props = defineProps({
    area: Object,
    accredId: Number
})
const emits = defineEmits([
    'handleClose',
])

const speed = ref(10)
const params = ref(null)
const complete = ref(null)
const inProgress = ref(null)
const progressValue = ref(0)
const progressBar = ref(null)
const valueContainer = ref(null)
const paramsProgress = ref(null)
const progressEndValue = ref(parseInt(props.area.progress.progress) ? parseInt(props.area.progress.progress) : 0)
const progressEndValueLabel = ref(parseInt(props.area.progress.progress) ? parseFloat(props.area.progress.progress).toFixed(2) : 0)


let progress = setInterval(() => {
        if (progressValue.value == progressEndValue.value) {    
            clearInterval(progress);
        }
        progressValue.value++;
        valueContainer.textContent = `${progressValue.value}%`;
        progressBar.value.style.background = `conic-gradient(
            #16764b ${progressValue.value * 3.6}deg,
            #BADECE ${progressValue.value * 3.6}deg
        )`;
        
}, speed);

const getComplete = () => {
    complete.value = params.value.filter(({progress})=> {return progress == 100}).length
}

const getInProgress = () => {
    inProgress.value = params.value.filter(({progress})=> {return progress < 100}).length
}

axios.post('/task/drilldown', {
    area: props.area.id,
    accredlvl: props.accredId
}).then(res => {
    params.value = res.data
    getComplete()
    getInProgress()
})
</script>

<style scoped>
    .progress:before{
        content: "";
        position: absolute;
        width: 80%;
        height: 80%;
        border-radius: 50%;
    }
</style>