<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { Users, Plus, Pencil, Trash2, ShieldAlert, X, UserPlus, FileText } from 'lucide-vue-next';

// --- PROPS ---
const props = defineProps({
    legislaturaAtual: Object,
    comissoes: Array,
    politicos: Array,
});

// --- STATE MODALS ---
const isModalOpen = ref(false); // Substitui showCreateModal e showEditModal
const isEditing = ref(false);

const showAddMemberModal = ref(false);
const comissaoToEdit = ref(null);
const comissaoToAddMember = ref(null);
const comissaoToDelete = ref(null);
const membroToDelete = ref(null);

// --- MODAL DE CONFIRMAÇÃO ---
const showConfirmationModal = ref(false);
const confirmationAction = ref(() => {});
const confirmationTitle = ref('');
const confirmationMessage = ref('');
const confirmationButtonText = ref('Confirmar');
const confirmationButtonClass = ref('btn-danger');

// --- FORM COMISSÃO ---
const form = useForm({
    legislatura_id: props.legislaturaAtual?.id,
    nome: '',
    tipo: 'Permanente',
    descricao: '',
});

// --- FORM MEMBRO ---
const membroForm = useForm({
    politico_id: null,
    cargo: 'Membro',
});

// --- MÉTODOS GERAIS DE MODAL ---
const closeModal = () => {
    isModalOpen.value = false;
    showAddMemberModal.value = false;
};

// --- MÉTODOS COMISSÃO ---
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (comissao) => {
    isEditing.value = true;
    comissaoToEdit.value = comissao;
    form.legislatura_id = comissao.legislatura_id; // Mantém a legislatura
    form.nome = comissao.nome;
    form.tipo = comissao.tipo;
    form.descricao = comissao.descricao;
    isModalOpen.value = true;
};

const submitComissao = () => {
    const url = isEditing.value
        ? route('admin.comissoes.update', comissaoToEdit.value.id)
        : route('admin.comissoes.store');

    // Configura o método HTTP para PUT em caso de edição, se necessário
    const method = isEditing.value ? 'put' : 'post';

    form.submit(method, url, {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

// --- MÉTODOS MEMBRO ---
const openAddMemberModal = (comissao) => {
    comissaoToAddMember.value = comissao;
    membroForm.reset();
    showAddMemberModal.value = true;
};

const addMember = () => {
    membroForm.post(route('admin.comissoes.membros.store', comissaoToAddMember.value.id), {
        preserveScroll: true,
        onSuccess: () => showAddMemberModal.value = false,
    });
};

// --- MÉTODOS DE CONFIRMAÇÃO ---
const openConfirmationModal = (action, title, message, btnText = 'Confirmar Exclusão', btnClass = 'btn-danger') => {
    confirmationAction.value = action;
    confirmationTitle.value = title;
    confirmationMessage.value = message;
    confirmationButtonText.value = btnText;
    confirmationButtonClass.value = btnClass;
    showConfirmationModal.value = true;
};

const confirmAndExecute = () => {
    confirmationAction.value();
    showConfirmationModal.value = false;
};

const openDeleteComissaoConfirmation = (comissao) => {
    const action = () => router.delete(route('admin.comissoes.destroy', comissao.id), { preserveScroll: true });
    openConfirmationModal(
        action,
        'Excluir Comissão',
        `Tem certeza que deseja excluir a comissão "${comissao.nome}"? Todos os membros associados também serão removidos. Esta ação é permanente.`,
    );
};

const openDeleteMembroConfirmation = (comissaoId, membro) => {
    const action = () => router.delete(route('admin.comissoes.membros.destroy', { comissao: comissaoId, membroId: membro.id }), { preserveScroll: true });
    openConfirmationModal(
        action,
        'Remover Membro',
        `Tem certeza que deseja remover **${membro.politico.nome_politico}** da comissão?`,
        'Remover Membro',
        'btn-danger'
    );
};
</script>

<template>
    <Head title="Comissões da Legislatura" />
    <TenantLayout title="Comissões">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Comissões
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="!legislaturaAtual" class="content-container p-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 mb-8">
                    <p class="font-bold text-lg text-yellow-700 dark:text-yellow-300">Atenção: Legislatura não Definida</p>
                    <p class="mt-2 text-yellow-600 dark:text-yellow-400">
                        Nenhuma legislatura está marcada como "atual". Por favor, acesse a
                        <Link :href="route('admin.legislaturas.index')" class="underline font-medium hover:text-yellow-800 dark:hover:text-yellow-200 transition">gestão de legislaturas</Link>
                        e defina a legislatura vigente para poder gerenciar as comissões.
                    </p>
                </div>

                <div v-else class="content-container">
                    <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Comissões da {{ legislaturaAtual.titulo }}</h2>
                            <p class="form-subtitle">Gerencie as comissões e seus membros para a legislatura atual.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="openCreateModal" class="btn-primary w-full"><Plus class="h-4 w-4 mr-2" />Nova Comissão</button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="comissoes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div v-for="comissao in comissoes" :key="comissao.id" class="commission-card">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1 pr-2">
                                        <h3 class="card-name">{{ comissao.nome }}</h3>
                                        <span :class="{'commission-tag-permanent': comissao.tipo === 'Permanente', 'commission-tag-temporary': comissao.tipo !== 'Permanente'}">{{ comissao.tipo }}</span>
                                    </div>
                                    <div class="flex space-x-1">
                                        <button @click="openEditModal(comissao)" class="table-action-btn" title="Editar Comissão"><Pencil class="w-4 h-4" /></button>
                                        <button @click="openDeleteComissaoConfirmation(comissao)" class="table-action-btn text-red-500 hover:bg-red-500/10" title="Excluir Comissão"><Trash2 class="w-4 h-4" /></button>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">{{ comissao.descricao || 'Sem descrição.' }}</p>

                                <hr class="my-4 dark:border-gray-700">

                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 flex items-center"><Users class="w-4 h-4 mr-1 text-emerald-500" /> Membros ({{ comissao.membros.length }})</h4>
                                    <button @click="openAddMemberModal(comissao)" class="btn-sm-secondary"><Plus class="w-3 h-3 mr-1" /> Membro</button>
                                </div>
                                <ul v-if="comissao.membros.length > 0" class="space-y-1 max-h-40 overflow-y-auto pr-2">
                                    <li v-for="membro in comissao.membros" :key="membro.id" class="flex justify-between items-center text-sm p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                                        <span class="text-gray-700 dark:text-gray-300 truncate">
                                            <span class="font-medium text-emerald-600 dark:text-emerald-400">[{{ membro.cargo.substring(0, 1) }}]</span>
                                            {{ membro.politico.nome_politico }}
                                        </span>
                                        <button @click="openDeleteMembroConfirmation(comissao.id, membro)" class="table-action-btn p-1 text-red-400 hover:text-red-600 ml-2" title="Remover Membro">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-400">Nenhum membro adicionado.</p>
                            </div>
                        </div>

                        <div v-else class="text-center py-16 px-6">
                            <FileText class="mx-auto h-16 w-16 text-gray-400" />
                            <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-200">Nenhuma Comissão Cadastrada</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                                Clique no botão acima para adicionar a primeira comissão desta legislatura.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isModalOpen" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/50 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submitComissao" class="flex flex-col h-full">
                                    <div class="p-6 border-b-dynamic">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Comissão' : 'Criar Nova Comissão' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                    </div>
                                    <div class="p-6 space-y-6 overflow-y-auto flex-1">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="nome" class="form-label">Nome</label>
                                                <input type="text" v-model="form.nome" id="nome" class="form-input" required>
                                                <div v-if="form.errors.nome" class="form-error">{{ form.errors.nome }}</div>
                                            </div>
                                            <div>
                                                <label for="tipo" class="form-label">Tipo</label>
                                                <select v-model="form.tipo" id="tipo" class="form-input" required>
                                                    <option>Permanente</option>
                                                    <option>Temporária</option>
                                                    <option>Especial</option>
                                                </select>
                                                <div v-if="form.errors.tipo" class="form-error">{{ form.errors.tipo }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="descricao" class="form-label">Descrição (Opcional)</label>
                                            <textarea v-model="form.descricao" id="descricao" rows="4" class="form-input"></textarea>
                                            <div v-if="form.errors.descricao" class="form-error">{{ form.errors.descricao }}</div>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl border-t-dynamic">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Salvar Alterações' : 'Criar Comissão' }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <TransitionRoot appear :show="showAddMemberModal" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/50 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel max-w-lg">
                                <form @submit.prevent="addMember" class="flex flex-col h-full">
                                    <div class="p-6 border-b-dynamic">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>Adicionar Membro</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                        <p class="form-subtitle mt-1">Comissão: **{{ comissaoToAddMember?.nome }}**</p>
                                    </div>
                                    <div class="p-6 space-y-6 overflow-y-auto flex-1">
                                        <div>
                                            <label for="politico_id" class="form-label">Político</label>
                                            <select v-model="membroForm.politico_id" id="politico_id" class="form-input" required>
                                                <option :value="null" disabled>Selecione um político</option>
                                                <option v-for="politico in politicos" :key="politico.id" :value="politico.id">
                                                    {{ politico.nome_politico }} ({{ politico.nome_completo }})
                                                </option>
                                            </select>
                                            <div v-if="membroForm.errors.politico_id" class="form-error">{{ membroForm.errors.politico_id }}</div>
                                        </div>
                                        <div>
                                            <label for="cargo" class="form-label">Cargo na Comissão</label>
                                            <select v-model="membroForm.cargo" id="cargo" class="form-input" required>
                                                <option>Membro</option>
                                                <option>Presidente</option>
                                                <option>Vice-Presidente</option>
                                                <option>Relator</option>
                                            </select>
                                            <div v-if="membroForm.errors.cargo" class="form-error">{{ membroForm.errors.cargo }}</div>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl border-t-dynamic">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="membroForm.processing" class="btn-primary"><UserPlus class="w-4 h-4 mr-1" /> Adicionar</button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <TransitionRoot appear :show="showConfirmationModal" as="template">
            <Dialog as="div" @close="showConfirmationModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0"><div class="fixed inset-0 bg-black/60 backdrop-blur-sm" /></TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left align-middle shadow-xl transition-all">
                                <div class="p-6 text-center">
                                    <ShieldAlert class="w-16 h-16 text-red-500 mx-auto mb-4" />
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ confirmationTitle }}</h3>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400" v-html="confirmationMessage"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                                    <button @click="showConfirmationModal = false" class="btn-secondary">Cancelar</button>
                                    <button @click="confirmAndExecute" :class="confirmationButtonClass">{{ confirmationButtonText }}</button>
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
/* ESTILOS COMUNS E PERSONALIZADOS */
.content-container { @apply relative w-full pt-16 rounded-2xl shadow-lg; @apply bg-white border border-gray-200/80; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

/* Modal e Formulário */
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; max-height: 90vh; display: flex; flex-direction: column; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-left; }
.form-input { @apply block w-full text-sm rounded-lg transition-all h-11 py-3 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
select.form-input { @apply appearance-none h-11; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }

/* Botões */
.btn-primary { @apply flex items-center justify-center px-5 py-2.5 rounded-lg font-semibold text-sm uppercase tracking-wider transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-900 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500; @apply dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50 disabled:cursor-not-allowed; }
.btn-secondary { @apply inline-flex items-center justify-center w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm uppercase tracking-wider shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors; }
.btn-danger { @apply inline-flex items-center justify-center w-full px-4 py-2.5 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider bg-red-600 hover:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.btn-sm-secondary { @apply inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors; }

/* Card de Comissão */
.commission-card { @apply bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 shadow-md border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-xl; }
.card-name { @apply font-bold text-lg text-gray-900 dark:text-emerald-300 truncate; }
.commission-tag-permanent { @apply text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300; }
.commission-tag-temporary { @apply text-xs font-medium mr-2 px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300; }
</style>
