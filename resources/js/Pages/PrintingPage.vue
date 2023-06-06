<script setup>
import PrintCard from '../Components/Accreditation/PrintCard.vue';
import { Head } from '@inertiajs/inertia-vue3'
import { onMounted, ref } from 'vue';

const props = defineProps({
    id: Number,
    area: Array
})

const print = () => {
    window.print()
}

const total = ref(null)

props.area[0].children.forEach(el => {
    let rate = (el.rate == "" || isNaN(el.rate) ? 0 : parseFloat(el.rate))
    total.value += rate
})

const generateDescriptive = rate => {
    if(rate >= 4.5 && rate <= 5) {
        return 'Excellent'
    } else if (rate >= 3.5 && rate <= 4.49) {
        return 'Very Satisfactory'
    } else if (rate >= 2.5 && rate <= 3.49) {
        return 'Satisfactory'
    } else if (rate >= 1.5 && rate <= 2.49) {
        return 'Fair'
    } else if (rate >= 1 && rate <= 1.49) {
        return 'Poor'
    } else if (rate == '0') {
        return 'Missing'
    } else if (rate == 'N/A' || rate == 'NA') {
        return 'Not Applicable'
    } else return ''
}

onMounted(() => {
    setTimeout(() => print(), 50)
})

window.addEventListener('beforeunload', function(event) {
    window.onafterprint()
});

</script>

<template>
    <Head>
        <title>Accreditation Rates</title>
    </Head>

    <div class="mx-auto md:max-w-[75rem] w-full flex flex-col text-black m-4">
        <div class="print:hidden mt-4 ml-auto mb-3">
            <button class="flex items-center bg-gray-200 hover:bg-gray-300 h-9 w-28 rounded justify-center gap-2" @click="print">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24"><path d="M640-627.691v-120H320v120h-59.999v-144.006q0-15.301 10.395-25.647 10.394-10.346 25.759-10.346h367.531q15.62 0 25.966 10.35 10.347 10.35 10.347 25.65v143.999H640Zm-468.461 59.998h616.922-616.922Zm540.768 100q17 0 28.5-11.5t11.5-28.5q0-17-11.5-28.5t-28.5-11.5q-17 0-28.5 11.5t-11.5 28.5q0 17 11.5 28.5t28.5 11.5ZM332.309-200h295.382q5.385 0 8.847-3.462 3.462-3.462 3.462-8.847V-370.77H320v158.461q0 5.385 3.462 8.847 3.462 3.462 8.847 3.462Zm0 59.999q-29.827 0-51.067-21.241-21.241-21.24-21.241-51.067v-87.692H147.694q-15.365 0-25.759-10.395-10.394-10.394-10.394-25.759v-191.538q0-42.499 29.038-71.249 29.038-28.749 70.96-28.749h536.922q42.499 0 71.249 28.749 28.749 28.75 28.749 71.249v191.538q0 15.365-10.394 25.759-10.394 10.395-25.759 10.395H699.999v87.692q0 29.827-21.241 51.067-21.24 21.241-51.067 21.241H332.309ZM788.461-360v-167.693q0-17-11.5-28.5t-28.5-11.5H211.539q-17 0-28.5 11.5t-11.5 28.5V-360h88.462v-70.769h439.998V-360h88.462Z"/></svg>
                <div>PRINT</div>
            </button>
        </div>
        
        <div class="flex flex-col mb-10">
            <div class="uppercase text-center text-4xl mb-8">
                <div>{{ $page.props.area[0].title }}</div>
                <div class="text-2xl">{{ $page.props.area[0].description }}</div>
            </div>

            <div class=" mb-10">
                <div class="uppercase text-xl">Parameters</div>

                <ol role="list" class="list-[upper-alpha] ml-10 text-base uppercase">
                    <li v-for="(child, index) in $page.props.area[0].children" :key="index">
                        {{ child.description }}
                    </li>
                </ol>
            </div>

            <div class="bg- white">
                <div class="text-center border border-b-0 border-black/70 py-1">RATING SCALE</div>
                <div class="border border-b-0 border-black/70">
                    <div class="grid grid-cols-7 border-b border-black/70">
                        <div class="uppercase max-h-[2rem] grow text-center py-1">NA</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">0</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">1</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">2</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">3</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">4</div>
                        <div class="uppercase max-h-[2rem] grow border-l border-black/70 text-center py-1">5</div>
                    </div>
                    <div class="grid grid-cols-7 border-b border-black/70">
                        <div class="max-h-[3rem] grow text-center py-1">-</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">-</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">Poor</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">Fair</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">Satisfactory</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">Very Satisfactory</div>
                        <div class="max-h-[3rem] grow border-l border-black/70 text-center py-1">Excellent</div>
                    </div>
                    <div class="grid grid-cols-7 text-xs">
                        <div class="grow text-center py-1">
                            <div class="">Not Applicable</div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="">Missing</div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="flex flex-col h-full">
                                Criterion is met minimally in some respects, but much Improvement is needed to overcome weaknesses
                                <br>
                                <br>
                                <div class="mt-auto mb-2">
                                    (75% lesser than the standards)
                                </div>
                            </div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="flex flex-col h-full">
                                Criterion is met in most respects, but some improvement is needed to overcome weaknesses
                                <br>
                                <br>
                                <div class="mt-auto mb-2">
                                    (50% lesser than the standards)
                                </div>
                            </div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="flex flex-col h-full">
                                <div>Criterion is met in all respects</div>
                                <br>
                                <br>
                                <div class="mt-auto mb-2">
                                    (100% compliance with the standards)
                                </div>
                            </div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="flex flex-col h-full">
                                Criterion is fully met in all respects, at a level that demonstrates good practice
                                <br>
                                <br>
                                <div class="mt-auto mb-2">
                                    (50% greater than the standards)
                                </div>
                            </div>
                        </div>
                        <div class="grow border-l border-black/70 text-center py-1">
                            <div class="flex flex-col h-full">
                                Criterion is fully met with substantial number of good practices, at a level that provides a model for others
                                <br>
                                <br>
                                <div class="mt-auto mb-2">
                                    (75% greater than the standards)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div class="flex h-52 border border-black/70 bg- white">
                <div class="grow flex items-center justify-center text-xl">Indicators</div>
                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center">
                    <div class="rotate-90 whitespace-nowrap">Item Rating (IR)</div>
                </div>
                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center">
                    <div class="rotate-90"><span class="whitespace-nowrap">System - Implementation</span> <span>Outcomes Mean (SIOM)</span> </div>
                </div>
                <div class="shrink-0 w-10 justify-center border-l border-black/70 flex items-center">
                    <div class="rotate-90 whitespace-nowrap">Parameter Mean (PM)</div>
                </div>
            </div>
        
            <div class="border border-black/70 mb-3 bg- white" v-for="(item, index) in $page.props.area[0].children" :key="index">
                <PrintCard :item="item" />
            </div>
            <br>
            
            <div class="break-before-page">
                <div class="w-full text-center mt-10 text-lg">
                    <div>SUMMARY OF RATINGS</div>
                    <div class="uppercase justify-center mt-8 mb-4 flex">
                        <div>{{ $page.props.area[0].title }}: </div>
                        <div class="pl-1"> {{ $page.props.area[0].description }}</div>
                    </div>
                </div>
                <div class="">
                    <div class="flex text-center border border-black/70 bg- white">
                        <div class="w-28 grow pt-3.5">Parameters</div>
                        <div class="w-28 border-l border-black/70 p-1">Numerical Rating</div>
                        <div class="w-28 border-l border-black/70 p-1">Descriptive Equivalence</div>
                    </div>

                    <ol class="list-[upper-alpha]">
                        <div class="border-b border-x border-black/70 pl-7 bg- white" v-for="(child, index) in $page.props.area[0].children" :key="index">
                            <li>
                                <div class="flex ml-2 pl-4 border-l border-black/70">
                                    <div class="w-28 grow flex items-center py-1 uppercase">{{child.description}}</div>
                                    <div class="w-28 border-l border-black/70 p-1 flex items-center justify-center">{{child.rate}}</div>
                                    <div class="w-28 border-l border-black/70 p-1 text-center">{{ (generateDescriptive(child.rate)) }}</div>
                                </div>
                            </li>
                        </div>
                        <div class="">
                            <div>
                                <div class="flex ml-2 pl-4">
                                    <div class="w-28 grow flex items-center py-1 justify-end pr-1 font-bold">Total:</div>
                                    <div class="w-28 border-b-2  border-black/70 p-1 flex items-center justify-center">{{ total }} </div>
                                    <div class="w-28"></div>
                                </div>
                            </div>
                        </div>
                        <div class="">
                            <div>
                                <div class="flex ml-2 pl-4">
                                    <div class="w-28 grow flex items-center py-1 justify-end pr-1 font-bold">Mean:</div>
                                    <div class="w-28 border-b-2  border-black/70 p-1 flex items-center justify-center">{{ (Math.floor((total/($page.props.area[0].children.length))*100)/100).toFixed(2) }}</div>
                                    <div class="w-28 text-center"> {{ (generateDescriptive((total/($page.props.area[0].children.length)).toFixed(2))) }}</div>
                                </div>
                            </div>
                        </div>
                    </ol>
                </div>

                <div class="uppercase text-center mt-20">
                    <div class="font-semibold mb-3">Lead accreditor/s</div>

                    <ul>
                        <li v-for="(item, index) in $page.props.self_accreditors" :key="index">{{ item.user }} </li>
                    </ul>
                </div>
            </div>



        </div>
    </div>
</template>
