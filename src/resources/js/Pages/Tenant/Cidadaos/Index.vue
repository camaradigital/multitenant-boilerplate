<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, router, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, UserCheck, X, Eye, ShieldOff, Download, Search } from 'lucide-vue-next';

const props = defineProps({
    cidadaos: Object,
    customFields: Array,
    cidadeTenant: String,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const searchTerm = ref('');
const isDeleteModalOpen = ref(false);
const isAnonymizeModalOpen = ref(false);
const selectedCidadao = ref(null);

// Filtra a lista de cidadãos com base no termo de busca
const filteredCidadaos = computed(() => {
    if (!searchTerm.value) {
        return props.cidadaos.data;
    }
    const lowerCaseSearch = searchTerm.value.toLowerCase();
    return props.cidadaos.data.filter(cidadao => {
        const profileData = cidadao.profile_data || {};
        return (
            (cidadao.name && cidadao.name.toLowerCase().includes(lowerCaseSearch)) ||
            (cidadao.email && cidadao.email.toLowerCase().includes(lowerCaseSearch)) ||
            (cidadao.cpf && cidadao.cpf.includes(lowerCaseSearch)) ||
            (profileData.telefone && profileData.telefone.includes(lowerCaseSearch))
        );
    });
});

// Gera a estrutura do profile_data com campos fixos e personalizados
const generateProfileDataStructure = (fields, data = {}) => {
    const profileData = {
        // Campos fixos
        telefone: data.telefone || '',
        data_nascimento: data.data_nascimento || '',
        genero: data.genero || '',
        nome_mae: data.nome_mae || '',
        nome_pai: data.nome_pai || '',
        endereco_cep: data.endereco_cep || '',
        endereco_logradouro: data.endereco_logradouro || '',
        endereco_numero: data.endereco_numero || '',
        endereco_bairro: data.endereco_bairro || '',
        endereco_cidade: data.endereco_cidade || '',
        endereco_estado: data.endereco_estado || '',
    };
    // Adiciona os campos dinâmicos
    if (fields) {
        fields.forEach(field => {
            profileData[field.name] = data[field.name] || '';
        });
    }
    return profileData;
};

const form = useForm({
    id: null,
    name: '',
    email: '',
    cpf: '',
    profile_data: generateProfileDataStructure(props.customFields),
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    form.profile_data = generateProfileDataStructure(props.customFields);
    isModalOpen.value = true;
};

const editCidadao = (cidadao) => {
    isEditing.value = true;
    form.id = cidadao.id;
    form.name = cidadao.name;
    form.email = cidadao.email;
    form.cpf = cidadao.cpf || '';
    form.profile_data = generateProfileDataStructure(props.customFields, cidadao.profile_data);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.cidadaos.update' : 'admin.cidadaos.store';
    const params = isEditing.value ? { cidadao: form.id } : {};
    const options = { onSuccess: () => closeModal(), preserveScroll: true };

    if (isEditing.value) {
        form.put(route(routeName, params), options);
    } else {
        form.post(route(routeName), options);
    }
};

const openDeleteModal = (cidadao) => {
    selectedCidadao.value = cidadao;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedCidadao.value = null;
};

const deleteCidadao = () => {
    if (selectedCidadao.value) {
        router.delete(route('admin.cidadaos.destroy', selectedCidadao.value.id), {
            onSuccess: () => closeDeleteModal(),
            preserveScroll: true,
        });
    }
};

const openAnonymizeModal = (cidadao) => {
    selectedCidadao.value = cidadao;
    isAnonymizeModalOpen.value = true;
};

const closeAnonymizeModal = () => {
    isAnonymizeModalOpen.value = false;
    selectedCidadao.value = null;
};

const anonymizeCidadao = () => {
    if (selectedCidadao.value) {
        router.post(route('admin.cidadaos.anonymize', selectedCidadao.value.id), {}, {
            onSuccess: () => closeAnonymizeModal(),
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Cidadãos" />
    <TenantLayout title="Cidadãos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gerenciar Cidadãos</h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><UserCheck :size="32" class="icon-in-badge" /></div>
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Cadastro de Cidadãos</h2>
                        <p class="form-subtitle">Adicione e gerencie os cidadãos atendidos.</p>
                    </div>
                    <div class="w-full md:w-auto flex items-center gap-2">
                        <div class="relative w-full md:w-auto">
                            <input type="text" v-model="searchTerm" placeholder="Buscar por nome, e-mail ou CPF..." class="form-input !h-12 !pr-10" />
                            <Search class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        </div>
                        <button @click="openModal" class="btn-primary flex-shrink-0"><Plus class="h-4 w-4 mr-2" />Novo Cidadão</button>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <div v-if="filteredCidadaos.length > 0" class="space-y-4">
                        <div v-for="cidadao in filteredCidadaos" :key="cidadao.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ cidadao.name }}</p>
                                <p class="form-subtitle">{{ cidadao.email }}</p>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <!-- Botão de Visualizar - Adicionado novamente aqui -->
                                <Link :href="route('admin.cidadaos.show', cidadao.id)" class="table-action-btn hover:text-sky-600" title="Visualizar"><Eye class="w-5 h-5" /></Link>
                                <button @click="editCidadao(cidadao)" class="table-action-btn hover:text-amber-600" title="Editar"><Pencil class="w-5 h-5" /></button>
                                <a :href="route('admin.cidadaos.export-data', cidadao.id)" class="table-action-btn hover:text-green-600" title="Exportar Dados (LGPD)">
                                    <Download class="w-5 h-5" />
                                </a>
                                <button @click="openAnonymizeModal(cidadao)" class="table-action-btn hover:text-gray-600 dark:hover:text-gray-300" title="Anonimizar (LGPD)">
                                    <ShieldOff class="w-5 h-5" />
                                </button>
                                <button @click="openDeleteModal(cidadao)" class="table-action-btn hover:text-red-600" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10"><p class="text-gray-500">Nenhum cidadão encontrado.</p></div>
                </div>
                <div class="px-6 pb-4"><Pagination :links="cidadaos.links" /></div>
            </div>
        </div>

        <!-- Modal de Cadastro/Edição -->
        <TransitionRoot appear :show="isModalOpen" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit">
                                    <div class="p-6">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Cidadão' : 'Adicionar Novo Cidadão' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                        <div class="mt-6 space-y-6">
                                            <div class="section-title">Dados de Acesso</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="name" class="form-label">Nome Completo</label>
                                                    <input type="text" v-model="form.name" id="name" class="form-input" required>
                                                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                                </div>
                                                <div>
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" v-model="form.email" id="email" class="form-input" required>
                                                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                                </div>
                                                <div v-if="!isEditing" class="md:col-span-2 text-sm text-gray-500 bg-gray-100 dark:bg-gray-700/50 p-3 rounded-lg">
                                                    A senha será definida pelo cidadão através de um link enviado para o e-mail cadastrado.
                                                </div>
                                            </div>
                                            <div class="section-title pt-4">Dados Pessoais</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="cpf" class="form-label">CPF</label>
                                                    <input type="text" v-model="form.cpf" id="cpf" class="form-input">
                                                    <div v-if="form.errors.cpf" class="form-error">{{ form.errors.cpf }}</div>
                                                </div>
                                                <div>
                                                    <label for="telefone" class="form-label">Telefone</label>
                                                    <input type="text" v-model="form.profile_data.telefone" id="telefone" class="form-input">
                                                </div>
                                                <div>
                                                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                                    <input type="date" v-model="form.profile_data.data_nascimento" id="data_nascimento" class="form-input">
                                                </div>
                                                <div>
                                                    <label for="genero" class="form-label">Gênero</label>
                                                    <select v-model="form.profile_data.genero" id="genero" class="form-input">
                                                        <option value="">Não informar</option>
                                                        <option value="Masculino">Masculino</option>
                                                        <option value="Feminino">Feminino</option>
                                                        <option value="Outro">Outro</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="nome_mae" class="form-label">Nome da Mãe</label>
                                                    <input type="text" v-model="form.profile_data.nome_mae" id="nome_mae" class="form-input">
                                                </div>
                                                <div>
                                                    <label for="nome_pai" class="form-label">Nome do Pai</label>
                                                    <input type="text" v-model="form.profile_data.nome_pai" id="nome_pai" class="form-input">
                                                </div>
                                            </div>
                                            <div class="section-title pt-4">Endereço</div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="md:col-span-1">
                                                    <label for="cep" class="form-label">CEP</label>
                                                    <input type="text" v-model="form.profile_data.endereco_cep" id="cep" class="form-input" />
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="logradouro" class="form-label">Logradouro</label>
                                                    <input type="text" v-model="form.profile_data.endereco_logradouro" id="logradouro" class="form-input">
                                                </div>
                                                <div>
                                                    <label for="numero" class="form-label">Número</label>
                                                    <input type="text" v-model="form.profile_data.endereco_numero" id="numero" class="form-input">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="bairro" class="form-label">Bairro</label>
                                                    <input type="text" v-model="form.profile_data.endereco_bairro" id="bairro" class="form-input">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="cidade" class="form-label">Cidade</label>
                                                    <input type="text" v-model="form.profile_data.endereco_cidade" id="cidade" class="form-input" required>
                                                    <div v-if="form.errors['profile_data.endereco_cidade']" class="form-error">{{ form.errors['profile_data.endereco_cidade'] }}</div>
                                                </div>
                                                <div>
                                                    <label for="estado" class="form-label">Estado</label>
                                                    <input type="text" v-model="form.profile_data.endereco_estado" id="estado" class="form-input">
                                                </div>
                                            </div>
                                            <div v-if="customFields.length > 0" class="section-title pt-4">Informações Adicionais</div>
                                            <div v-if="customFields.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div v-for="field in customFields" :key="field.id">
                                                    <label :for="field.name" class="form-label">{{ field.label }}</label>
                                                    <input v-if="['text', 'number', 'date'].includes(field.type)" :type="field.type" v-model="form.profile_data[field.name]" :id="field.name" class="form-input" :required="field.is_required" />
                                                    <select v-if="field.type === 'select'" v-model="form.profile_data[field.name]" :id="field.name" class="form-input" :required="field.is_required">
                                                        <option value="">Selecione</option>
                                                        <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                                                    </select>
                                                    <div v-if="form.errors[`profile_data.${field.name}`]" class="form-error">{{ form.errors[`profile_data.${field.name}`] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">{{ isEditing ? 'Atualizar' : 'Salvar' }}</button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Modal de Confirmação para Anonimizar -->
        <TransitionRoot appear :show="isAnonymizeModalOpen" as="template">
            <Dialog as="div" @close="closeAnonymizeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-confirmation-panel !max-w-md">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Confirmar Anonimização e Conformidade com a LGPD
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        A anonimização é um processo definitivo para garantir a privacidade dos dados de acordo com a Lei Geral de Proteção de Dados (LGPD). Esta ação **removerá permanentemente** todas as informações pessoais de **{{ selectedCidadao?.name }}**, como nome, e-mail e CPF. No entanto, o histórico de solicitações e atividades será mantido para fins de métricas e auditoria, porém de forma anônima e irreversível.
                                    </p>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" @click="closeAnonymizeModal" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="anonymizeCidadao" class="btn-danger">Confirmar</button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Modal de Confirmação para Excluir -->
        <TransitionRoot appear :show="isDeleteModalOpen" as="template">
            <Dialog as="div" @close="closeDeleteModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-confirmation-panel !max-w-md">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Confirmar Exclusão Definitiva de Cidadão
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        A exclusão definitiva **remove por completo** o perfil de **{{ selectedCidadao?.name }}** e todos os seus dados pessoais do sistema. Esta ação é recomendada apenas para contas que **não possuem histórico** de solicitações ou atividades. Para contas com histórico, a ação de Anonimizar é a opção correta para a privacidade do cidadão. Esta ação não pode ser desfeita.
                                    </p>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" @click="closeDeleteModal" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="deleteCidadao" class="btn-danger">Confirmar</button>
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
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-danger { @apply inline-flex items-center px-4 py-2.5 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-4xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.modal-confirmation-panel { @apply w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply font-semibold text-gray-700 dark:text-gray-300 text-sm border-b border-gray-200 dark:border-gray-700 pb-2; }
</style>
