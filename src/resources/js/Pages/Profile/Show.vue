<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import { ShieldAlert, Download, X, User, Lock, DatabaseZap } from 'lucide-vue-next';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';

// --- INTERFACES PARA TIPAGEM ---
interface Bairro {
    id: number;
    nome: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    bairro_id: number | null; // ATUALIZADO: para receber o ID do bairro
    profile_photo_url?: string;
    profile_data: Record<string, any>;
}

interface CustomField {
    id: number;
    name: string;
    label: string;
    type: string;
}

interface Props {
    confirmsTwoFactorAuthentication?: boolean;
    sessions?: any[];
    customFields?: CustomField[];
    bairros?: Bairro[]; // ADICIONADO: para receber a lista de bairros
}

// --- PROPS ---
const props = withDefaults(defineProps<Props>(), {
    sessions: () => [],
    customFields: () => [],
    bairros: () => [], // ADICIONADO
});

// --- ESTADO DO COMPONENTE ---
const page = usePage();
const user = page.props.auth.user as User;

const getCustomFieldsInitialState = () => {
    const initialState: Record<string, any> = {};
    if (props.customFields && user.profile_data) {
        props.customFields.forEach(field => {
            initialState[field.name] = user.profile_data[field.name] || '';
        });
    }
    return initialState;
};

// --- FORMULÁRIO DE INFORMAÇÕES DE PERFIL ---
const formInfo = useForm({
    _method: 'PUT',
    name: user.name,
    email: user.email,
    photo: null as File | null,
    // ADICIONADO: campo para o ID do bairro
    bairro_id: user.bairro_id || null,
    // Campos de endereço (bairro foi removido do profile_data)
    telefone: user.profile_data?.telefone || '',
    endereco_cep: user.profile_data?.endereco_cep || '',
    endereco_logradouro: user.profile_data?.endereco_logradouro || '',
    endereco_numero: user.profile_data?.endereco_numero || '',
    endereco_cidade: user.profile_data?.endereco_cidade || '',
    endereco_estado: user.profile_data?.endereco_estado || '',
    // Campos pessoais
    data_nascimento: user.profile_data?.data_nascimento || '',
    genero: user.profile_data?.genero || '',
    nome_mae: user.profile_data?.nome_mae || '',
    nome_pai: user.profile_data?.nome_pai || '',
    // Adiciona os campos personalizados dinamicamente ao formulário
    ...getCustomFieldsInitialState(),
});

const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

const updateProfileInformation = () => {
    if (photoInput.value?.files?.[0]) {
        formInfo.photo = photoInput.value.files[0];
    }

    formInfo.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];
    if (!photo) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(photo);
};

const clearPhotoFileInput = () => {
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

// --- FORMULÁRIO DE ATUALIZAÇÃO DE SENHA ---
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

// --- MODAL DE ANONIMIZAÇÃO ---
const isAnonymizeModalOpen = ref(false);
const openAnonymizeModal = () => isAnonymizeModalOpen.value = true;
const closeAnonymizeModal = () => isAnonymizeModalOpen.value = false;
const anonymizeAccount = () => {
    router.post(route('profile.anonymize-account'), {}, {
        onSuccess: () => closeAnonymizeModal(),
        preserveScroll: true,
    });
};

// --- CONTROLE DE ABAS ---
const activeTab = ref('profile');
const selectTab = (tab: string) => activeTab.value = tab;

const tabs = {
    profile: { name: 'Perfil', icon: User },
    security: { name: 'Segurança', icon: Lock },
    data: { name: 'Gestão de Dados', icon: DatabaseZap },
};

</script>

<template>
    <TenantLayout title="Meu Perfil">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Configurações da Conta
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
                <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                    <nav class="space-y-1">
                        <button v-for="(tab, key) in tabs" :key="key" @click="selectTab(key)"
                                :class="[
                                    activeTab === key
                                        ? 'bg-gray-100 dark:bg-gray-700 text-emerald-600 dark:text-emerald-400'
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                    'group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full text-left'
                                ]">
                            <component :is="tab.icon" :class="[
                                activeTab === key ? 'text-emerald-500' : 'text-gray-400 group-hover:text-gray-500',
                                'flex-shrink-0 -ml-1 mr-3 h-6 w-6'
                            ]" />
                            <span class="truncate">{{ tab.name }}</span>
                        </button>
                    </nav>
                </aside>

                <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                    <!-- Aba de Perfil -->
                    <div v-if="activeTab === 'profile'">
                        <form @submit.prevent="updateProfileInformation">
                            <div class="shadow sm:rounded-md sm:overflow-hidden">
                                <div class="bg-white dark:bg-gray-800 py-6 px-4 space-y-6 sm:p-6">
                                    <div>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Informações do Perfil</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize as informações de perfil e o endereço de e-mail da sua conta.</p>
                                    </div>

                                    <!-- Profile Photo: Apenas mostra esta seção se a feature estiver habilitada no Jetstream -->
                                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                                        <InputLabel for="photo" value="Foto" />
                                        <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">
                                        <div class="mt-2 flex items-center">
                                            <span v-show="!photoPreview" class="block h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                                                <img :src="user.profile_photo_url" :alt="user.name" class="h-full w-full object-cover">
                                            </span>
                                            <span v-show="photoPreview" class="block w-20 h-20 rounded-full"
                                                  :style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'" />

                                            <SecondaryButton class="ms-5" type="button" @click.prevent="selectNewPhoto">
                                                Selecionar Nova Foto
                                            </SecondaryButton>
                                            <InputError :message="formInfo.errors.photo" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="name" value="Nome" />
                                            <TextInput id="name" v-model="formInfo.name" type="text" class="mt-1 block w-full" required autocomplete="name" />
                                            <InputError :message="formInfo.errors.name" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="email" value="E-mail" />
                                            <TextInput id="email" v-model="formInfo.email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                                            <InputError :message="formInfo.errors.email" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="telefone" value="Telefone" />
                                            <TextInput id="telefone" v-model="formInfo.telefone" type="text" class="mt-1 block w-full" autocomplete="tel" />
                                            <InputError :message="formInfo.errors.telefone" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="data_nascimento" value="Data de Nascimento" />
                                            <TextInput id="data_nascimento" v-model="formInfo.data_nascimento" type="date" class="mt-1 block w-full" />
                                            <InputError :message="formInfo.errors.data_nascimento" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="genero" value="Gênero" />
                                            <TextInput id="genero" v-model="formInfo.genero" type="text" class="mt-1 block w-full" />
                                            <InputError :message="formInfo.errors.genero" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="nome_mae" value="Nome da Mãe" />
                                            <TextInput id="nome_mae" v-model="formInfo.nome_mae" type="text" class="mt-1 block w-full" />
                                            <InputError :message="formInfo.errors.nome_mae" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="nome_pai" value="Nome do Pai" />
                                            <TextInput id="nome_pai" v-model="formInfo.nome_pai" type="text" class="mt-1 block w-full" />
                                            <InputError :message="formInfo.errors.nome_pai" class="mt-2" />
                                        </div>

                                        <div class="col-span-6"><hr class="dark:border-gray-700"/></div>

                                        <div class="col-span-6 sm:col-span-2">
                                            <InputLabel for="endereco_cep" value="CEP" />
                                            <TextInput id="endereco_cep" v-model="formInfo.endereco_cep" type="text" class="mt-1 block w-full" autocomplete="postal-code" />
                                            <InputError :message="formInfo.errors.endereco_cep" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-4">
                                            <InputLabel for="endereco_logradouro" value="Logradouro" />
                                            <TextInput id="endereco_logradouro" v-model="formInfo.endereco_logradouro" type="text" class="mt-1 block w-full" autocomplete="street-address" />
                                            <InputError :message="formInfo.errors.endereco_logradouro" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-2">
                                            <InputLabel for="endereco_numero" value="Número" />
                                            <TextInput id="endereco_numero" v-model="formInfo.endereco_numero" type="text" class="mt-1 block w-full" />
                                            <InputError :message="formInfo.errors.endereco_numero" class="mt-2" />
                                        </div>

                                        <!-- MODIFICADO: Campo de bairro agora é um select -->
                                        <div class="col-span-6 sm:col-span-4">
                                            <InputLabel for="bairro_id" value="Bairro" />
                                            <select id="bairro_id" v-model="formInfo.bairro_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-md shadow-sm">
                                                <option :value="null">Selecione um bairro</option>
                                                <option v-for="bairro in bairros" :key="bairro.id" :value="bairro.id">
                                                    {{ bairro.nome }}
                                                </option>
                                            </select>
                                            <InputError :message="formInfo.errors.bairro_id" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="endereco_cidade" value="Cidade" />
                                            <TextInput id="endereco_cidade" v-model="formInfo.endereco_cidade" type="text" class="mt-1 block w-full" autocomplete="address-level2" />
                                            <InputError :message="formInfo.errors.endereco_cidade" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <InputLabel for="endereco_estado" value="Estado" />
                                            <TextInput id="endereco_estado" v-model="formInfo.endereco_estado" type="text" class="mt-1 block w-full" autocomplete="address-level1" />
                                            <InputError :message="formInfo.errors.endereco_estado" class="mt-2" />
                                        </div>

                                        <!-- Campos Personalizados -->
                                        <template v-if="customFields.length">
                                            <div class="col-span-6"><hr class="dark:border-gray-700"/></div>
                                            <div v-for="field in customFields" :key="field.id" class="col-span-6 sm:col-span-3">
                                                <InputLabel :for="field.name" :value="field.label" />
                                                <TextInput :id="field.name" v-model="formInfo[field.name as keyof typeof formInfo]" :type="field.type" class="mt-1 block w-full" />
                                                <InputError :message="formInfo.errors[field.name]" class="mt-2" />
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-right sm:px-6">
                                    <ActionMessage :on="formInfo.recentlySuccessful" class="me-3">
                                        Salvo com sucesso.
                                    </ActionMessage>
                                    <PrimaryButton class="bg-[#059669] hover:bg-emerald-700" :class="{ 'opacity-25': formInfo.processing }" :disabled="formInfo.processing">
                                        Salvar Alterações
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Aba de Segurança -->
                    <div v-if="activeTab === 'security'" class="space-y-6">
                         <form @submit.prevent="updatePassword">
                            <div class="shadow sm:rounded-md sm:overflow-hidden">
                                <div class="bg-white dark:bg-gray-800 py-6 px-4 space-y-6 sm:p-6">
                                      <div>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Atualizar Senha</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Garanta que a sua conta esteja a usar uma senha longa e aleatória para se manter segura.</p>
                                    </div>
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-6 sm:col-span-4">
                                            <InputLabel for="current_password" value="Senha Atual" />
                                            <TextInput id="current_password" v-model="formPassword.current_password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                                            <InputError :message="formPassword.errors.current_password" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-4">
                                            <InputLabel for="password" value="Nova Senha" />
                                            <TextInput id="password" v-model="formPassword.password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                            <InputError :message="formPassword.errors.password" class="mt-2" />
                                        </div>

                                        <div class="col-span-6 sm:col-span-4">
                                            <InputLabel for="password_confirmation" value="Confirmar Nova Senha" />
                                            <TextInput id="password_confirmation" v-model="formPassword.password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                            <InputError :message="formPassword.errors.password_confirmation" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 text-right sm:px-6">
                                    <ActionMessage :on="formPassword.recentlySuccessful" class="me-3">
                                        Senha salva.
                                    </ActionMessage>
                                    <PrimaryButton class="bg-[#059669] hover:bg-emerald-700" :class="{ 'opacity-25': formPassword.processing }" :disabled="formPassword.processing">
                                        Salvar Senha
                                    </PrimaryButton>
                                </div>
                            </div>
                         </form>

                        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                            <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                        </div>

                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>

                    <!-- Aba de Gestão de Dados -->
                    <div v-if="activeTab === 'data'" class="space-y-6">
                        <div class="shadow sm:rounded-md bg-white dark:bg-gray-800">
                             <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Privacidade e Gestão de Dados
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Exerça os seus direitos sobre os seus dados pessoais, conforme a Lei Geral de Proteção de Dados (LGPD).
                                </p>
                                <div class="mt-5 flex items-center space-x-4">
                                    <a :href="route('profile.export-data')" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                        <Download class="w-5 h-5 mr-2" />
                                        Exportar Meus Dados
                                    </a>
                                    <button @click="openAnonymizeModal" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <ShieldAlert class="w-5 h-5 mr-2" />
                                        Anonimizar Minha Conta
                                    </button>
                                </div>
                            </div>
                        </div>

                        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                            <DeleteUserForm />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmação de Anonimização -->
        <TransitionRoot appear :show="isAnonymizeModalOpen" as="template">
            <Dialog as="div" @close="closeAnonymizeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-confirmation-panel">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                    <span>Confirmar Anonimização da Conta</span>
                                    <button @click="closeAnonymizeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                </DialogTitle>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        A anonimização é um processo definitivo para garantir a privacidade dos dados de acordo com a LGPD. Esta ação <strong>removerá permanentemente</strong> todas as informações pessoais da sua conta. O histórico de atividades será mantido para fins de métricas, de forma anônima e irreversível.
                                    </p>
                                </div>
                                <div class="mt-6 flex justify-end space-x-3">
                                    <SecondaryButton @click="closeAnonymizeModal">Cancelar</SecondaryButton>
                                    <button type="button" @click="anonymizeAccount" class="btn-danger">Confirmar Anonimização</button>
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
.modal-confirmation-panel { @apply w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all; }
.btn-danger { @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10 focus:outline-none; }
</style>
