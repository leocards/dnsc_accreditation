<template>
    <div
        ref="parentContainer"
        class="flex grow pr-2 pointer-events-none overscroll-contain relative mr-1"
        :class="{ 'max-h-[7rem]': !expand }"
    >
        <div class="flex grow" ref="contentContainer">
            <div>{{ inst.title }}</div>
            <div
                class="ml-2 shrink-0 grow qwIOrAQty h-fit"
                ref="descriptionContent"
                :class="{ Twoline: !expand }, [inst.category == 'item' ? 'max-w-[22rem] w-[22rem]' : 'max-w-[33rem]']"
                v-if="inst.description"
            >
                {{ inst.description }}
            </div>
            <ul
                class="ml-2 qwIOrAQty aqwQdwEr grow relative max-w-[33rem] flex flex-col"
                :class="{ 'justify-center': !expand }"
                ref="evidenceContect"
                v-if="inst.attachment && (myMethod.isValidJsonAndEmpty(inst.attachment))"
            >
                <div class="font-semibold">
                    <span class="">({{ myMethod.isValidJsonAndEmpty(inst.attachment).length }})</span> 
                    <span v-if="!accreditor"> Evidence<span v-if="myMethod.isValidJsonAndEmpty(inst.attachment).length > 1">s</span> to attach</span>
                    <span v-else> Evidence<span v-if="myMethod.isValidJsonAndEmpty(inst.attachment).length > 1">s</span> attached</span>
                </div>

                <li
                    class="flex mt-3 transition duration-150 rounded p-1"
                    :class="[active && index == storeDocument.evidenceIndex?'bg-gray-400/40':'hover:bg-gray-400/40']"
                    v-for="(item, index) in myMethod.isValidJson( inst.attachment )"
                    :key="index"
                    v-if="expand"
                >
                    <div class="">&#x2022; {{ item.evidence }} </div>
                    <div class="ml-auto max-w-fit h-fit my-auto">
                        <button 
                            v-if="!$page.url.startsWith('/accreditation') && showDocuButton"
                            class="text-sm pointer-events-auto px-1.5 py-0.5 rounded-full"
                            :class="[active && index == storeDocument.evidenceIndex?'bg-dnscGreen text-white':'hover:bg-dnscGreen bg-green-600/20 hover:text-white text-green-700']"
                            @click="getEvidenceDocument(inst, index)"
                        >
                            Document
                        </button>
                    </div>
                </li>

                <!-- <li
                    :class="{ 'mt-3': index != 0 }"
                    class="h-fit"
                    v-for="(item, index) in myMethod.isValidJson(
                        inst.attachment
                    )"
                    v-if="expand"
                >
                    
                    <button 
                        v-if="!$page.url.startsWith('/accreditation')"
                        class="text-sm pointer-events-auto hover:bg-dnscGreen hover:text-white px-1.5 bg-green-600/20 rounded-full text-green-700"
                        @click="getEvidenceDocument(inst, index)"
                    >
                        Document
                    </button>
                </li> -->
            </ul>
            <div
                class="ml-2 grow qwIOrAQty aqwQdwEr max-w-[33rem] flex flex-col"
                v-if="
                    inst.attachment &&
                    !myMethod.isValidJson(inst.attachment)
                "
                ref="evidenceContect2" 
                :class="{ 'justify-center': !expand }"
            >
                <div class="font-semibold">(1) Evidence to attach</div>
                <div v-if="expand">
                    {{ inst.attachment }}
                </div>
            </div>
            
            <button
                v-if="expand && isExpandable"
                class="h-fit text-xs hover:opacity-100 hover:text-white hover:bg-dnscGreen/80 px-1.5 py-1 
                rounded-full pointer-events-auto actionBtn my-auto ml-auto"
                @click="expand = false"
            >
                minimize
            </button>
            <button
                v-if="!expand && isExpandable"
                class="h-fit text-xs hover:opacity-100 hover:text-white hover:bg-dnscGreen/80 px-1.5 py-1 
                rounded-full pointer-events-auto actionBtn my-auto ml-auto"
                @click="expand = true"
            >
                expand
            </button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watchEffect } from "vue";
import myMethod from "../../Store/Methods";
import { useDocumentStore } from "../../Store/storeDocument";

const storeDocument = useDocumentStore()

const props = defineProps({
    inst: Object,
    active: Boolean,
    showDocuButton: {
        type: Boolean,
        default: true
    },
    accreditor: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['handleEvidence'])

const expand = ref(false);
const isExpandable = ref(false);
const evidenceContect = ref(null);
const parentContainer = ref(null);
const evidenceContect2 = ref(null);
const contentContainer = ref(null);
const descriptionContent = ref(null);

function showToggleExpand() {
    if (descriptionContent.value) {
        let testLineDesc =
            contentContainer.value.offsetHeight /
            parseInt(getComputedStyle(contentContainer.value).lineHeight);

        if((testLineDesc == 2 && descriptionContent.value.innerText.length > 97) || myMethod.isValidJsonAndEmpty(props.inst.attachment))
        {
            isExpandable.value = true;
        }else if(props.inst.attachment && !myMethod.isValidJson(props.inst.attachment)) {
            isExpandable.value = true;
        }

        /* if (
            evidenceContect2.value &&
            evidenceContect2.value.innerText.length > 101
        ) {
            isExpandable.value = true;
        }
        if (
            evidenceContect.value &&
            (evidenceContect.value.innerText.length > 101 || 
                (myMethod.isValidJson(props.inst.attachment) ? myMethod.isValidJson(props.inst.attachment).length > 1 : false
            ))
        ) {
            isExpandable.value = true;
        } else if (
            testLineDesc == 2 &&
            descriptionContent.value.innerText.length > 90
        ) {
            isExpandable.value = true;
        } */
    }
}

const getEvidenceDocument = (inst, evidence) => {
    emit('handleEvidence', inst, evidence)
}

onMounted(() => {
    myMethod.resizedElement(parentContainer.value, () => {
        showToggleExpand();
    });

    watchEffect(() => {
        if (props.inst) {
            showToggleExpand();
        }
    });
});
</script>