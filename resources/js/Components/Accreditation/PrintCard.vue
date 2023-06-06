<script setup>
import {ref, computed} from 'vue'

const props = defineProps({
    item: Object,
    parent: String,
    padding: {
        type: Number,
        default: 0
    },
})
const titles = props.item.title
const searchTerm = props.parent
const isChild = ref(titles.includes(searchTerm))

const addPadding = ref(isChild.value?5:0)

addPadding.value += (isChild.value?(addPadding.value==0?5:props.padding):0)

const pl = computed(() => {
    return {
        '5': 'pl-[2rem]',
        '10': 'pl-[3rem]',
        '15': 'pl-[4rem]',
        '20': 'pl-[5rem]',
        '25': 'pl-[6rem]',
        '30': 'pl-[7rem]',
        '35': 'pl-[8rem]',
        '40': 'pl-[9rem]',
        '45': 'pl-[10rem]',
        '50': 'pl-[11rem]',
    }[addPadding.value];
});

</script>

<template>
    <div>
        <div class="">
            <div class="flex">  
                <div class="border-b border-black/70 grow p-1.5" :class="pl, {'font-semibold uppercase':(item.category != 'item')}">
                    {{ item.title }}<span v-if="item.category == 'item'">.</span> <span class="pl-3">{{ item.description }}</span>
                </div>

                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center"
                :class="'border-b border-black/70'">
                    {{(item.category=='item'?item.rate:'')}}
                </div>
                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center"
                :class="{'border-b border-black/70':(item.category=='ind' || item.category=='param')},{'border-t border-black/70':(item.category=='ind')}">
                    {{(item.category=='ind'?item.rate:'')}}
                </div>
                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center"
                :class="{'border-b border-black/70':(item.category=='param')}">
                    {{(item.category=='param'?item.rate:'')}}
                </div>
            </div>

        </div>

        <div class="" v-for="(item2, index) in item.children" :key="index">
            <PrintCard :item="item2" :padding="addPadding" :parent="item.title"/>
        </div>
    </div>
</template>