<template>
    <div class="h-full flex gap-1.5">
        <Link 
            :href="'/user'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url.startsWith('/user') && !$page.url.startsWith('/user/request') ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-3 py-2.5 flex gap-2">
                Users
            </div>
        </Link>
        <Link 
            :href="'/user/request'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url.startsWith('/user/request') ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-2 py-2.5 flex gap-2">
                Requests 
                <div class="rounded-full min-w-[1rem] h-4 px-1 text-center ml-2 shrink-0 text-xs inter-font bg-red-600 text-white"
                v-if="$page.props.requestCount > 0">
                    {{$page.props.requestCount}}
                </div>
            </div>
        </Link>
    </div>
</template>