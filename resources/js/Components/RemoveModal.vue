<template>
    <Modal @handleClose="$emit('handleClose')">
        <template #header>
            <div class="text-red-400 text-2xl">
                <slot name="removeHeader" />
            </div>
        </template>

        <div class="w-full min-h-[6.8rem] flex flex-col">
            <slot />
                
            <div class="mt-auto flex gap-2 justify-end pb-3">
                <Cancel @click="$emit('handleCancel')" />
                <DeleteButton @click="$emit('handleDelete')">
                    <slot name="deleteLabel" />
                </DeleteButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from './Modal.vue'
import Cancel from './Buttons/Cancel.vue'
import DeleteButton from './Buttons/Delete.vue'

const emits = defineEmits([
    'handleClose',
    'handleCancel',
    'handleDelete'
])

</script>