<template>
    <AccredCard>
        <div class="w-full h-full p-2.5 px-3 select-none flex items-center relative" :id="`accred${accredit.id}`"
        :class="[selected === accredit.id?'card-select':' card-hover']">
            <div class="pointer-events-none grow">
                <div class="text-base">{{accredit.program}}</div>
                <div class="text-xs inter-font">{{accredit.title}}</div>
            </div>

            <div class="flex gap-1.5 absolute bottom-[0.30rem] right-11">
                <button class="p-0.5 rounded transition_300 hover:bg-dnscGreen hover:text-white"
                    :class="[accredit.selfsurvey?'text-green-400 ':'text-gray-400/70']"
                    title="Unmark as complete self survey"
                    @click="removeSelfSurvey"
                    v-if="accredit.selfSurvey"
                >
                    <SelfSurveyIcon />
                </button>
            
                <button class="p-0.5 rounded transition_300 hover:bg-dnscGreen hover:text-white"
                    :class="[accredit.selfsurvey?'text-green-400 ':'text-gray-400/70']"
                    title="Unverify"
                    @click="removeVerified"
                    v-if="accredit.verified"
                >
                    <RateVerifiedIcon />
                </button>
            </div>

            <button class="absolute bottom-1.5 right-3 px-1 rounded-xl transition_300 hover:bg-dnscGreen hover:text-white"
                :class="[accredit.restrict?'text-green-400 ':'text-gray-400/70']"
                :title="accredit.restrict?'Turn off restrict':'Turn on restrict'"
                @click="toggleRetrict"
            >
                <On v-if="accredit.restrict" />
                <Off v-else />
            </button>

            <button class="absolute top-1.5 right-3 flex items-center justify-center w-6 h-6 hover:bg-black/20 dark:hover:bg-white/20 rounded-full"
                v-if="isSurvey"
                @click="$emit('handleCloseSurvey', accredit, isActual)"
                title="Close survey"
            >
                <CloseIcon />
            </button>
            <div class="mb-1.5 mr-1" v-if="false">
                <button class="ring-1 w-6 h-6 flex items-center justify-center rounded-full">
                    <div class="-mt-0.5 rotate-180">
                        <BackIcon />
                    </div>
                </button>
            </div>
        </div>
    </AccredCard>
</template>
<script setup>
import AccredCard from '../Card.vue'
import On from '../Icons/toggleOnIcon.vue'
import Off from '../Icons/toggleOffIcon.vue'
import CloseIcon from '../Icons/smallClose.vue'
import RateVerifiedIcon from '../Icons/rateVerifiedIcon.vue'
import SelfSurveyIcon from '../Icons/prelimSmallFilledIcon.vue'
import BackIcon from '../Icons/backIcon.vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
    accredit: Object,
    selected: Number,
    isSurvey: Boolean,
    isActual: {
        type: Boolean,
        default: false
    }
})

const emits = defineEmits(['handleCloseSurvey'])

const toggleRetrict = () => {
    Inertia.post('/accreditation/restrict', {
        id: props.accredit.id
    })
}

const removeSelfSurvey = () => {
    Inertia.post('/accreditation/remove_selfSurvey', {
        id: props.accredit.id
    })
}

const removeVerified = () => {
    Inertia.post('/accreditation/unverify_rates', {
        id: props.accredit.id
    })
}
</script>