<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, Building2, X } from 'lucide-vue-next';
import axios from 'axios';
import { vMaska } from "maska/vue";

const props = defineProps({
    empresas: Object,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

// --- Lógica para o Modal de Exclusão ---
const confirmingEmpresaDeletion = ref(false);
const empresaToDelete = ref(null);
const deleteForm = useForm({});

const confirmEmpresaDeletion = (empresa) => {
    empresaToDelete.value = empresa;
    confirmingEmpresaDeletion.value = true;
};

const deleteEmpresa = () => {
    deleteForm.delete(route('admin.empresas.destroy', empresaToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingEmpresaDeletion.value = false;
            empresaToDelete.value = null;
        },
    });
};

const deleteConfirmationMessage = computed(() => {
    return empresaToDelete.value ? `Tem certeza que deseja remover a empresa "${empresaToDelete.value.nome_fantasia}"? Todas as vagas associadas também serão excluídas.` : '';
});
// --- Fim da Lógica de Exclusão ---

const form = useForm({
    id: null,
    nome_fantasia: '',
    razao_social: '',
    cnpj: '',
    email: '',
    telefone: '',
    endereco: '',
    is_active: true,
});

const getInitials = (name) => {
    if (!name) return '';
    const names = name.split(' ');
    const initials = names.map(n => n[0]).join('');
    return initials.substring(0, 2).toUpperCase();
};

const openModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    realtimeErrors.value = {};
    isModalOpen.value = true;
};

const editEmpresa = (empresa) => {
    isEditing.value = true;
    form.id = empresa.id;
    form.nome_fantasia = empresa.nome_fantasia;
    form.razao_social = empresa.razao_social || '';
    form.cnpj = empresa.cnpj || '';
    form.email = empresa.email;
    form.telefone = empresa.telefone || '';
    form.endereco = empresa.endereco || '';
    form.is_active = empresa.is_active;
    form.clearErrors();
    realtimeErrors.value = {};
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.empresas.update' : 'admin.empresas.store';
    const params = isEditing.value ? { empresa: form.id } : {};

    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    };

    if (isEditing.value) {
        form.put(route(routeName, params), options);
    } else {
        form.post(route(routeName), options);
    }
};

// --- LÓGICA DE VALIDAÇÃO EM TEMPO REAL ---
const realtimeErrors = ref({});
const debounce = (func, delay = 500) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

const validateField = async (fieldName, value) => {
    delete realtimeErrors.value[fieldName];
    if (!value) return;

    try {
        await axios.post(route('realtime.validate'), { field: fieldName, value: value, editingId: form.id });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            realtimeErrors.value[fieldName] = error.response.data[fieldName][0];
        } else {
            console.error('Erro ao validar o campo:', error);
        }
    }
};

const debouncedValidate = debounce(validateField);

watch(() => form.cnpj, (newValue) => { debouncedValidate('cnpj', newValue) });
watch(() => form.email, (newValue) => { debouncedValidate('email', newValue) });
watch(() => form.telefone, (newValue) => { debouncedValidate('telefone', newValue) });

</script>

<template>
    <Head title="Empresas" />

    <TenantLayout title="Empresas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Empresas
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
                    <div class="form-icon"><Building2 :size="32" class="icon-in-badge" /></div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Catálogo de Empresas</h2>
                            <p class="form-subtitle">Adicione e gerencie as empresas parceiras.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="openModal" class="btn-primary">
                                <Plus class="h-4 w-4 mr-2" />
                                Nova Empresa
                            </button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="empresas.data.length > 0">
                             <ul class="divide-y divide-gray-200 dark:divide-white/10">
                                <li v-for="empresa in empresas.data" :key="empresa.id" class="company-item group">
                                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                                        <div class="company-avatar">
                                            <span>{{ getInitials(empresa.nome_fantasia) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="company-name">{{ empresa.nome_fantasia }}</p>
                                                <div :class="empresa.is_active ? 'bg-green-500' : 'bg-gray-400'" class="status-dot" :title="empresa.is_active ? 'Ativa' : 'Inativa'"></div>
                                            </div>
                                            <p class="company-details">{{ empresa.razao_social || empresa.cnpj }}</p>
                                        </div>
                                    </div>
                                    <div class="hidden md:flex items-center space-x-4">
                                         <div class="text-right">
                                             <p class="company-contact">{{ empresa.email }}</p>
                                             <p class="company-details">{{ empresa.telefone }}</p>
                                         </div>
                                        <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="editEmpresa(empresa)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Empresa"><Pencil class="w-5 h-5" /></button>
                                            <button @click="confirmEmpresaDeletion(empresa)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Empresa"><Trash2 class="w-5 h-5" /></button>
                                        </div>
                                    </div>
                                     <div class="md:hidden flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editEmpresa(empresa)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Empresa"><Pencil class="w-5 h-5" /></button>
                                        <button @click="confirmEmpresaDeletion(empresa)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Empresa"><Trash2 class="w-5 h-5" /></button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="empty-state">
                           <Building2 class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Nenhuma empresa cadastrada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece adicionando a primeira empresa parceira.</p>
                        </div>
                    </div>

                    <div v-if="empresas.data.length > 0" class="px-6 pb-4 border-t border-gray-200 dark:border-white/10 pt-4">
                        <Pagination :links="empresas.links" />
                    </div>
                </div>
            </div>
        </div>

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
                                            <span>{{ isEditing ? 'Editar Empresa' : 'Cadastrar Nova Empresa' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-8">
                                            <fieldset class="space-y-6">
                                                <legend class="section-title">Dados da Empresa</legend>
                                                <div class="md:col-span-2">
                                                    <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                                                    <input type="text" v-model="form.nome_fantasia" id="nome_fantasia" class="form-input" required>
                                                    <div v-if="form.errors.nome_fantasia" class="form-error">{{ form.errors.nome_fantasia }}</div>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="razao_social" class="form-label">Razão Social (Opcional)</label>
                                                        <input type="text" v-model="form.razao_social" id="razao_social" class="form-input">
                                                        <div v-if="form.errors.razao_social" class="form-error">{{ form.errors.razao_social }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="cnpj" class="form-label">CNPJ (Opcional)</label>
                                                        <input id="cnpj" v-model="form.cnpj" type="text" class="form-input" :class="{ 'input-invalid': realtimeErrors.cnpj || form.errors.cnpj, 'input-valid': !realtimeErrors.cnpj && !form.errors.cnpj && form.cnpj }" v-maska data-maska="##.###.###/####-##" placeholder="00.000.000/0000-00" />
                                                        <div v-if="form.errors.cnpj" class="form-error">{{ form.errors.cnpj }}</div>
                                                        <div v-if="realtimeErrors.cnpj" class="form-error">{{ realtimeErrors.cnpj }}</div>
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <fieldset class="space-y-6">
                                                <legend class="section-title">Informações de Contato</legend>
                                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="email" class="form-label">Email de Contato</label>
                                                        <input id="email" v-model="form.email" type="email" class="form-input" :class="{ 'input-invalid': realtimeErrors.email || form.errors.email, 'input-valid': !realtimeErrors.email && !form.errors.email && form.email }" required />
                                                        <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                                        <div v-if="realtimeErrors.email" class="form-error">{{ realtimeErrors.email }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="telefone" class="form-label">Telefone (Opcional)</label>
                                                        <input id="telefone" v-model="form.telefone" type="tel" class="form-input" :class="{ 'input-invalid': realtimeErrors.telefone || form.errors.telefone, 'input-valid': !realtimeErrors.telefone && !form.errors.telefone && form.telefone }" v-maska data-maska="['(##) ####-####', '(##) #####-####']" placeholder="(00) 0000-0000" />
                                                        <div v-if="form.errors.telefone" class="form-error">{{ form.errors.telefone }}</div>
                                                        <div v-if="realtimeErrors.telefone" class="form-error">{{ realtimeErrors.telefone }}</div>
                                                    </div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="endereco" class="form-label">Endereço (Opcional)</label>
                                                    <textarea v-model="form.endereco" id="endereco" rows="3" class="form-input"></textarea>
                                                    <div v-if="form.errors.endereco" class="form-error">{{ form.errors.endereco }}</div>
                                                </div>
                                            </fieldset>

                                            <fieldset>
                                                <legend class="section-title">Configurações</legend>
                                                <div class="mt-4">
                                                    <label for="is_active" class="toggle-switch-label">
                                                        <span class="font-medium text-gray-900 dark:text-gray-100">Status da Empresa</span>
                                                        <div class="toggle-switch">
                                                            <input type="checkbox" v-model="form.is_active" id="is_active" class="toggle-switch-checkbox">
                                                            <div class="toggle-switch-bg"></div>
                                                            <div class="toggle-switch-indicator"></div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing || Object.keys(realtimeErrors).length > 0" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Empresa' : 'Salvar Empresa' }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <ConfirmationModal
            :show="confirmingEmpresaDeletion"
            title="Excluir Empresa"
            :message="deleteConfirmationMessage"
            @close="confirmingEmpresaDeletion = false"
            @confirm="deleteEmpresa"
        />
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

/* --- NOVOS ESTILOS PARA A LISTA E ITENS --- */
.company-item { @apply flex items-center justify-between p-4 transition duration-150 ease-in-out; }
.company-avatar { @apply h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center font-bold; @apply bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300; }
.company-name { @apply text-base font-bold text-emerald-800 dark:text-emerald-300 truncate; }
.company-details { @apply text-sm text-gray-500 dark:text-gray-400 truncate; }
.company-contact { @apply text-sm font-medium text-gray-700 dark:text-gray-300 truncate;}
.status-dot { @apply h-2.5 w-2.5 rounded-full; }
.empty-state { @apply text-center py-12 px-6; }

.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

/* --- ESTILOS DO MODAL E FORMULÁRIO --- */
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider; }

/* Estilos para feedback de validação em tempo real */
.input-valid { @apply border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500 dark:border-green-500 dark:focus:border-green-500 dark:focus:ring-green-500; }
.input-invalid { @apply border-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-400 dark:focus:border-red-400 dark:focus:ring-red-400; }

/* --- ESTILOS PARA TOGGLE SWITCH --- */
.toggle-switch-label { @apply flex items-center justify-between cursor-pointer text-sm; }
.toggle-switch { @apply relative inline-flex items-center h-6 rounded-full w-11 transition-colors; }
.toggle-switch-checkbox { @apply absolute w-full h-full opacity-0 cursor-pointer; }
.toggle-switch-bg { @apply w-full h-full rounded-full transition-colors; @apply bg-gray-200 dark:bg-gray-700; }
.toggle-switch-indicator { @apply absolute left-1 top-1 w-4 h-4 rounded-full transition-transform; @apply bg-white; }
.toggle-switch-checkbox:checked + .toggle-switch-bg { @apply bg-emerald-600 dark:bg-green-500; }
.toggle-switch-checkbox:checked ~ .toggle-switch-indicator { @apply translate-x-5; }
</style>
