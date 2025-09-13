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
import { Plus, Pencil, Trash2, FileBadge, X, Loader2 } from 'lucide-vue-next';
import { vMaska } from "maska/vue";

const props = defineProps({
    documentos: Object, // Objeto de paginação
});

// Tipos de documentos pré-definidos
const tiposDocumento = ['RG', 'CPF', 'CNH', 'Passaporte', 'Carteira de Trabalho', 'Título de Eleitor', 'Outro'];

// --- Modal de Formulário ---
const isFormModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    tipo_documento: '',
    nome_completo: '',
    numero_documento: '',
    data_encontrado: '',
    local_encontrado: '',
    entregue_por: '',
    observacoes: '',
    status: 'Aguardando Retirada',
    data_entrega: '',
    retirado_por_nome: '',
    retirado_por_cpf: '',
});

const openFormModal = () => {
    isEditing.value = false;
    form.reset();
    form.data_encontrado = new Date().toISOString().split('T')[0]; // Data de hoje como padrão
    isFormModalOpen.value = true;
};

const editDocumento = (documento) => {
    isEditing.value = true;
    form.id = documento.id;
    form.tipo_documento = documento.tipo_documento;
    form.nome_completo = documento.nome_completo;
    form.numero_documento = documento.numero_documento;
    // Formata a data para 'yyyy-mm-dd'
    form.data_encontrado = documento.data_encontrado ? new Date(documento.data_encontrado).toISOString().split('T')[0] : '';
    form.local_encontrado = documento.local_encontrado;
    form.entregue_por = documento.entregue_por;
    form.observacoes = documento.observacoes;
    form.status = documento.status;
    // Formata a data de entrega
    form.data_entrega = documento.data_entrega ? new Date(documento.data_entrega).toISOString().split('T')[0] : '';
    form.retirado_por_nome = documento.retirado_por_nome;
    form.retirado_por_cpf = documento.retirado_por_cpf;
    isFormModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    form.reset();
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.achados-e-perdidos-documentos.update' : 'admin.achados-e-perdidos-documentos.store';
    const params = isEditing.value ? { achadosEPerdidosDocumento: form.id } : {};

    const options = {
        onSuccess: () => closeFormModal(),
        preserveScroll: true,
    };

    if (isEditing.value) {
        form.put(route(routeName, params), options);
    } else {
        form.post(route(routeName), options);
    }
};

// --- Modal de Exclusão ---
const isDeleteModalOpen = ref(false);
const documentoToDelete = ref(null);

const openDeleteModal = (documento) => {
    documentoToDelete.value = documento;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    documentoToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const confirmDelete = () => {
    router.delete(route('admin.achados-e-perdidos-documentos.destroy', documentoToDelete.value), {
        onSuccess: () => closeDeleteModal(),
        preserveScroll: true,
    });
};

const statusClass = computed(() => (status) => {
    return status === 'Entregue' ? 'badge-active' : 'badge-pending';
});

</script>

<template>
    <Head title="Documentos Perdidos" />

    <TenantLayout title="Documentos Perdidos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Achados e Perdidos - Documentos
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><FileBadge :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Documentos Encontrados</h2>
                        <p class="form-subtitle">Gerencie os documentos perdidos que foram entregues na Câmara.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openFormModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Registrar Documento
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="documentos.data.length > 0" class="space-y-4">
                        <div v-for="doc in documentos.data" :key="doc.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ doc.nome_completo }}</p>
                                <p class="form-subtitle">{{ doc.tipo_documento }} <span v-if="doc.numero_documento"> - {{ doc.numero_documento }}</span></p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                   <span :class="statusClass(doc.status)" class="badge-base">
                                        {{ doc.status }}
                                   </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editDocumento(doc)" class="table-action-btn hover:text-amber-600" title="Editar / Devolver"><Pencil class="w-5 h-5" /></button>
                                <button @click="openDeleteModal(doc)" class="table-action-btn hover:text-red-600" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                     <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhum documento registrado.</p>
                     </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="documentos.links" />
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isFormModalOpen" as="template">
            <Dialog as="div" @close="closeFormModal" class="relative z-50">
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
                                            <span>{{ isEditing ? 'Editar / Devolver Documento' : 'Registrar Novo Documento' }}</span>
                                            <button @click="closeFormModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div class="section-title">Informações do Documento</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="nome_completo" class="form-label">
                                                        Nome Completo (no documento) <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        v-model="form.nome_completo"
                                                        id="nome_completo"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.nome_completo}"
                                                        required>
                                                    <div v-if="form.errors.nome_completo" class="form-error">{{ form.errors.nome_completo }}</div>
                                                </div>
                                                <div>
                                                    <label for="tipo_documento" class="form-label">
                                                        Tipo de Documento <span class="text-red-500">*</span>
                                                    </label>
                                                    <select
                                                        v-model="form.tipo_documento"
                                                        id="tipo_documento"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.tipo_documento}"
                                                        required>
                                                        <option disabled value="">Selecione um tipo</option>
                                                        <option v-for="tipo in tiposDocumento" :key="tipo">{{ tipo }}</option>
                                                    </select>
                                                    <div v-if="form.errors.tipo_documento" class="form-error">{{ form.errors.tipo_documento }}</div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="numero_documento" class="form-label">Número (se houver)</label>
                                                    <input
                                                        type="text"
                                                        v-model="form.numero_documento"
                                                        id="numero_documento"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.numero_documento}">
                                                    <div v-if="form.errors.numero_documento" class="form-error">{{ form.errors.numero_documento }}</div>
                                                </div>
                                            </div>

                                            <div class="section-title pt-4">Informações da Entrega (na Câmara)</div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="data_encontrado" class="form-label">
                                                        Data em que foi encontrado <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="date"
                                                        v-model="form.data_encontrado"
                                                        id="data_encontrado"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.data_encontrado}"
                                                        required>
                                                    <div v-if="form.errors.data_encontrado" class="form-error">{{ form.errors.data_encontrado }}</div>
                                                </div>
                                                <div>
                                                    <label for="local_encontrado" class="form-label">
                                                        Local em que foi encontrado <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        v-model="form.local_encontrado"
                                                        id="local_encontrado"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.local_encontrado}"
                                                        required>
                                                    <div v-if="form.errors.local_encontrado" class="form-error">{{ form.errors.local_encontrado }}</div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="entregue_por" class="form-label">
                                                        Nome de quem entregou <span class="text-red-500">*</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        v-model="form.entregue_por"
                                                        id="entregue_por"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.entregue_por}"
                                                        required>
                                                    <div v-if="form.errors.entregue_por" class="form-error">{{ form.errors.entregue_por }}</div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="observacoes" class="form-label">Observações</label>
                                                    <textarea
                                                        v-model="form.observacoes"
                                                        id="observacoes"
                                                        rows="3"
                                                        class="form-input"
                                                        :class="{'is-invalid': form.errors.observacoes}"></textarea>
                                                    <div v-if="form.errors.observacoes" class="form-error">{{ form.errors.observacoes }}</div>
                                                </div>
                                            </div>

                                            <div v-if="isEditing" class="section-title pt-4">Status e Devolução</div>
                                            <div v-if="isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="status" class="form-label">Status</label>
                                                    <select v-model="form.status" id="status" class="form-input">
                                                        <option>Aguardando Retirada</option>
                                                        <option>Entregue</option>
                                                    </select>
                                                </div>
                                                <div v-if="form.status === 'Entregue'" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                                    <div>
                                                        <label for="data_entrega" class="form-label">
                                                            Data da Entrega <span class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            type="date"
                                                            v-model="form.data_entrega"
                                                            id="data_entrega"
                                                            class="form-input"
                                                            :class="{'is-invalid': form.errors.data_entrega}"
                                                            required>
                                                        <div v-if="form.errors.data_entrega" class="form-error">{{ form.errors.data_entrega }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="retirado_por_cpf" class="form-label">
                                                            CPF de quem retirou <span class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            v-model="form.retirado_por_cpf"
                                                            v-maska="'###.###.###-##'"
                                                            id="retirado_por_cpf"
                                                            class="form-input"
                                                            :class="{'is-invalid': form.errors.retirado_por_cpf}"
                                                            required>
                                                        <div v-if="form.errors.retirado_por_cpf" class="form-error">{{ form.errors.retirado_por_cpf }}</div>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label for="retirado_por_nome" class="form-label">
                                                            Nome de quem retirou <span class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            v-model="form.retirado_por_nome"
                                                            id="retirado_por_nome"
                                                            class="form-input"
                                                            :class="{'is-invalid': form.errors.retirado_por_nome}"
                                                            required>
                                                        <div v-if="form.errors.retirado_por_nome" class="form-error">{{ form.errors.retirado_por_nome }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeFormModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            <span v-if="form.processing" class="flex items-center"><Loader2 class="w-4 h-4 mr-2 animate-spin"/> Processando...</span>
                                            <span v-else>{{ isEditing ? 'Atualizar Registro' : 'Salvar Registro' }}</span>
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
                            <DialogPanel class="modal-panel max-w-lg">
                                <div class="p-6 text-center">
                                    <Trash2 class="w-16 h-16 text-red-600 mx-auto mb-4" />
                                    <DialogTitle as="h3" class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                        Confirmar Exclusão
                                    </DialogTitle>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Tem certeza que deseja remover o registro de <br> "<span class="font-bold">{{ documentoToDelete?.nome_completo }}</span>"?
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        Esta ação não pode ser desfeita.
                                    </p>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                    <button type="button" @click="closeDeleteModal" class="btn-secondary">Cancelar</button>
                                    <button type="button" @click="confirmDelete" class="btn-primary !bg-red-600 hover:!bg-red-700 focus:!ring-red-500">
                                        <span v-if="form.processing" class="flex items-center"><Loader2 class="w-4 h-4 mr-2 animate-spin"/> Removendo...</span>
                                        <span v-else>Confirmar</span>
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
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.badge-active { @apply bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-pending { @apply bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-3xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-input.is-invalid { @apply border-red-500 focus:ring-red-500 focus:border-red-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply font-semibold text-gray-700 dark:text-gray-300 text-sm border-b border-gray-200 dark:border-gray-700 pb-2; }
</style>
