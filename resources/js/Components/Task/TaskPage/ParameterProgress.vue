<template>
    <div class="w-[80%] py-2 px-3 rounded border dark:border-primaryDarkBorder">
        <div class="w-full flex justify-between items-center mb-2">
            <div>{{param.title}}</div>
            <div>{{progLabel}}%</div>
        </div>

        <div class="relative h-2.5 rounded-sm w-full bg-slate-300/40">
            <div class="h-full bg-[#4d5bf9] dark:bg-[#4d5bf9]/60 rounded-sm" ref="progress"></div><!-- progress bar -->
        </div>
    </div>
</template>
<script setup>
import { ref } from "@vue/reactivity"
import { onMounted } from "@vue/runtime-core"


const props = defineProps({
    param: Object
})

const progress = ref(null)
const progWhole = ref(props.param.progress ? props.param.progress : 0)
const progLabel = ref(progWhole.value > 0 ? parseFloat(props.param.progress).toFixed(2) : progWhole.value)

onMounted(()=>{
    progress.value.style.width = progLabel.value+'%'
})

</script>