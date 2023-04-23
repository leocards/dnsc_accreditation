<template>
    <Modal @handleClose="$emit('handleClose')">
        <div class="border border-slate-200 dark:border-primaryDarkBorder rounded-md mt-6 w-[35rem] flex" v-if="user != 'error' && user">
            <div class="p-4">
                
                <div class="w-20 h-20 rounded-full overflow-hidden">
                    <Avatar :avatar="$page.props.user.img" />
                </div>

            </div>

            <div class="p-3 grow">
                <form @submit.prevent="saveChanges" class="flex-col flex" v-if="!isChangePass">
                    <div type="text" @click="triggerUpload" v-if="!hasAttachedAvatar" class="formInput flex items-center cursor-default hover:bg-white/20">Choose avatar</div>

                    <div :class="{'hidden':!hasAttachedAvatar}">
                        <FilePond
                            className="filepond--panel-root filepond--drop-label"
                            ref="pond"
                            max-files="1"
                            credits="false"
                            name="fileUpload"
                            maxFileSize="3MB"
                            allow-multiple="false"
                            :allowFileTypeValidation="true"
                            :acceptedFileTypes="['image/jpg', 'image/jpeg', 'image/JPG', 'image/JPEG']"
                            :server="{
                                url: '',
                                timeout: 7000,
                                process: {
                                    url: '/user/profile/tmp',
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $page.props.ct,
                                    },
                                    withCredentials: false,
                                    onload: handleFilepondLoad,
                                    onerror: (err) => { errorFilePond(err) }
                                },
                                revert: handleFilepondRevert
                            }"
                            @removefile="fileRemoved"
                            @processfilestart="processfile"
                        />
                    </div>

                    <div class="mt-3">
                        <label for="lastname">Last name</label>
                        <span class="errorMessage" v-if="userForm.errors.last_name">{{userForm.errors.last_name}}</span>
                        <input type="text" id="lastname" placeholder="Last name" class="formInput mt-0.5" @input="isModified = true" :disabled="isEdit" v-model="userForm.last_name">
                    </div>

                    <div class="mt-3">
                        <label for="firstname">First name</label>
                        <span class="errorMessage" v-if="userForm.errors.first_name">{{userForm.errors.first_name}}</span>
                        <input type="text" id="firstname" placeholder="First name" class="formInput mt-0.5" @input="isModified = true" :disabled="isEdit" v-model="userForm.first_name">
                    </div>

                    <div class="mt-3">
                        <label for="username">Username</label>
                        <span class="errorMessage" v-if="userForm.errors.username">{{userForm.errors.username}}</span>
                        <input type="text" id="username" placeholder="Username" class="formInput mt-0.5" @input="isModified = true" :disabled="isEdit" v-model="userForm.username">
                    </div>

                    <div class="w-full mt-3">
                        <Submit width="w-full" :disabled="!isModified || userForm.processing">
                            Save changes
                        </Submit>
                    </div>

                    <div class="border-b border-slate-200 dark:border-primaryDarkBorder my-3"></div>

                    <button type="button" class="w-fit hover:text-dnscGreen transition_300" @click="isChangePass = true">Change password</button>
                </form>

                <form @submit.prevent="changePassword" class="flex-col flex" v-else>
                    <div class="">
                        <label for="password">Password</label>
                        <span class="errorMessage" v-if="userForm.errors.password">{{userForm.errors.password}}</span>
                        <input type="password" id="password" placeholder="password" class="formInput mt-0.5" @input="isModified = true" :disabled="isEdit" v-model="userForm.password">
                    </div>

                    <div class="mt-3">
                        <label for="confirm_password">Confirm password</label>
                        <span class="errorMessage" v-if="userForm.errors.confirm_password">{{userForm.errors.confirm_password}}</span>
                        <input type="password" id="confirm_password" placeholder="confirm password" class="formInput mt-0.5" @input="isModified = true" :disabled="isEdit" v-model="userForm.confirm_password">
                    </div>

                    <div class="w-full mt-3">
                        <Submit width="w-full">
                            Save changes
                        </Submit>
                    </div>

                    <div class="border-b border-slate-200 dark:border-primaryDarkBorder my-3"></div>

                    <button type="button" class="w-fit hover:text-dnscGreen transition_300" @click="isChangePass = false">Cancel</button>
                </form>
            </div>
        </div>

        <div class="w-full h-52 flex items-center justify-center" v-if="!user || user == 'error'">
            <Loading v-if="!user" />
            <div v-if="user == 'error'">
                Something went wrong
            </div>
        </div>
    </Modal>

    <input type="hidden" :value="loadUser(user)">
</template>

<script setup>
import "filepond/dist/filepond.min.css"
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css"
import Modal from '../Modal.vue'
import Avatar from '../Avatar.vue'
import Loading from '../Loading.vue'
import Submit from '../Buttons/Submit.vue'
import vueFilePond from "vue-filepond"
import FilePondPluginImagePreview from "filepond-plugin-image-preview"
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type"
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
import { useForm } from '@inertiajs/inertia-vue3'
import { ref } from '@vue/reactivity'
import axios from 'axios'
import { onBeforeMount, onMounted } from '@vue/runtime-core'

const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

const props = defineProps({
    user: Object
})
const emits = defineEmits(['handleClose'])

const userForm = useForm({
    last_name: null,
    first_name: null,
    username: null,
    designation: null,
    password: null,
    confirm_password: null,
    avatar: null,
    avatarId: null,
})

const isEdit = ref(false)
const isModified = ref(false)
const isChangePass = ref(false)
const hasAttachedAvatar = ref(false)

const changePassword = () => {
    if(userForm.password != userForm.confirm_password)
    {
        userForm.errors.confirm_password = 'Password not matched'
    }else if(userForm.password < 8) {
        userForm.errors.password = 'Password must be atleast 8 characters'
    }else{
        userForm.post('/user/change_password', {
            onSuccess: page => {
                if(page.props.flash.success)
                    emits('handleClose')
            }
        })
    }
}

const triggerUpload = () => {
    document.querySelector('.filepond--label-action').click()
}

const fileRemoved = file => {
    hasAttachedAvatar.value = false
}

const processfile = () => {
    hasAttachedAvatar.value = true
}

const errorFilePond = error => {
    
}

const handleFilepondLoad = res => {
    userForm.avatarId = res
    isModified.value = true
}

const handleFilepondRevert = (uniqueId, load, error) => {
    axios.post(`/user/profile/tmp_revert`, {
        tempId: userForm.avatarId
    })
    .then(res => {
        if(res.data == 'success')
        {
            userForm.reset()
            loadUser(props.user)
            isModified.value = false
        }else{
            //console.log(res.data)
        }
    })
}

const loadUser = user => {
    if(props.user){
        userForm.last_name = props.user.last_name
        userForm.first_name = props.user.first_name
        userForm.username = props.user.user_name
        userForm.avatar = props.user.avatar
    }
}

const saveChanges = () => {
    userForm.post('/user/profile_update', {
        onSuccess: page => {
            if(page.props.flash.success)
                emits('handleClose', true)
            else ''
                //console.log(page.props.flash.error)
        }
    })
}

</script>