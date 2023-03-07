<template>
    <div class="w-full grow flex flex-col-reverse overflow-y-auto overscroll-contain">
        <div class="w-full flex flex-col-reverse">
            <div class="w-full flex flex-col">
                <slot name="load"/>

                <div class="w-full flex flex-col-reverse py-2">

                    <slot/>

                </div>

            </div>
        </div>
    </div>
</template>