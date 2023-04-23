<template>
    <div class="">
        <div class="flex rounded items-center cursor-pointer pr-2" :class="[showSubs?'bg-dnscGreen/20 text-dnscGreen dark:text-white/60 dark:bg-dnscGreen/30':'bg-gray-100 hover:bg-gray-200 dark:bg-tertiaryDarkBg hover:dark:bg-white/20']" 
        @click.self="ratesInst(insts.id)">
            <div class="grow flex items-center gap-2 p-2 pointer-events-none">
                {{insts.title}} <span v-if="isArea"> - {{insts.description}}</span>
            </div>   
            <div class=" pointer-events-none">
                {{(insts.rate??'0')}}  
            </div> 
            <button class="ml-2 hover:bg-dnscGreen hover:text-white rounded relative group
            " @click="reCalculates(insts.id)">
                <SyncIcon/>
                <div class="absolute text-xs whitespace-nowrap group-hover:block bg-dnscGreen/20 text-dnscGreen right-5 top-4 p-px rounded hidden z-20">Re-calculate rate</div>
            </button>
        </div>
        <div v-if="showSubs">
            <div 
                class="pl-8 relative" 
                v-for="(item, index) in inst" :key="index"
                v-if="inst"
            >
                <div class="absolute top-0 left-4 h-6 w-3.5 rounded-bl border-b-2 border-l-2 border-green-600"></div>
                <div class="absolute top-0 left-4 h-full w-5 border-l-2 border-green-600"
                    v-if="(inst.length-1) != index"
                ></div>
                <div class="pt-1">
                    <RateSubs :insts="item" :accred="accred" :isArea="false" @reCalculate="reCalculates" />
                </div>
            </div>
            <div class="flex w-full justify-center pt-1" v-else>
                <Loading />
            </div>
        </div>
    </div>
</template>

<script setup>
import Loading from '../Loading.vue'
import SyncIcon from '../Icons/sync.vue'
import { ref, watch, watchEffect } from 'vue';
import axios from 'axios';

const props = defineProps({
    parent: Number,
    insts: Object,
    accred: Object,
    isArea: {
        type: Boolean,
        default: true
    }
})
const emits = defineEmits(['reCalculate'])

const inst = ref(null)
const showSubs = ref(false)

const ratesInst = parent => {
    showSubs.value = !showSubs.value
}

const reCalculates = async (ids) => {
    try{
        await axios.post('/self_accreditor/recalculateRate', {
            id: ids,
            accredlvlId: props.accred.id
        })

        emits('reCalculate')

    } catch (e) {
    }
}

watch(showSubs, ()=>{
    inst.value = null
})

watchEffect(()=>{
    if(props.insts){
        if(showSubs.value) {
            try {
                axios.post('/accreditation/areas_certification',{
                    id: props.accred.id,
                    parent: props.insts.id
                })
                .then(res => {
                    inst.value = res.data
                    console.log(res.data)
                })
            } catch (e) {
            }
        }
    }
})

</script>