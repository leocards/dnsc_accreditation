<template>
    <div class="text-xs" v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/60)) < 1">
        Now
    </div>

    <div class="text-xs" v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/60)) >= 1 && Math.abs(Math.floor(((new Date() - new Date(time))/1000)/60)) <= 60">
        <span>
            {{Math.abs(Math.floor(((new Date() - new Date(time))/1000)/60))}}m
        </span>
    </div>

    <div class="text-xs" v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) >= 1 && Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) < 24">
        <span v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) <= 1">1 hr</span>
        <span v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) > 1">
            {{Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60)))}} hrs
        </span>
    </div>

    <div class="text-xs" v-if="Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) >= 24 && Math.abs(Math.floor(((new Date() - new Date(time))/1000)/(60*60))) <= 168">
        <span >
            {{Math.abs(Math.floor((((new Date() - new Date(time))/1000)/(60*60))/24))}}d
        </span>
    </div>

    <div class="text-xs" v-if="Math.abs(Math.floor((((new Date() - new Date(time))/1000)/(60*60))/24)) >= 7 && !mmDD">
        {{new Date(time).toLocaleString('en-US', {year: "numeric",month: "short",day: "numeric", hour: "numeric", minute: "2-digit"})}}
    </div>

    <div class="text-xs" v-if="Math.abs(Math.floor((((new Date() - new Date(time))/1000)/(60*60))/24)) >= 7 && mmDD">
        {{new Date(time).toLocaleString('en-US', {month: "short",day: "numeric"})}}
    </div>
</template>

<script setup>

const props = defineProps({
    time: String,
    mmDD: Boolean
})

</script>