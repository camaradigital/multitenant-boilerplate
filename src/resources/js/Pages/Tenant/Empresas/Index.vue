<script setup>
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, Building2, X } from 'lucide-vue-next';

const props = defineProps({
    empresas: Object,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

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

const openModal = () => {
    isEditing.value = false;
    form.reset();
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

const deleteEmpresa = (empresa) => {
    if (confirm(`Tem certeza que deseja remover a empresa "${empresa.nome_fantasia}"? Todas as vagas associadas também serão excluídas.`)) {
        router.delete(route('admin.empresas.destroy', empresa), {
            preserveScroll: true,
        });
    }
};

const getStatusClass = (isActive) => {
    return isActive
        ? 'badge-active'
        : 'badge-inactive';
};
</script>

<template>
    <Head title="Empresas" />

    <TenantLayout title="Empresas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Empresas
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
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
                    <div v-if="empresas.data.length > 0" class="space-y-4">
                        <div v-for="empresa in empresas.data" :key="empresa.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ empresa.nome_fantasia }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span v-if="empresa.cnpj" class="badge-permission">
                                        CNPJ: {{ empresa.cnpj }}
                                    </span>
                                    <span class="badge-info">
                                        {{ empresa.email }}
                                    </span>
                                    <span :class="getStatusClass(empresa.is_active)">
                                        {{ empresa.is_active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editEmpresa(empresa)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Empresa"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteEmpresa(empresa)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Empresa"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma empresa cadastrada.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="empresas.links" />
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

                                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2">
                                                <label for="nome_fantasia" class="form-label">Nome Fantasia</label>
                                                <input type="text" v-model="form.nome_fantasia" id="nome_fantasia" class="form-input" required>
                                                <div v-if="form.errors.nome_fantasia" class="form-error">{{ form.errors.nome_fantasia }}</div>
                                            </div>
                                            <div>
                                                <label for="razao_social" class="form-label">Razão Social (Opcional)</label>
                                                <input type="text" v-model="form.razao_social" id="razao_social" class="form-input">
                                                <div v-if="form.errors.razao_social" class="form-error">{{ form.errors.razao_social }}</div>
                                            </div>
                                             <div>
                                                <label for="cnpj" class="form-label">CNPJ (Opcional)</label>
                                                <input type="text" v-model="form.cnpj" id="cnpj" class="form-input">
                                                <div v-if="form.errors.cnpj" class="form-error">{{ form.errors.cnpj }}</div>
                                            </div>
                                            <div>
                                                <label for="email" class="form-label">Email de Contato</label>
                                                <input type="email" v-model="form.email" id="email" class="form-input" required>
                                                <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                            </div>
                                            <div>
                                                <label for="telefone" class="form-label">Telefone (Opcional)</label>
                                                <input type="tel" v-model="form.telefone" id="telefone" class="form-input">
                                                <div v-if="form.errors.telefone" class="form-error">{{ form.errors.telefone }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="endereco" class="form-label">Endereço (Opcional)</label>
                                                <textarea v-model="form.endereco" id="endereco" rows="3" class="form-input"></textarea>
                                                <div v-if="form.errors.endereco" class="form-error">{{ form.errors.endereco }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" v-model="form.is_active" class="form-checkbox">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Empresa ativa</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
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
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes com o seu design */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-info { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-500/10 dark:text-slate-300; }
.badge-active { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-inactive { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>

