<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Laptop, Smartphone } from 'lucide-vue-next';

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmLogout = () => {
    confirmingLogout.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;
    form.reset();
};
</script>

<template>
    <div class="shadow sm:rounded-md bg-white dark:bg-gray-800">
         <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Sessões de Navegador
            </h3>

            <div class="mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                <p>
                    Faça logout das suas outras sessões de navegador em todos os seus dispositivos. Se sentir que a sua conta foi comprometida, deve também atualizar a sua senha.
                </p>
            </div>

            <!-- Other Browser Sessions -->
            <div v-if="sessions.length > 0" class="mt-5 space-y-6">
                <div v-for="(session, i) in sessions" :key="i" class="flex items-center">
                    <div>
                        <Laptop v-if="session.agent.is_desktop" class="size-8 text-gray-500 dark:text-gray-400" />
                        <Smartphone v-else class="size-8 text-gray-500 dark:text-gray-400" />
                    </div>

                    <div class="ms-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ session.agent.platform ? session.agent.platform : 'Desconhecido' }} - {{ session.agent.browser ? session.agent.browser : 'Desconhecido' }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">
                                {{ session.ip_address }},

                                <span v-if="session.is_current_device" class="text-emerald-500 font-semibold">Este dispositivo</span>
                                <span v-else>Última atividade {{ session.last_active }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-right sm:px-6 flex items-center justify-between">
             <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Concluído.
            </ActionMessage>

            <PrimaryButton class="bg-[#059669] hover:bg-emerald-700" @click="confirmLogout">
                Sair de outras sessões
            </PrimaryButton>
        </div>


        <!-- Log Out Other Devices Confirmation Modal -->
        <DialogModal :show="confirmingLogout" @close="closeModal">
            <template #title>
                Sair de Outras Sessões de Navegador
            </template>

            <template #content>
                Por favor, introduza a sua senha para confirmar que deseja sair das suas outras sessões de navegador em todos os seus dispositivos.

                <div class="mt-4">
                    <TextInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Senha"
                        autocomplete="current-password"
                        @keyup.enter="logoutOtherBrowserSessions"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancelar
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3 bg-[#059669] hover:bg-emerald-700"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="logoutOtherBrowserSessions"
                >
                    Confirmar e Sair
                </PrimaryButton>
            </template>
        </DialogModal>
    </div>
</template>
