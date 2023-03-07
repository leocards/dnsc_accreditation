<template>
    <RemoveModal 
        @handleClose="$emit('handleClose')"
        @handleCancel="$emit('handleCancel')"
        @handleDelete="$emit('handleDelete')"
    >
        <template #deleteLabel>
            Unmark
        </template>

        <div class="mx-auto h-20 pt-7">
            Unmark " {{inst.title}} " as complete
        </div>
        
    </RemoveModal>
</template>

<script setup>
import RemoveModal from '../../RemoveModal.vue'

const props = defineProps({inst: Object})
const emits = defineEmits([
    'handleClose', 
    'handleCancel', 
    'handleDelete'
])


</script>