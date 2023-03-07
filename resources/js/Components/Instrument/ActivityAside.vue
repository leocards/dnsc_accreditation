<template>
    <div class="w-full p-2 rounded dark:bg-tertiaryDarkBg/50 bg-whiteBg mb-0.5">
        <div class="text-xs mb-0.5">
            <Time :time="activity.time" />
        </div>

        <div>{{activity.name}} <span class="text-xs dark:text-white/60 inter-font">{{activity.detail}}</span></div>

        <div class="dark:bg-white/10 bg-gray-300/80 p-2 mt-2 rounded">
            <div class="Twoline">
                {{activity.title}}
            </div>
        </div>
    </div>
</template>

<script setup>
import Time from './../DateTime.vue'

const props = defineProps({
    activity:Object
})

</script>