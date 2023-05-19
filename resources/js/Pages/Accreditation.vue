<template>
<Layout :user="$page.props.user">
    <template #navSlot>
        <NavTabs />
    </template>
    <template #subNav>
        <div class="w-full py-1.5 flex items-center gap-1.5 sticky top-14 subnavBg slate-900 dark:border-primaryDarkBorder slate-700/70 border-t border-slate-200 px-2.5 z-20">
                <InfoButton title="Info" :disabled="!selected" @click="isAccredInfo = true" />
                <SurveyButton 
                    title="Open survey" 
                    :disabled="!selected"
                    @click="opeSurvey"
                />
                <PreliminaryButton 
                    title="Mark as complete self survey" 
                    :disabled="!isSelfSurveyComplete"
                    @click="markAsCompleteSelfSurvey"
                />

                <div class="ml-auto relative" v-if="$page.props.user.auth == 6" v-click-outside="closeViewRates">
                    <button 
                        class="mr-2 rounded h-9 hover:bg-dnscGreen bg-dnscGreen/90 px-3 text-white transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
                        @click="isViewRatesVerified = !isViewRatesVerified, isViewRates = false"
                    >
                        View verified
                    </button>
                    <button 
                        class="mr-2 rounded h-9 hover:bg-dnscGreen bg-dnscGreen/90 px-3 text-white transition_300 dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
                        @click="isViewRates = !isViewRates, isViewRatesVerified = false"
                    >
                        Verify Rates
                    </button>

                    <VerifyRatesList
                        v-if="isViewRates"
                        @handleViewRates="CetificationRates"
                    />
                    <VerifyRatesList
                        v-if="isViewRatesVerified"
                        @handleViewRates="VerifiedRatings"
                        :verified="true"
                    />
                </div>
        </div>
    </template>

    <div class="mt-3 mb-8" v-if="myMethod.ifArray(actualSurveyDue, '>', 0) || myMethod.ifArray(selfSurveyDue, '>', 0)">
        <div>Currently conducting survey</div>

        <div class="ml-4 mt-3" v-if="myMethod.ifArray(actualSurveyDue, '>', 0)">
            <div>Actual survey</div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-2">
                <Card 
                    :isSurvey="true"
                    :accredit="accred"
                    :selected="selected"
                    :isActual="true"
                    @click=""
                    @dblclick="route(accred.programId, accred.instrumentId)"
                    @handleCloseSurvey="closeCurrentSurvey"
                    v-for="accred in actualSurveyDue"
                />
            </div>
        </div>

        <div class="ml-4 mt-5" v-if="myMethod.ifArray(selfSurveyDue, '>', 0)">
            <div>Self survey</div>

            <div class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-2">
                <Card 
                    :isSurvey="true"
                    :accredit="accred"
                    :selected="selected"
                    @click="selectAccred(accred)"
                    @dblclick="route(accred.programId, accred.instrumentId)"
                    @handleCloseSurvey="closeCurrentSurvey"
                    v-for="accred in selfSurveyDue"
                />
            </div>
        </div>
    </div>

    <div 
        class="grid grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] gap-3 mt-3" 
        :data-accred="getSurveys($page.props.actual_survey, $page.props.self_survey, $page.props.accreditation)"
    >
        <Card 
            :accredit="accred"
            :selected="selected"
            @click="selectAccred(accred)"
            @dblclick="route(accred.programId, accred.instrumentId)"
            v-for="accred in accreditations"
        />
    </div>

    <SurveyModal 
        :program="selectedProgram"
        v-if="selected && isOpenSurvey"
        @handleClose="closeSurvey"
    />

    <RatesCertification
        :accred="viewRatesAccred"
        v-if="viewRatesAccred"
        @handleClose="viewRatesAccred = null"
    />

    <InfoModal 
        :accred="selectedProgram"
        v-if="isAccredInfo && selected"
        @handleClose="isAccredInfo = false"
    />

    <RateViewing
        v-if="isShowVerifiedRates.open"
        @close="isShowVerifiedRates.reset()"
        :id="isShowVerifiedRates.id"
        :accreds="isShowVerifiedRates.accred"
    ></RateViewing>
</Layout>
</template>

<script setup>
import Layout from '../Components/Layout.vue'
import InfoButton from '../Components/Buttons/Info.vue'
import Card from '../Components/Accreditation/Card.vue'
import SurveyButton from '../Components/Buttons/Survey.vue'
import NavTabs from '../Components/Accreditation/NavTab.vue'
import RateViewing from '../Components/Accreditation/RateViewing.vue'
import InfoModal from '../Components/Accreditation/InfoModal.vue'
import SurveyModal from '../Components/Accreditation/SurveyModal.vue'
import PreliminaryButton from '../Components/Buttons/PreliminaryButton.vue'
import VerifyRatesList from '../Components/Accreditation/VerifyRatesList.vue'
import RatesCertification from '../Components/Accreditation/RatesCertification.vue'
import myMethod from '../Store/Methods'
import { onMounted, onUnmounted, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({
    user: Object
})

const selected = ref(null)
const isViewRates = ref(false)
const isOpenSurvey = ref(false)
const viewRatesAccred = ref(null)
const selectedProgram = ref(null)
const isSelfSurveyComplete = ref(false)
const isAccredInfo = ref(false)
const selfSurveyDue = ref(null)
const accreditations = ref(null)
const actualSurveyDue = ref(null)
const isViewRatesVerified = ref(false)
const isShowVerifiedRates = useForm({
    open: false,
    id: null,
    accred: null
})

const closeSurvey = () => {
    selected.value = null
    isOpenSurvey.value = false
    isSelfSurveyComplete.value = false
}

const opeSurvey = () => {
    isOpenSurvey.value = true
}

const selectAccred = accred => {
    selected.value = accred.id
    selectedProgram.value = accred
    isSelfSurveyComplete.value = true
}

const closeCurrentSurvey = (accred, isActual = false) => {
    Inertia.post('/accreditation/close_survey', {
        accred: accred
    }, {
        onSuccess: page => {
            ////console.log(page.props.flash.error)
        }
    })
}

const closeViewRates = () => {
    isViewRates.value = false
    isViewRatesVerified.value = false
}

const VerifiedRatings = accred => {
    isShowVerifiedRates.open = true
    isShowVerifiedRates.id = accred.surveyId
    isShowVerifiedRates.accred = accred
    closeViewRates()
}

const CetificationRates = accred => {
    viewRatesAccred.value = accred
    closeViewRates()
}

const markAsCompleteSelfSurvey = () => {
    Inertia.get(`/accreditation/mark_as_complete_self_survey/${selected.value}`)
}

const route = (program, level) => {
    Inertia.get(`/program/level/prog/${program}/level/${level}/${level}`)
}

const windowsClick = (e) => {
    if(!e.target.closest(`#accred${selected.value}`) && !e.target.closest('.actionBtn') && !e.target.closest('.modal'))
    {
        //clear selected
        selected.value = null
        closeSurvey()

    }
}

const getSurveys = (actual, self, accred) => {
    selfSurveyDue.value = self
    accreditations.value = accred
    actualSurveyDue.value = actual
}

onMounted(()=>{
    window.addEventListener('click', windowsClick)
})
onUnmounted(()=>{
    window.removeEventListener('click', windowsClick)
})

try {
    Echo.private('UpdateAccred.'+props.user.auth)
        .listen('UpdateAccred', (e) => {
            if(window.location.pathname == '/accreditation')
            {
                let accredSelfIndex = []
                let accredActualIndex = []
    
                if(e.accred.self.length > 0)
                {
                    e.accred.self.forEach(el => {
                        accredSelfIndex.push(accreditations.value.findIndex(({id}) => id == el.id))
                        selfSurveyDue.value.push(el)
                    });
                    accredSelfIndex.forEach(indx => {
                        accreditations.value.splice(indx, 1)
                    })
                }
    
                if(e.accred.actual.length > 0)
                {
                    e.accred.actual.forEach(el => {
                        accredActualIndex.push(accreditations.value.findIndex(({id}) => id == el.id))
                        actualSurveyDue.value.push(el)
                    });
                    accredActualIndex.forEach(indx => {
                        accreditations.value.splice(indx, 1)
                    })
                }
            }
        });

} catch (e) {
    //console.log(e)
}

</script>