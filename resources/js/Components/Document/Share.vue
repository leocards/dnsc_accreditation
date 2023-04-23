<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            Share document
        </template>

        <div class="mt-4 flex gap-1.5">
            title: 
            <div class="max-w-[30rem]">
                {{document.title}} 
            </div>
        </div>

        <div class="mt-4">
            <Search
                v-model="modelValue"
                placeholder="Search user"
            />

            <div class="border border-slate-200 dark:border-primaryDarkBorder mt-1.5 rounded-md max-h-[14rem] overflow-hidden flex" v-if="modelValue">
                <div class="w-full overflow-y-auto p-1">
                    <div class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard justify-between"
                    v-for="user in searchResult"
                    @click="addUser(user)">
                        {{user.first_name+' '+user.last_name}}

                        <button class="text-xs rounded px-1.5 h-7 hover:bg-dnscGreen hover:text-white transition_300" @click="share(user)">share</button>
                    </div>

                    <div class="flex justify-center p-3" v-if="!searchResult">
                        <Loading />
                    </div>

                    <div class="mx-auto w-full text-center" v-if="myMethod.ifArray(searchResult, '==', 0)">
                        No results found for " {{modelValue}} "
                    </div>
                </div>
            </div>

            <div class="mt-6" v-else>
                <div>Shared with</div>

                <div class="border border-slate-200 dark:border-primaryDarkBorder mt-1.5 rounded-md max-h-[14rem] overflow-hidden flex">
                    <div class="w-full overflow-y-auto p-1">
                        <div class="h-11 w-full hover:bg-gray-300/80 dark:hover:bg-white/10 rounded transition_300 cursor-pointer flex items-center px-3 gap-2.5 inter-font itemCard"
                        v-for="user in sharedWith">
                            {{user.first_name+' '+user.last_name}}
                        </div>

                        <div class="flex justify-center p-3" v-if="!sharedWith">
                            <Loading />
                        </div>

                        <div class="mx-auto w-full text-center" v-if="myMethod.ifArray(sharedWith, '==', 0)">
                            Not shared
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </Modal>
</template>

<script setup>
import Modal from '../Modal.vue'
import Search from '../Search.vue'
import Loading from '../Loading.vue'
import CancelButton from '../Buttons/Cancel.vue'
import SubmitButton from '../Buttons/Submit.vue'
import axios from 'axios'
import { throttle } from 'lodash'
import { ref } from '@vue/reactivity'
import { watch } from '@vue/runtime-core'
import myMethod from '../../Store/Methods'
import { Inertia } from '@inertiajs/inertia'
import { useForm } from '@inertiajs/inertia-vue3'

const props = defineProps({
    document: Object
})
const emits = defineEmits(['handleClose'])

const isEdit = ref(false)
const modelValue = ref('')
const sharedWith = ref([])
const searchResult = ref(null)
const shareForm = useForm({
    userId: null,
    documentId: props.document.docuCurrentId
})

watch(modelValue, throttle((search) => {
    if(search == '')
        searchResult.value = null
    else
        axios.get(`/user/search/shareWith/${search}`)
        .then(res => {
            let filtered = res.data.filter(({id}) => !sharedWith.value.some(user => user.id === id ))
            searchResult.value = filtered.filter(({id}) => id != props.document.userId)
        })

}, 900))

const addUser = user => {
    //sharedWith.value.push(user)
}

const share = user => {
    shareForm.userId = user.id
    shareForm.post('/share/share_document', {
        onSuccess: page => {
            if(page.props.flash.success){
                modelValue.value = ''
                searchResult.value = null
                emits('handleClose')
            }else ''
                //console.table(page.props.flash)
        }
    })
}

try {

    axios.get(`/share/shared_with/${props.document.docuCurrentId}`)
    .then(res => {
        sharedWith.value = res.data
    })

} catch (e) {

}

</script>