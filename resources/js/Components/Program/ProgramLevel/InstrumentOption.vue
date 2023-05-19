<template>
    <div class="absolute -top-2 right-0 w-60 bg-white dark:text-white/70 text-slate-600 dark:bg-tertiaryDarkBg rounded-md 
    dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)] z-10">
        <ul class="w-full h-full rounded-md py-1.5 myshadow select-none">
            <li 
                class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300"
                @click="$emit('handleInfo')"
            >
               <div class=""><InfoIcon/></div>
               <div>Details</div>
            </li>
            <li 
                class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300" 
                v-if="isItem"
                @click="$emit('handleDocument')"
            >
               <div class=""><DocumentIcon/></div>
               <div>Documents</div>
            </li>
            <li
                class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300" 
                v-if="isAssign && isParameter"
                @click="$emit('handleAssign')"
            >
                <div class=""><AssignIcon/></div>
               <div>Assign member</div>
            </li>
            <li
                class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300" 
                v-if="isItem && isTask"
                @click="$emit('handleEvidence')"
            >
                <div class=""><EditIcon/></div>
               <div>Evidences to attach</div>
            </li>
        </ul>
    </div>
</template>
<script setup>
import EditIcon from '../../Icons/editIcon.vue'
import InfoIcon from '../../Icons/infoIcon.vue'
import DocumentIcon from '../../Icons/documentIcon.vue'
import AssignIcon from '../../Icons/assignIcon.vue'

const props = defineProps({
    isItem: Boolean,
    isAssign: Boolean,
    isParameter: {
        type: Boolean,
        default: false
    },
    isTask: {
        type: Boolean,
        default: false
    }
})
const emits = defineEmits(['handleDocument', 'handleAssign', 'handleInfo', 'handleEvidence'])

</script>