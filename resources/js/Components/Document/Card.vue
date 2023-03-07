<template>
    <div class="h-20 flex subnavBg cursor-pointer rounded group documents dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]" 
        :id="`docsPage${document.id}`"
    >
        <div class="grow flex flex-col rounded transition_300"
            :class="[document.id == selected?'bg-dnscGreen/20':'hover:bg-gray-300/80 hover:dark:bg-tertiaryDarkBg']">
            <div class="grow flex items-center px-1.5 pointer-events-none">
                <div class="grow">
                    <div class="Twoline">{{document.title}}</div>
                </div>
                
            </div>
            <div class="w-full h-7 border-t pointer-events-none  flex items-center justify-between px-1.5 select-none"
                :class="[document.id == selected?'border-dnscGreen/40':'dark:border-primaryDarkBorder border-slate-300/70']">
                <div class="flex items-center text-xs gap-1.5">
                    <button 
                    type="button" 
                    class="flex items-center gap-1.5 rounded h-fit p-0.5 select-none px-1.5 pr-2 dark:hover:text-white/70">
                        <div>Comment <span v-if="document.comment">∙ {{document.comment}}</span></div>
                    </button>
                </div>
                <div class="select-none text-xs">{{document.type}}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useDocumentStore } from "../../Store/storeDocument"


const storeDocument = useDocumentStore()

const props = defineProps({
    selected: Number,
    document: Object
})

</script>