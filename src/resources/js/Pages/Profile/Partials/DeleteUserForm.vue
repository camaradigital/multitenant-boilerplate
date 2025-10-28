<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const page = usePage();

const form = useForm({
    password: '',
});

const hasDeletionError = computed(() => !!page.props.flash.error);

const confirmUserDeletion = () => {
    if (page.props.flash.error) {
        page.props.flash.error = null;
    }
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('portalcidadao.profile.destroy'), {
        preserveScroll: true,
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
    page.props.flash.error = null;
};

// Esta função não deve mais ser uma função interna do formulário
const anonymizeAccount = () => {
    router.post(route('portalcidadao.profile.anonymize-account'), {}, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <div class="shadow sm:rounded-md bg-white dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-red-700 dark:text-red-500">
                Deletar Conta
            </h3>
            <div class="mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    Uma vez que sua conta é deletada, todos os seus recursos e dados serão deletados permanentemente. Antes de deletar sua conta, por favor, baixe qualquer dado ou informação que você queira manter.
                </p>
            </div>
            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Deletar Conta
                </DangerButton>
            </div>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <DialogModal :show="confirmingUserDeletion" @close="closeModal">
            <template #title>
                Deletar Conta
            </template>

            <template #content>
                <div v-if="hasDeletionError" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Aviso:</p>
                    <p>{{ page.props.flash.error }}</p>
                </div>

                <p v-else class="text-sm text-gray-600 dark:text-gray-400">
                    Você tem certeza que quer deletar sua conta? Uma vez que sua conta for deletada, todos os seus recursos e dados serão deletados permanentemente. Por favor, insira sua senha para confirmar que você gostaria de deletar permanentemente sua conta.
                </p>

                <div class="mt-4">
                    <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Senha"
                        autocomplete="current-password"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancelar
                </SecondaryButton>

                <DangerButton
                    v-if="!hasDeletionError"
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="deleteUser"
                >
                    Deletar Conta
                </DangerButton>

                <DangerButton
                    v-if="hasDeletionError"
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="anonymizeAccount"
                >
                    Anonimizar Minha Conta
                </DangerButton>
            </template>
        </DialogModal>
    </div>
</template>
