<template>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-12 h-12 shrink-0 rounded-full relative grid place-items-center area_progress dark:before:bg-tertiaryDarkBg before:bg-white" ref="progressBar">
                <div class="text-xs relative progValue" ref="valueContainer">{{progressEndValue}}%</div>
            </div>
            <div class="flex items-center gap-3">
                <div>{{area.title}}</div>
                <div>{{area.description}} </div>
            </div>
        </div>

        <div class="flex gap-2 items-center">
            <span class="text-xs">{{(area.role?'Task Force Chairperson':'Member')}} </span>
            <DrilledDown title="Area drilled down" @click="$emit('handleDrillDown', area)" />
            <Link :href="`/task/page/level/${$page.props.currentLevel.id}/area/${area.id}`">
                <RightButton title="Open" />
            </Link>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from '@vue/runtime-core'
import DrilledDown from '../../Buttons/DrillDown.vue'
import RightButton from '../../Buttons/RightButton.vue'

const props = defineProps({
    area: Object
})
const emits = defineEmits(['handleDrillDown'])

const progressBar = ref(null)
const valueContainer = ref(null)
const progressValue = ref(0)
const progressEndValue = ref(props.area.progress.progress ? parseInt(props.area.progress.progress) : 0)

onMounted(()=>{
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
    }, 15);
})
</script>