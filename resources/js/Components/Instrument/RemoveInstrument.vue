<script setup>
import Remove from '../RemoveModal.vue'

defineProps({
    inst: Object
})
const emits = defineEmits([
    'handleClose',
    'handleCancel',
    'handleDelete'
])

</script>

<template>
    <Remove
        @handleClose="$emit('handleClose')"
        @handleCancel="$emit('handleCancel')"
        @handleDelete="$emit('handleDelete')"
    >
        <div class="px-3 text-center my-5 mb-10">
            Are you sure you want to delete <span class="font-semibold text-base">{{ inst.title }}</span> ?
            <br>
            This will be permanently deleted along with all of its contents.
        </div>
    </Remove>
</template>