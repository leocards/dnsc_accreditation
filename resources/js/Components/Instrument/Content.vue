<template>
    <div
        ref="parentContainer"
        class="flex grow pr-2 pointer-events-none overscroll-contain relative mr-1"
        :class="{ 'max-h-[7rem]': !expand }"
    >
        <div class="flex grow" ref="contentContainer">
            <div>{{ inst.title }}</div>
            <div
                class="ml-2 max-w-[33rem] grow qwIOrAQty h-fit"
                ref="descriptionContent"
                :class="{ Twoline: !expand }"
                v-if="inst.description"
            >
                {{ inst.description }}
            </div>
            <ul
                class="ml-2 qwIOrAQty aqwQdwEr grow relative max-w-[33rem]"
                :class="{ Twoline: !expand }"
                ref="evidenceContect"
                v-if="inst.attachment && (myMethod.isValidJsonAndEmpty(inst.attachment))"
            >
                <div class="font-semibold">Evidence to attach <span class="pl-2"> {{ myMethod.isValidJsonAndEmpty(inst.attachment).length }} </span></div>
                <li
                    :class="{ 'mt-3': index != 0 }"
                    v-for="(item, index) in myMethod.isValidJson(
                        inst.attachment
                    )"
                    :key="index"
                >
                    &#x2022; {{ item.evidence }}
                </li>
            </ul>
            <button
                v-if="expand && isExpandable"
                class="absolute top-0 right-0 text-xs opacity-60 hover:opacity-100 hover:text-white hover:bg-dnscGreen/80 px-1.5 py-0.5 rounded-full pointer-events-auto actionBtn"
                @click="expand = false"
            >
                minimize
            </button>
            <button
                v-if="!expand && isExpandable"
                class="absolute top-0 right-0 text-xs opacity-60 hover:opacity-100 hover:text-white hover:bg-dnscGreen/80 px-1.5 py-0.5 rounded-full pointer-events-auto actionBtn"
                @click="expand = true"
            >
                expand
            </button>
            <div
                class="ml-2 grow qwIOrAQty aqwQdwEr max-w-[33rem]"
                v-if="
                    inst.attachment &&
                    !myMethod.isValidJson(inst.attachment)
                "
                :class="{ Twoline: !expand }"
                ref="evidenceContect2"
            >
                <div class="font-semibold">Evidence to attach</div>
                {{ inst.attachment }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watchEffect } from "vue";
import myMethod from "../../Store/Methods";

const props = defineProps({
    inst: Object,
});

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
        if (
            evidenceContect2.value &&
            evidenceContect2.value.innerText.length > 90
        ) {
            isExpandable.value = true;
        }
        if (
            evidenceContect.value &&
            (evidenceContect.value.innerText.length > 90 || 
                (myMethod.isValidJson(props.inst.attachment) ? myMethod.isValidJson(props.inst.attachment).length > 1 : false
            ))
        ) {
            isExpandable.value = true;
        } else if (
            testLineDesc == 2 &&
            descriptionContent.value.innerText.length > 140
        ) {
            isExpandable.value = true;
        }
    }
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