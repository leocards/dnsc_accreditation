<template>
    <div class="w-fit h-fit relative">
        <div class="w-fit h-fit relative ml-2"
            v-click-outside="hideOptions">
            <button type="button" class="w-44 rounded flex items-center justify-between text-white dark:text-white/80 bg-dnscGreen px-2 pl-3.5 py-2.5 
            dark:shadow-[inset_0_1px_0_0_hsl(0deg_0%_100%_/_5%)]"
            @click="showOptions = !showOptions">
                <div v-if="$page.props.currentProgram">{{$page.props.currentProgram.program}}</div>
                <div v-else>No program</div>
                <DownIcon />
            </button>

            <div class="absolute min-h-[0rem] w-full py-2 bg-white dark:bg-tertiaryDarkBg myshadow top-10 left-0 rounded" v-if="showOptions">
                <ul>
                    <Link :href="'/task'" :data="{ program: program.id }" v-for="program in $page.props.program">
                        <li class="px-2 py-2 flex items-center gap-2.5 hover:bg-black/10 dark:hover:bg-white/20 transition_300">
                            {{program.program}}
                        </li>
                    </Link>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import SearchIcon from '../Icons/searchIcon.vue'
import DownIcon from '../Icons/downIcon.vue'
import { ref } from '@vue/reactivity'

const showOptions = ref(false)

const hideOptions = () => {
    showOptions.value = false
}
</script>