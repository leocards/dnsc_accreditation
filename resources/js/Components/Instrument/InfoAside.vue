<template>
        <div class="text-slate-400 mt-6">Title</div>
        <div class="mt-2">{{instrument.title}}</div>

        <div class="text-slate-400 mt-6">Description</div>
        <div class="mt-2 whitespace-pre-wrap break-words">{{instrument.description}}</div>

        <div class="mt-6" v-if="instrument.attachment">
            <div 
                v-if="myMethod.isValidJson(instrument.attachment)" 
                v-for="(item, index) in myMethod.isValidJson(instrument.attachment)" 
                :key="index"
                :class="{'mt-3':(index != 0)}"
            >
                <div class="dark:text-slate-400">Evidence to attach</div>
                <div class="mt-2 whitespace-pre-wrap break-words">{{item.evidence}}</div>
            </div>
            <div v-else>
                <div class="dark:text-slate-400">Evidence to attach</div>
                <div class="mt-2 whitespace-pre-wrap break-words">{{instrument.attachment}}</div>
            </div>
        </div>
</template>
<script setup>
import myMethod from '../../Store/Methods'

const props = defineProps({
    instrument: Object
})

</script>