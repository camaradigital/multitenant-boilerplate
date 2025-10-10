<script setup>
import { ref, computed } from 'vue';
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
    form.clearErrors();
    form.data_encontrado = new Date().toISOString().split('T')[0]; // Data de hoje como padrão
    isFormModalOpen.value = true;
};

const editDocumento = (documento) => {
    isEditing.value = true;
    form.id = documento.id;
    form.tipo_documento = documento.tipo_documento;
    form.nome_completo = documento.nome_completo;
    form.numero_documento = documento.numero_documento;
    form.data_encontrado = documento.data_encontrado ? new Date(documento.data_encontrado).toISOString().split('T')[0] : '';
    form.local_encontrado = documento.local_encontrado;
    form.entregue_por = documento.entregue_por;
    form.observacoes = documento.observacoes;
    form.status = documento.status;
    form.data_entrega = documento.data_entrega ? new Date(documento.data_entrega).toISOString().split('T')[0] : '';
    form.retirado_por_nome = documento.retirado_por_nome;
    form.retirado_por_cpf = documento.retirado_por_cpf;
    form.clearErrors();
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
const confirmingDocDeletion = ref(false);
const documentoToDelete = ref(null);
const deleteForm = useForm({});

const confirmDocDeletion = (documento) => {
    documentoToDelete.value = documento;
    confirmingDocDeletion.value = true;
};

const deleteDocumento = () => {
    deleteForm.delete(route('admin.achados-e-perdidos-documentos.destroy', documentoToDelete.value.id), {
        onSuccess: () => {
            confirmingDocDeletion.value = false;
            documentoToDelete.value = null;
        },
        preserveScroll: true
    });
};

const deleteConfirmationMessage = computed(() => {
    return documentoToDelete.value ? `Tem certeza que deseja remover o registro do documento de "${documentoToDelete.value.nome_completo}"? Esta ação não pode ser desfeita.` : '';
});

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

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
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
                        <div v-if="documentos.data.length > 0">
                            <ul class="divide-y divide-gray-200 dark:divide-white/10">
                                <li v-for="doc in documentos.data" :key="doc.id" class="document-item group">
                                    <div class="flex-1 min-w-0">
                                        <p class="document-name">{{ doc.nome_completo }}</p>
                                        <p class="document-info">{{ doc.tipo_documento }} <span v-if="doc.numero_documento"> - {{ doc.numero_documento }}</span></p>
                                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                                           <span :class="statusClass(doc.status)" class="badge-base">
                                                {{ doc.status }}
                                           </span>
                                           <span class="text-xs text-gray-500 dark:text-gray-400">
                                               Encontrado em: {{ new Date(doc.data_encontrado).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) }}
                                           </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editDocumento(doc)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar / Devolver"><Pencil class="w-5 h-5" /></button>
                                        <button @click="confirmDocDeletion(doc)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="empty-state">
                            <FileBadge class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Nenhum documento registrado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece registrando o primeiro documento encontrado.</p>
                        </div>
                    </div>

                    <div  v-if="documentos.data.length > 0" class="px-6 pb-4 border-t border-gray-200 dark:border-white/10 pt-4">
                        <Pagination :links="documentos.links" />
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isFormModalOpen" as="template">
            <Dialog as="div" @close="closeFormModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit">
                                    <div class="p-6">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar / Devolver Documento' : 'Registrar Novo Documento' }}</span>
                                            <button @click="closeFormModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-8">
                                            <fieldset class="space-y-6">
                                                <legend class="section-title">Dados do Documento</legend>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="nome_completo" class="form-label">Nome Completo (no documento) <span class="text-red-500">*</span></label>
                                                        <input type="text" v-model="form.nome_completo" id="nome_completo" class="form-input" required>
                                                        <div v-if="form.errors.nome_completo" class="form-error">{{ form.errors.nome_completo }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="tipo_documento" class="form-label">Tipo de Documento <span class="text-red-500">*</span></label>
                                                        <select v-model="form.tipo_documento" id="tipo_documento" class="form-input" required>
                                                            <option disabled value="">Selecione um tipo</option>
                                                            <option v-for="tipo in tiposDocumento" :key="tipo">{{ tipo }}</option>
                                                        </select>
                                                        <div v-if="form.errors.tipo_documento" class="form-error">{{ form.errors.tipo_documento }}</div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="numero_documento" class="form-label">Número do Documento (se houver)</label>
                                                    <input type="text" v-model="form.numero_documento" id="numero_documento" class="form-input">
                                                    <div v-if="form.errors.numero_documento" class="form-error">{{ form.errors.numero_documento }}</div>
                                                </div>
                                            </fieldset>

                                            <fieldset class="space-y-6">
                                                <legend class="section-title">Registro de Entrada</legend>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="data_encontrado" class="form-label">Data em que foi encontrado <span class="text-red-500">*</span></label>
                                                        <input type="date" v-model="form.data_encontrado" id="data_encontrado" class="form-input" required>
                                                        <div v-if="form.errors.data_encontrado" class="form-error">{{ form.errors.data_encontrado }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="local_encontrado" class="form-label">Local em que foi encontrado <span class="text-red-500">*</span></label>
                                                        <input type="text" v-model="form.local_encontrado" id="local_encontrado" class="form-input" required>
                                                        <div v-if="form.errors.local_encontrado" class="form-error">{{ form.errors.local_encontrado }}</div>
                                                    </div>
                                                </div>
                                                 <div>
                                                    <label for="entregue_por" class="form-label">Nome de quem entregou na Câmara <span class="text-red-500">*</span></label>
                                                    <input type="text" v-model="form.entregue_por" id="entregue_por" class="form-input" required>
                                                    <div v-if="form.errors.entregue_por" class="form-error">{{ form.errors.entregue_por }}</div>
                                                </div>
                                                <div>
                                                    <label for="observacoes" class="form-label">Observações</label>
                                                    <textarea v-model="form.observacoes" id="observacoes" rows="3" class="form-input"></textarea>
                                                    <div v-if="form.errors.observacoes" class="form-error">{{ form.errors.observacoes }}</div>
                                                </div>
                                            </fieldset>

                                            <fieldset v-if="isEditing" class="space-y-6">
                                                <legend class="section-title">Status e Devolução</legend>
                                                <div>
                                                    <label for="status" class="form-label">Status</label>
                                                    <select v-model="form.status" id="status" class="form-input">
                                                        <option>Aguardando Retirada</option>
                                                        <option>Entregue</option>
                                                    </select>
                                                </div>

                                                <div v-if="form.status === 'Entregue'" class="space-y-6 pt-6 border-t border-dashed border-gray-200 dark:border-white/10">
                                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div>
                                                            <label for="data_entrega" class="form-label">Data da Entrega <span class="text-red-500">*</span></label>
                                                            <input type="date" v-model="form.data_entrega" id="data_entrega" class="form-input" required>
                                                            <div v-if="form.errors.data_entrega" class="form-error">{{ form.errors.data_entrega }}</div>
                                                        </div>
                                                         <div>
                                                            <label for="retirado_por_cpf" class="form-label">CPF de quem retirou <span class="text-red-500">*</span></label>
                                                            <input type="text" v-model="form.retirado_por_cpf" v-maska data-maska="###.###.###-##" id="retirado_por_cpf" class="form-input" required>
                                                            <div v-if="form.errors.retirado_por_cpf" class="form-error">{{ form.errors.retirado_por_cpf }}</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label for="retirado_por_nome" class="form-label">Nome de quem retirou <span class="text-red-500">*</span></label>
                                                        <input type="text" v-model="form.retirado_por_nome" id="retirado_por_nome" class="form-input" required>
                                                        <div v-if="form.errors.retirado_por_nome" class="form-error">{{ form.errors.retirado_por_nome }}</div>
                                                    </div>
                                                </div>
                                            </fieldset>
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

       <ConfirmationModal
            :show="confirmingDocDeletion"
            title="Excluir Registro"
            :message="deleteConfirmationMessage"
            @close="confirmingDocDeletion = false"
            @confirm="deleteDocumento"
            danger
        />
    </TenantLayout>
</template>

<style scoped>
/* Estilos consistentes */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white dark:text-[#0A1E1C]; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.empty-state { @apply text-center py-12 px-6; }

/* --- NOVOS ESTILOS PARA A LISTA --- */
.document-item { @apply flex items-center justify-between p-4; }
.document-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.document-info { @apply text-sm text-gray-500 dark:text-gray-400; }

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
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider; }
</style>
