<template>
    <Aside :top="top" @handleClose="$emit('handleClose')">
        <template #head>
            Comment
        </template>
        
        <div class="flex-col-reverse flex p-1.5 gap-1">
            <CommentCard :comment="comment" v-for="comment in comments" />
        </div>

        <div class="flex justify-center py-3" v-if="!comments">
            <Loading />
        </div>

        <div class="dark:text-white/50 text-center" v-if="myMethod.ifArray(comments, '==', 0)">
            No comments
        </div>

        <template #input>
            <div class="w-full min-h-[3.1rem] max-h-[9rem] p-2 shrink-0 border-t dark:border-primaryDarkBorder border-slate-300 relative">
                <InputMessage @handleMesage="send" @submitButton="send" />
            </div>
        </template>
    </Aside>
    <input type="hidden" :value="getComments()">
</template>

<script setup>
import Aside from '../../Aside.vue'
import Avatar from '../../Avatar.vue'
import Loading from '../../Loading.vue'
import CommentCard from '../../CommentCard.vue'
import InputMessage from '../../ContentEditable.vue'
import { ref } from '@vue/reactivity'
import myMethod from '../../../Store/Methods'
const props = defineProps({
    instrument: Object,
    accredlvl: Number,
    top: String
})
const emits = defineEmits(['handleClose'])

const comments = ref(null)

const send = val => {
    if(val){
        if(val.trim().length > 0){
            axios.post('/instrument/comment', {
                instrumentId: props.instrument.id,
                accredlvl: props.accredlvl,
                comment: val
            }).then(res => {
                comments.value.push(res.data.response);
            }).catch(error => {
                console.log(error)
            })
        }
    }
}

const getComments = () => {
    try {
        axios.post('/instrument/get/comments', {
            instrumentId: props.instrument.id,
            accredlvl: props.accredlvl
        }).then(res => {
            comments.value = res.data.comments//console.table()
        }).catch(error => {

        })
    } catch (e) {

    }
}

</script>