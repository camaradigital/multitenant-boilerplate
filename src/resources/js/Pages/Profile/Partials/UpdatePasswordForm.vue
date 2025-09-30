<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <div class="shadow sm:rounded-md bg-white dark:bg-gray-800">
        <form @submit.prevent="updatePassword">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Atualizar Palavra-passe
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Garanta que a sua conta esteja a usar uma palavra-passe longa e aleatória para se manter segura.
                </p>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <InputLabel for="current_password" value="Palavra-passe Atual" />
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                        />
                        <InputError :message="form.errors.current_password" class="mt-2" />
                    </div>

                    <div class="sm:col-span-4">
                        <InputLabel for="password" value="Nova Palavra-passe" />
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="sm:col-span-4">
                        <InputLabel for="password_confirmation" value="Confirmar Palavra-passe" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password_confirmation" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-right sm:px-6 flex items-center justify-end space-x-3">
                <ActionMessage :on="form.recentlySuccessful" class="me-3">
                    Salvo.
                </ActionMessage>

                <PrimaryButton class="bg-[#059669] hover:bg-emerald-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Salvar
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
