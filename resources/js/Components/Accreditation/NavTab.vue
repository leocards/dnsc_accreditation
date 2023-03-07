<template>
    <div class="h-full flex gap-1.5">
        <Link 
            :href="'/accreditation/instrument'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url.startsWith('/accreditation/instrument') ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-2 py-2.5 flex gap-2">
                Instrument
            </div>
        </Link>
        <Link 
            :href="'/accreditation'"
            class="h-full flex items-center border-b-[3px] justify-center mr-2.5 transition_300"
            :class="[$page.url === '/accreditation' ? 'border-dnscGreen text-dnscGreen font-semibold':'group hover:border-dnscGreen border-transparent']"
        >
            <div class="rounded group-hover:bg-dnscGreen/20 group-hover:text-dnscGreen px-2 py-2.5 flex gap-2">
                Accreditation
            </div>
        </Link>
    </div>
</template>