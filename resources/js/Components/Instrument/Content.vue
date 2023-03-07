<template>
    <div class="pointer-events-none flex grow pr-2">
        <div>{{inst.title}}</div>
        <div class="ml-2 max-w-[34rem] grow qwIOrAQty" v-if="inst.description">{{inst.description}}</div>
        <div class="ml-2 grow qwIOrAQty aqwQdwEr" v-if="inst.attachment">
            <div class="font-semibold ">Evidence to attach</div>
            {{inst.attachment}}
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    inst: Object
})
</script>