<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import { ShieldAlert, Download, X } from 'lucide-vue-next';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

interface Props {
    confirmsTwoFactorAuthentication?: boolean;
    sessions?: any[];
    customFields?: any[]; // Adicionei essa prop para receber os campos personalizados do backend
}

const props = withDefaults(defineProps<Props>(), {
    sessions: () => [],
    customFields: () => [], // Defini o valor padrão
});

const page = usePage();
const user = page.props.auth.user;

// Inicializa a variável profileData, que deve ser um objeto já desserializado pelo backend.
const profileData = user.profile_data || {};

const formInfo = useForm({
    _method: 'PUT',
    name: user.name,
    email: user.email,
    photo: null,
    // Popula os campos de profile_data a partir do objeto profileData
    telefone: profileData.telefone || '',
    endereco_cep: profileData.endereco_cep || '',
    endereco_logradouro: profileData.endereco_logradouro || '',
    endereco_numero: profileData.endereco_numero || '',
    endereco_bairro: profileData.endereco_bairro || '',
    endereco_cidade: profileData.endereco_cidade || '',
    endereco_estado: profileData.endereco_estado || '',
    // Adiciona e popula os campos personalizados dinamicamente
    ...Object.fromEntries(props.customFields.map(field => [field.name, profileData[field.name] || ''])),
});

const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        formInfo.photo = photoInput.value.files?.[0];
    }
    // Incluir o profile_data no payload
    const dataToSend = {
        ...formInfo.data(),
        profile_data: {
            telefone: formInfo.telefone,
            endereco_cep: formInfo.endereco_cep,
            endereco_logradouro: formInfo.endereco_logradouro,
            endereco_numero: formInfo.endereco_numero,
            endereco_bairro: formInfo.endereco_bairro,
            endereco_cidade: formInfo.endereco_cidade,
            endereco_estado: formInfo.endereco_estado,
            // Adicionar os campos personalizados ao profile_data
            ...Object.fromEntries(props.customFields.map(field => [field.name, formInfo[field.name]])),
        },
    };

    formInfo.post(route('user-profile-information.update'), {
        data: dataToSend, // Enviar os dados formatados
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const formPassword = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    formPassword.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => formPassword.reset(),
        onError: () => {
            if (formPassword.errors.password) {
                formPassword.reset('password', 'password_confirmation');
            }
            if (formPassword.errors.current_password) {
                formPassword.reset('current_password');
            }
        },
    });
};

const isAnonymizeModalOpen = ref(false);
const openAnonymizeModal = () => {
    isAnonymizeModalOpen.value = true;
};
const closeAnonymizeModal = () => {
    isAnonymizeModalOpen.value = false;
};
const anonymizeAccount = () => {
    router.post(route('profile.anonymize-account'), {}, {
        onSuccess: () => closeAnonymizeModal(),
        preserveScroll: true,
    });
};

const activeTab = ref('profile');

const selectTab = (tab: string) => {
    activeTab.value = tab;
};

// Exemplo de como buscar os campos personalizados do backend
// Você precisará de uma rota no seu backend que retorne esses dados
onMounted(async () => {
    try {
        const response = await fetch(route('custom-fields.index')); // Crie esta rota no seu backend
        const data = await response.json();
        props.customFields.value = data;
    } catch (error) {
        console.error('Erro ao buscar campos personalizados:', error);
    }
});
</script>

<template>
    <TenantLayout title="Perfil">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Perfil
            </h2>
        </template>
        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a href="#" @click.prevent="selectTab('profile')" :class="{ 'border-emerald-500 text-emerald-600 dark:text-emerald-400': activeTab === 'profile', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'profile' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Informações do Perfil
                        </a>
                        <a href="#" @click.prevent="selectTab('security')" :class="{ 'border-emerald-500 text-emerald-600 dark:text-emerald-400': activeTab === 'security', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'security' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Segurança
                        </a>
                        <a href="#" @click.prevent="selectTab('data-management')" :class="{ 'border-emerald-500 text-emerald-600 dark:text-emerald-400': activeTab === 'data-management', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'data-management' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Gestão de Dados
                        </a>
                    </nav>
                </div>

                <div class="mt-8">
                    <div v-if="activeTab === 'profile'">
                        <FormSection @submitted="updateProfileInformation">
                            <template #title>
                                Informações do Perfil
                            </template>
                            <template #description>
                                Atualize as informações de perfil e o endereço de e-mail da sua conta.
                            </template>
                            <template #form>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="name" value="Nome" />
                                    <TextInput id="name" v-model="formInfo.name" type="text" class="mt-1 block w-full" required autocomplete="name" />
                                    <InputError :message="formInfo.errors.name" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="email" value="E-mail" />
                                    <TextInput id="email" v-model="formInfo.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                                    <InputError :message="formInfo.errors.email" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="telefone" value="Telefone" />
                                    <TextInput id="telefone" v-model="formInfo.telefone" type="text" class="mt-1 block w-full" autocomplete="tel" />
                                    <InputError :message="formInfo.errors.telefone" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_cep" value="CEP" />
                                    <TextInput id="endereco_cep" v-model="formInfo.endereco_cep" type="text" class="mt-1 block w-full" autocomplete="postal-code" />
                                    <InputError :message="formInfo.errors.endereco_cep" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_logradouro" value="Logradouro" />
                                    <TextInput id="endereco_logradouro" v-model="formInfo.endereco_logradouro" type="text" class="mt-1 block w-full" autocomplete="street-address" />
                                    <InputError :message="formInfo.errors.endereco_logradouro" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_numero" value="Número" />
                                    <TextInput id="endereco_numero" v-model="formInfo.endereco_numero" type="text" class="mt-1 block w-full" autocomplete="address-line2" />
                                    <InputError :message="formInfo.errors.endereco_numero" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_bairro" value="Bairro" />
                                    <TextInput id="endereco_bairro" v-model="formInfo.endereco_bairro" type="text" class="mt-1 block w-full" autocomplete="address-line3" />
                                    <InputError :message="formInfo.errors.endereco_bairro" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_cidade" value="Cidade" />
                                    <TextInput id="endereco_cidade" v-model="formInfo.endereco_cidade" type="text" class="mt-1 block w-full" autocomplete="address-level2" />
                                    <InputError :message="formInfo.errors.endereco_cidade" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="endereco_estado" value="Estado" />
                                    <TextInput id="endereco_estado" v-model="formInfo.endereco_estado" type="text" class="mt-1 block w-full" autocomplete="address-level1" />
                                    <InputError :message="formInfo.errors.endereco_estado" class="mt-2" />
                                </div>
                                <div v-for="field in props.customFields" :key="field.id" class="col-span-6 sm:col-span-4">
                                    <InputLabel :for="field.name" :value="field.label" />
                                    <TextInput :id="field.name" v-model="formInfo[field.name]" :type="field.type" class="mt-1 block w-full" />
                                    <InputError :message="formInfo.errors[field.name]" class="mt-2" />
                                </div>
                            </template>
                            <template #actions>
                                <ActionMessage :on="formInfo.recentlySuccessful" class="me-3">
                                    Salvo.
                                </ActionMessage>
                                <PrimaryButton :class="{ 'opacity-25': formInfo.processing }" :disabled="formInfo.processing">
                                    Salvar
                                </PrimaryButton>
                            </template>
                        </FormSection>
                    </div>

                    <div v-if="activeTab === 'security'">
                        <FormSection @submitted="updatePassword">
                            <template #title>
                                Atualizar Palavra-passe
                            </template>
                            <template #description>
                                Garanta que a sua conta esteja a usar uma palavra-passe longa e aleatória para se manter segura.
                            </template>
                            <template #form>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="current_password" value="Palavra-passe Atual" />
                                    <TextInput id="current_password" v-model="formPassword.current_password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                                    <InputError :message="formPassword.errors.current_password" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="password" value="Nova Palavra-passe" />
                                    <TextInput id="password" v-model="formPassword.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                    <InputError :message="formPassword.errors.password" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <InputLabel for="password_confirmation" value="Confirmar Palavra-passe" />
                                    <TextInput id="password_confirmation" v-model="formPassword.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                    <InputError :message="formPassword.errors.password_confirmation" class="mt-2" />
                                </div>
                            </template>
                            <template #actions>
                                <ActionMessage :on="formPassword.recentlySuccessful" class="me-3">
                                    Salvo.
                                </ActionMessage>
                                <PrimaryButton :class="{ 'opacity-25': formPassword.processing }" :disabled="formPassword.processing">
                                    Salvar
                                </PrimaryButton>
                            </template>
                        </FormSection>

                        <SectionBorder class="my-8" />

                        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                            <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                        </div>

                        <SectionBorder class="my-8" />
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>

                    <div v-if="activeTab === 'data-management'">
                        <div class="md:grid md:grid-cols-3 md:gap-6">
                            <div class="md:col-span-1 flex justify-between">
                                <div class="px-4 sm:px-0">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        Privacidade e Gestão de Dados
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Exerça os seus direitos sobre os seus dados pessoais, conforme a Lei Geral de Proteção de Dados (LGPD).
                                    </p>
                                </div>
                            </div>
                            <div class="mt-5 md:mt-0 md:col-span-2">
                                <div class="px-4 py-5 sm:p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                                    <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                                        Aqui pode exportar todos os seus dados ou solicitar a anonimização da sua conta.
                                    </div>
                                    <div class="mt-5 flex items-center space-x-4">
                                        <a :href="route('profile.export-data')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                            <Download class="w-4 h-4 mr-2" />
                                            Exportar Meus Dados
                                        </a>
                                        <button @click="openAnonymizeModal" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                            <ShieldAlert class="w-4 h-4 mr-2" />
                                            Anonimizar Minha Conta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <SectionBorder class="my-8" />

                        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                            <DeleteUserForm />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isAnonymizeModalOpen" as="template">
            <Dialog as="div" @close="closeAnonymizeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-confirmation-panel !max-w-md p-6">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                    <span>Confirmar Anonimização da Conta</span>
                                    <button @click="closeAnonymizeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        A anonimização é um processo definitivo para garantir a privacidade dos dados de acordo com a Lei Geral de Proteção de Dados (LGPD). Esta ação **removerá permanentemente** todas as informações pessoais da sua conta, como nome e e-mail. No entanto, o histórico de solicitações e atividades será mantido para fins de métricas e auditoria, porém de forma anônima e irreversível.
                                    </p>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" @click="closeAnonymizeModal" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="anonymizeAccount" class="btn-danger">Confirmar</button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </TenantLayout>
</template>

<style scoped>
.modal-confirmation-panel { @apply w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-danger { @apply inline-flex items-center px-4 py-2.5 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
</style>
