<template>
    <div class="md:grid md:grid-cols-[repeat(auto-fill,minmax(17rem,1fr))] max-sm:flex max-sm:w-full gap-2">
        <div class="rounded h-[4.5rem] p-1.5 bg-dnscGreen text-whiteBg stat max-sm:w-full">  
            <div class="w-full h-full flex gap-1.5 items-center max-sm:justify-between max-sm:px-2 max-sm_378:flex-col">
                <div class="w-9 flex h-9 rounded-full justify-center items-center bg-whiteBg/30 shrink-0">
                    <i> <UserIcon /> </i>
                </div>
                <div class="w-full max-sm:hidden">Users</div>
                <div class="text-xl shrink-0 w-14 text-center max-sm:text-end max-sm:w-fit max-sm_378:text-sm">{{$page.props.statistic.users}}</div>
            </div>
        </div>

        <div class="rounded h-[4.5rem] p-1.5 bg-dnscGreen text-whiteBg stat max-sm:w-full">  
            <div class="w-full h-full flex gap-1.5 items-center max-sm:justify-between max-sm:px-2 max-sm_378:flex-col">
                <div class="w-9 flex h-9 rounded-full justify-center items-center bg-whiteBg/30 shrink-0">
                    <i> <InstituteIcon /> </i>
                </div>
                <div class="w-full max-sm:hidden">Institutes</div>
                <div class="text-xl shrink-0 w-14 text-center max-sm:text-end max-sm:w-fit max-sm_378:text-sm">{{$page.props.statistic.institute}}</div>
            </div>
        </div>

        <div class="rounded h-[4.5rem] p-1.5 bg-dnscGreen text-whiteBg stat max-sm:w-full">  
            <div class="w-full h-full flex gap-1.5 items-center max-sm:justify-between max-sm:px-2 max-sm_378:flex-col">
                <div class="w-9 flex h-9 rounded-full justify-center items-center bg-whiteBg/30 shrink-0">
                    <i> <ProgramIcon /> </i>
                </div>
                <div class="w-full max-sm:hidden">Programs</div>
                <div class="text-xl shrink-0 w-14 text-center max-sm:text-end max-sm:w-fit max-sm_378:text-sm">{{$page.props.statistic.program}}</div>
            </div>
        </div>

        <div class="rounded h-[4.5rem] p-1.5 bg-dnscGreen text-whiteBg stat max-sm:w-full">  
            <div class="w-full h-full flex gap-1.5 items-center max-sm:justify-between max-sm:px-2 max-sm_378:flex-col">
                <div class="w-9 flex h-9 rounded-full justify-center items-center bg-whiteBg/30 shrink-0">
                    <i> <AccreditedIcon /> </i>
                </div>
                <div class="w-full max-sm:hidden">Accredited</div>
                <div class="text-xl shrink-0 w-14 text-center max-sm:text-end max-sm:w-fit max-sm_378:text-sm">{{$page.props.statistic.accred}}</div>
            </div>
        </div>
    </div>
</template>
<script setup>
import UserIcon from '../Icons/userIcon.vue'
import InstituteIcon from '../Icons/instituteIcon.vue'
import ProgramIcon from '../Icons/programIcon.vue'
import AccreditedIcon from '../Icons/accreditedIcon.vue'

</script>