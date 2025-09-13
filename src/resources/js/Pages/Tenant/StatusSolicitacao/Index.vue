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
import { Plus, Pencil, Trash2, ClipboardCheck, X } from 'lucide-vue-next';

const props = defineProps({
    status: Object, // Objeto de paginação vindo do controller
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    nome: '',
    cor: '#cccccc',
    is_default_abertura: false,
    is_final: false,
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editStatus = (statusItem) => {
    isEditing.value = true;
    form.id = statusItem.id;
    form.nome = statusItem.nome;
    form.cor = statusItem.cor;
    form.is_default_abertura = statusItem.is_default_abertura;
    form.is_final = statusItem.is_final;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.status-solicitacao.update' : 'admin.status-solicitacao.store';
    // CORREÇÃO: o nome do parâmetro foi alterado de status_solicitacao para statusSolicitacao
    const params = isEditing.value ? { statusSolicitacao: form.id } : {};

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

const deleteStatus = (statusItem) => {
    if (confirm('Tem certeza que deseja remover este status?')) {
        router.delete(route('admin.status-solicitacao.destroy', statusItem), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Status de Solicitação" />

    <TenantLayout title="Status de Solicitação">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Status de Solicitação
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><ClipboardCheck :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Status de Solicitação</h2>
                        <p class="form-subtitle">Defina as etapas do seu fluxo de atendimento.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Novo Status
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="status.data.length > 0" class="space-y-4">
                        <div v-for="statusItem in status.data" :key="statusItem.id" class="role-card">
                            <div class="flex items-center gap-4">
                                <div class="w-6 h-6 rounded-full border-2 border-white/20" :style="{ backgroundColor: statusItem.cor }"></div>
                                <div class="flex-1">
                                    <p class="role-name">{{ statusItem.nome }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span v-if="statusItem.is_default_abertura" class="badge-special">
                                            Status Inicial Padrão
                                        </span>
                                        <span v-if="statusItem.is_final" class="badge-special">
                                            Status de Finalização
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editStatus(statusItem)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteStatus(statusItem)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                     <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhum status encontrado.</p>
                     </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="status.links" />
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
                                            <span>{{ isEditing ? 'Editar Status' : 'Criar Novo Status' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="md:col-span-2">
                                                    <label for="nome" class="form-label">Nome do Status</label>
                                                    <input type="text" v-model="form.nome" id="nome" class="form-input" required>
                                                    <div v-if="form.errors.nome" class="form-error">{{ form.errors.nome }}</div>
                                                </div>
                                                <div>
                                                    <label for="cor" class="form-label">Cor</label>
                                                    <input type="color" v-model="form.cor" id="cor" class="form-input !p-1 !h-12">
                                                    <div v-if="form.errors.cor" class="form-error">{{ form.errors.cor }}</div>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" v-model="form.is_default_abertura" class="form-checkbox">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Marcar como status inicial padrão</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" v-model="form.is_final" class="form-checkbox">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Marcar como status de finalização (Ex: Concluído, Cancelado)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Status' : 'Salvar Status' }}
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
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-special { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>
