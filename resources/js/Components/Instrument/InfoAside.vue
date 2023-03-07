<template>
        <div class="text-slate-400 mt-6">Title</div>
        <div class="mt-2">{{instrument.title}}</div>

        <div class="text-slate-400 mt-6">Description</div>
        <div class="mt-2 whitespace-pre-wrap break-words">{{instrument.description}}</div>

        <div class="text-slate-400 mt-6" v-if="instrument.category == 'item'">Evidence to Attach</div>
        <div class="mt-2 whitespace-pre-wrap break-words" v-if="instrument.category == 'item'">{{instrument.attachment}}</div>
</template>
<script setup>

const props = defineProps({
    instrument: Object
})

</script>