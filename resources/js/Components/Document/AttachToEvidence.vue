<template>
    <Modal @handleClose="$emit('handleClose')">
        <div class="mt-3 w-[30rem]" v-if="(myMethod.isValidJsonAndEmpty(storeDocument.evidenceAttach.inst.attachment) || storeDocument.evidenceAttach.inst.attachment)">
                <label for="title">Select evidence to attach</label>
            
                <div class="rounded md p-1 5 overflow-y-auto border border-slate-300 min-h-fit max-h-[20rem] mt-2">
                    <div class="flex items-center px-2 h-10 rounded hover:bg-gray-200 cursor-pointer transition_300 gap-1.5"
                        v-for="(item, index) in myMethod.isValidJsonAndEmpty(storeDocument.evidenceAttach.inst.attachment)" :key="index"
                        @click="storeDocument.attachHere(storeDocument.evidenceAttach.accred, storeDocument.evidenceAttach.inst.id, $page.props.flash, index)"
                    >
                        <div class="Oneline h-fit">
                            {{ item.evidence }}
                        </div>
                    </div>
                </div>
                
            </div>
    </Modal>
</template>

<script setup>
import Modal from '../Modal.vue'
import DownIcon from '../Icons/downIcon.vue';
import myMethod from '../../Store/Methods.js'
import { useDocumentStore } from '../../Store/storeDocument';
import { onBeforeMount, ref } from 'vue';

const storeDocument = useDocumentStore()

const emits = defineEmits(['handleClose'])
</script>