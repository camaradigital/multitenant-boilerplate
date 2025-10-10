<script setup>
import { ref, computed } from 'vue';
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
import { Plus, Pencil, Trash2, UserSearch, X, AlertCircle, Loader2, FileDown, Image, Paperclip } from 'lucide-vue-next';

const props = defineProps({
    pessoas: Object, // Objeto de paginação
});

// --- Estado dos Modais ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false); // Estado para o processo de exclusão

const selectedPessoa = ref(null);
const pessoaToDelete = ref(null);

// --- Formulários ---
const formCreate = useForm({
    nome_completo: '',
    idade: '',
    data_desaparecimento: '',
    local_desaparecimento: '',
    detalhes: '',
    foto: null,
    boletim_ocorrencia: null,
});

const formUpdate = useForm({
    id: null,
    status: '',
});

// --- Funções dos Modais ---
const openCreateModal = () => {
    isEditing.value = false;
    formCreate.reset();
    isModalOpen.value = true;
};

const openEditModal = (pessoa) => {
    isEditing.value = true;
    selectedPessoa.value = pessoa;
    formUpdate.id = pessoa.id;
    formUpdate.status = pessoa.status;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedPessoa.value = null;
        formCreate.reset();
        formUpdate.reset();
    }, 300);
};

const openDeleteModal = (pessoa) => {
    pessoaToDelete.value = pessoa;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    setTimeout(() => {
        pessoaToDelete.value = null;
    }, 300);
};

// --- Funções de Submissão ---
const submitCreate = () => {
    formCreate.post(route('admin.pessoas-desaparecidas.store'), {
        onFinish: () => {
            if (!formCreate.hasErrors) {
                closeModal();
            }
        },
        preserveScroll: true,
    });
};

const submitUpdate = () => {
    formUpdate.put(route('admin.pessoas-desaparecidas.update', formUpdate.id), {
        onFinish: () => {
            if (!formUpdate.hasErrors) {
                closeModal();
            }
        },
        preserveScroll: true,
    });
};

const deletePessoaConfirmed = () => {
    if (pessoaToDelete.value) {
        router.delete(route('admin.pessoas-desaparecidas.destroy', pessoaToDelete.value.id), {
            preserveScroll: true,
            onBefore: () => {
                isDeleting.value = true;
            },
            onFinish: () => {
                isDeleting.value = false;
                closeDeleteModal();
            },
        });
    }
};

// --- Utilitários ---
const statusClass = computed(() => (status) => {
    switch (status) {
        case 'Publicado': return 'badge-active';
        case 'Encontrado': return 'badge-inactive';
        case 'Aguardando Aprovação':
        default: return 'badge-pending';
    }
});

const fotoFileName = computed(() => formCreate.foto ? formCreate.foto.name : 'Nenhum arquivo selecionado');
const boFileName = computed(() => formCreate.boletim_ocorrencia ? formCreate.boletim_ocorrencia.name : 'Nenhum arquivo selecionado');

</script>

<template>
    <Head title="Pessoas Desaparecidas" />

    <TenantLayout title="Pessoas Desaparecidas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Achados e Perdidos - Pessoas Desaparecidas
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><UserSearch :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Pessoas Desaparecidas</h2>
                        <p class="form-subtitle">Gerencie os registros de pessoas desaparecidas para divulgação.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openCreateModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Novo Registro
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="pessoas.data.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div v-for="pessoa in pessoas.data" :key="pessoa.id" class="role-card flex-row items-start gap-5">
                            <div class="w-24 h-24 md:w-28 md:h-28 flex-shrink-0">
                                <img v-if="pessoa.foto_url" :src="pessoa.foto_url" :alt="`Foto de ${pessoa.nome_completo}`" class="w-full h-full object-cover rounded-lg shadow-md">
                                <div v-else class="w-full h-full bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <Image class="w-8 h-8 text-gray-400" />
                                </div>
                            </div>

                            <div class="flex-1">
                                <p class="role-name">{{ pessoa.nome_completo }}, {{ pessoa.idade }} anos</p>
                                <p class="form-subtitle mt-1">Desapareceu em {{ new Date(pessoa.data_desaparecimento).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}</p>
                                <p class="form-subtitle">Local: {{ pessoa.local_desaparecimento }}</p>
                                <div class="mt-3 flex items-center justify-between">
                                    <span :class="statusClass(pessoa.status)" class="badge-base">
                                        {{ pessoa.status }}
                                    </span>
                                    <div class="flex items-center space-x-1">
                                        <a :href="route('admin.pessoas-desaparecidas.downloadBoletim', pessoa.id)" class="table-action-btn hover:text-sky-600" title="Baixar Boletim de Ocorrência"><FileDown class="w-5 h-5" /></a>
                                        <button @click="openEditModal(pessoa)" class="table-action-btn hover:text-amber-600" title="Moderar Status"><Pencil class="w-5 h-5" /></button>
                                        <button @click="openDeleteModal(pessoa)" class="table-action-btn hover:text-red-600" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-16">
                        <UserSearch class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Nenhum registro</h3>
                        <p class="mt-1 text-sm text-gray-500">Comece cadastrando um novo registro de pessoa desaparecida.</p>
                    </div>
                </div>

                <div v-if="pessoas.data.length > 0" class="px-6 pb-4">
                    <Pagination :links="pessoas.links" />
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
                            <DialogPanel class="modal-panel max-w-2xl">
                                <form v-if="!isEditing" @submit.prevent="submitCreate">
                                    <DialogTitle as="h3" class="modal-header">
                                        <div class="flex items-center gap-3">
                                            <div class="modal-header-icon bg-emerald-100 dark:bg-emerald-900/50"><Plus class="h-6 w-6 text-emerald-600 dark:text-emerald-300" /></div>
                                            <span>Registrar Pessoa Desaparecida</span>
                                        </div>
                                        <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                    </DialogTitle>

                                    <div class="p-6 space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="nome_completo" class="form-label">Nome Completo</label>
                                                <input type="text" v-model="formCreate.nome_completo" id="nome_completo" class="form-input" :class="{'is-invalid': formCreate.errors.nome_completo}" required>
                                                <div v-if="formCreate.errors.nome_completo" class="form-error">{{ formCreate.errors.nome_completo }}</div>
                                            </div>
                                            <div>
                                                <label for="idade" class="form-label">Idade</label>
                                                <input type="number" v-model="formCreate.idade" id="idade" class="form-input" :class="{'is-invalid': formCreate.errors.idade}" required>
                                                <div v-if="formCreate.errors.idade" class="form-error">{{ formCreate.errors.idade }}</div>
                                            </div>
                                            <div>
                                                <label for="data_desaparecimento" class="form-label">Data do Desaparecimento</label>
                                                <input type="date" v-model="formCreate.data_desaparecimento" id="data_desaparecimento" class="form-input" :class="{'is-invalid': formCreate.errors.data_desaparecimento}" required>
                                                <div v-if="formCreate.errors.data_desaparecimento" class="form-error">{{ formCreate.errors.data_desaparecimento }}</div>
                                            </div>
                                            <div>
                                                <label for="local_desaparecimento" class="form-label">Local do Desaparecimento</label>
                                                <input type="text" v-model="formCreate.local_desaparecimento" id="local_desaparecimento" class="form-input" :class="{'is-invalid': formCreate.errors.local_desaparecimento}" required>
                                                <div v-if="formCreate.errors.local_desaparecimento" class="form-error">{{ formCreate.errors.local_desaparecimento }}</div>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label for="detalhes" class="form-label">Detalhes (características, roupas, etc.)</label>
                                                <textarea v-model="formCreate.detalhes" id="detalhes" rows="4" class="form-input" :class="{'is-invalid': formCreate.errors.detalhes}" required></textarea>
                                                <div v-if="formCreate.errors.detalhes" class="form-error">{{ formCreate.errors.detalhes }}</div>
                                            </div>
                                            <div>
                                                <label class="form-label">Foto da Pessoa</label>
                                                <label for="foto" class="form-file-input-label" :class="{'is-invalid': formCreate.errors.foto}">
                                                    <Paperclip class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                                    <span class="truncate">{{ fotoFileName }}</span>
                                                </label>
                                                <input type="file" @change="formCreate.foto = $event.target.files[0]" class="sr-only" id="foto" accept="image/*" required>
                                                <div v-if="formCreate.errors.foto" class="form-error">{{ formCreate.errors.foto }}</div>
                                            </div>
                                            <div>
                                                <label class="form-label">Boletim de Ocorrência</label>
                                                <label for="boletim_ocorrencia" class="form-file-input-label" :class="{'is-invalid': formCreate.errors.boletim_ocorrencia}">
                                                    <Paperclip class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                                    <span class="truncate">{{ boFileName }}</span>
                                                </label>
                                                <input type="file" @change="formCreate.boletim_ocorrencia = $event.target.files[0]" class="sr-only" id="boletim_ocorrencia" accept=".pdf,.doc,.docx,image/*" required>
                                                <div v-if="formCreate.errors.boletim_ocorrencia" class="form-error">{{ formCreate.errors.boletim_ocorrencia }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="formCreate.processing" class="btn-primary">
                                            <Loader2 v-if="formCreate.processing" class="w-4 h-4 animate-spin"/>
                                            <span v-else>Enviar para Moderação</span>
                                        </button>
                                    </div>
                                </form>

                                <form v-else @submit.prevent="submitUpdate">
                                    <DialogTitle as="h3" class="modal-header">
                                        <div class="flex items-center gap-3">
                                            <div class="modal-header-icon bg-amber-100 dark:bg-amber-900/50"><Pencil class="h-6 w-6 text-amber-600 dark:text-amber-300" /></div>
                                            <span>Moderar Registro</span>
                                        </div>
                                        <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                    </DialogTitle>

                                    <div class="p-6 space-y-6">
                                        <div class="text-left p-4 bg-gray-100 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600/50">
                                            <div class="flex items-start gap-4">
                                                <img v-if="selectedPessoa.foto_url" :src="selectedPessoa.foto_url" alt="Foto" class="h-24 w-24 rounded-md object-cover">
                                                <div>
                                                    <h4 class="font-bold text-gray-900 dark:text-gray-100">
                                                        {{ selectedPessoa.nome_completo }}
                                                    </h4>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ selectedPessoa.idade }} anos | Desapareceu em {{ new Date(selectedPessoa.data_desaparecimento).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}
                                                    </p>
                                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                                        {{ selectedPessoa.detalhes }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="status" class="form-label">Alterar Status do Registro</label>
                                            <select v-model="formUpdate.status" id="status" class="form-input" :class="{'is-invalid': formUpdate.errors.status}">
                                                <option>Aguardando Aprovação</option>
                                                <option>Publicado</option>
                                                <option>Encontrado</option>
                                            </select>
                                            <div v-if="formUpdate.errors.status" class="form-error">{{ formUpdate.errors.status }}</div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="formUpdate.processing" class="btn-primary">
                                            <Loader2 v-if="formUpdate.processing" class="w-4 h-4 animate-spin"/>
                                            <span v-else>Atualizar Status</span>
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <TransitionRoot appear :show="isDeleteModalOpen" as="template">
            <Dialog as="div" @close="closeDeleteModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel max-w-md">
                                <div class="p-6 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                        <AlertCircle class="h-6 w-6 text-red-600 dark:text-red-400" aria-hidden="true" />
                                    </div>
                                    <DialogTitle as="h3" class="mt-4 text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                        Tem certeza?
                                    </DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Esta ação não pode ser desfeita.
                                            O registro de <strong>{{ pessoaToDelete?.nome_completo }}</strong> será permanentemente removido.
                                        </p>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-center space-x-3 rounded-b-2xl">
                                    <button type="button" class="btn-secondary" @click="closeDeleteModal" :disabled="isDeleting">
                                        Cancelar
                                    </button>
                                    <button type="button" class="btn-danger" @click="deletePessoaConfirmed" :disabled="isDeleting">
                                        <span v-if="isDeleting" class="flex items-center">
                                            <Loader2 class="w-4 h-4 mr-2 animate-spin"/> Removendo...
                                        </span>
                                        <span v-else>Excluir</span>
                                    </button>
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
.modal-header { @apply p-6 flex justify-between items-center border-b border-gray-200 dark:border-gray-700; @apply text-lg font-medium leading-6 text-gray-900 dark:text-gray-100; }
.modal-header-icon { @apply w-10 h-10 rounded-lg flex items-center justify-center; }
.modal-footer { @apply px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end items-center space-x-3 rounded-b-2xl; }
.form-file-input-label { @apply flex items-center space-x-3 w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4 cursor-pointer; @apply bg-gray-50 border border-gray-300 text-gray-700 dark:bg-gray-700/50 dark:border-gray-600 dark:text-gray-400; @apply hover:bg-gray-100 dark:hover:bg-gray-700; }
.form-file-input-label.is-invalid { @apply border-red-500; }
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-emerald-500/50 dark:hover:border-emerald-400/50; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.badge-active { @apply bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-pending { @apply bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300; }
.badge-inactive { @apply bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300; }
.btn-primary { @apply inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed; }
.btn-danger { @apply inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 text-white; @apply bg-red-600 hover:bg-red-700 focus:ring-red-500; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-input.is-invalid { @apply border-red-500 focus:ring-red-500 focus:border-red-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
.sr-only { @apply absolute w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0; clip: rect(0, 0, 0, 0); }
</style>
