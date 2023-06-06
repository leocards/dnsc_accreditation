<template>
    <div class="select-none">
        <div class="flex items-center p-3 border border-gray-300 rounded cursor-pointer hover:bg-dnscGreen/30 hover:border-dnscGreen/30 transition_300"
            @click.self="showSubs = !showSubs"
        >
            <div class="grow flex items-center gap-2 pointer-events-none">
                <div class="shrink-0">{{inst.title}}  {{ inst.description? ' - ':'' }}</div> <span class="pr-4">{{inst.description}}</span>
            </div>
            <div class="shrink-0 px-2 pointer-events-none">
                {{ inst.rate?inst.rate:'0' }}
            </div> 
        </div>
        <div 
            class="pl-8 relative"
             v-for="(instrument, index) in inst.children" v-if="showSubs"
        >
            <div class="absolute top-0 left-4 h-6 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="absolute top-0 left-4 h-full w-5 border-l-2 border-green-600"
            v-if="(inst.children.length-1) != index"
            ></div>
            <div class="pt-1">
                <VerifiedAccord :key="index" :inst="instrument" />
            </div>
        </div>
        <div
            class="pl-8 relative"
            v-if="showSubs && inst.children.length == 0"
        >
            <div class="absolute top-0 left-4 h-6 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
            <div class="flex p-2  bo rder border-gray-300 rounded opacity-40">
                empty
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    inst: Object,
    view: {
        type: Boolean,
        default: false
    }
})

const showSubs = ref(props.inst.children.length == 0?false:props.view)


</script>