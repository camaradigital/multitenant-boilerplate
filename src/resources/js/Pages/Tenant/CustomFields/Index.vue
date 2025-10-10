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
import { Plus, Pencil, Trash2, ListChecks, X, PlusCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    customFields: Object, // Objeto de paginação
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const newOptionText = ref(''); // Para adicionar novas opções ao select

// --- Lógica para o Modal de Exclusão ---
const confirmingFieldDeletion = ref(false);
const fieldToDelete = ref(null);
const deleteForm = useForm({});

const confirmFieldDeletion = (field) => {
    fieldToDelete.value = field;
    confirmingFieldDeletion.value = true;
};

const deleteField = () => {
    deleteForm.delete(route('admin.custom-fields.destroy', fieldToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingFieldDeletion.value = false;
            fieldToDelete.value = null;
        }
    });
};

const deleteConfirmationMessage = computed(() => {
    return fieldToDelete.value ? `Tem certeza que deseja remover o campo "${fieldToDelete.value.label}"? Esta ação não pode ser desfeita.` : '';
});
// --- Fim da Lógica de Exclusão ---

const form = useForm({
    id: null,
    label: '',
    type: 'text',
    options: [],
    is_required: false,
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    newOptionText.value = '';
    isModalOpen.value = true;
};

const editField = (field) => {
    isEditing.value = true;
    form.id = field.id;
    form.label = field.label;
    form.type = field.type;
    form.options = field.options || [];
    form.is_required = field.is_required;
    form.clearErrors();
    newOptionText.value = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const addOption = () => {
    if (newOptionText.value.trim() !== '' && !form.options.includes(newOptionText.value.trim())) {
        form.options.push(newOptionText.value.trim());
        newOptionText.value = '';
    }
};

const removeOption = (index) => {
    form.options.splice(index, 1);
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.custom-fields.update' : 'admin.custom-fields.store';
    const params = isEditing.value ? { custom_field: form.id } : {};

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
</script>

<template>
    <Head title="Campos Personalizados" />

    <TenantLayout title="Campos Personalizados">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Campos Personalizados do Cidadão
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
                    <div class="form-icon"><ListChecks :size="32" class="icon-in-badge" /></div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Campos Personalizados</h2>
                            <p class="form-subtitle">Crie campos adicionais para o cadastro do cidadão.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="openModal" class="btn-primary">
                                <Plus class="h-4 w-4 mr-2" />
                                Novo Campo
                            </button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="customFields.data.length > 0">
                            <ul class="divide-y divide-gray-200 dark:divide-white/10">
                                <li v-for="field in customFields.data" :key="field.id" class="field-item group">
                                    <div class="flex-1 min-w-0">
                                        <p class="field-name">{{ field.label }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="badge-base badge-type">
                                                Tipo: {{ field.type }}
                                            </span>
                                            <span :class="field.is_required ? 'badge-required' : 'badge-optional'" class="badge-base">
                                                {{ field.is_required ? 'Obrigatório' : 'Opcional' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editField(field)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar"><Pencil class="w-5 h-5" /></button>
                                        <button @click="confirmFieldDeletion(field)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="empty-state">
                            <ListChecks class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">Nenhum campo personalizado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece criando um novo campo para os cadastros.</p>
                        </div>
                    </div>

                    <div v-if="customFields.data.length > 0" class="px-6 pb-4 border-t border-gray-200 dark:border-white/10 pt-4">
                        <Pagination :links="customFields.links" />
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
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit">
                                    <div class="p-6">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Campo' : 'Criar Novo Campo' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-8">
                                            <fieldset>
                                                 <legend class="section-title">Detalhes do Campo</legend>
                                                 <div class="mt-4 space-y-6">
                                                     <div>
                                                        <label for="label" class="form-label">Nome do Campo (Rótulo)</label>
                                                        <input type="text" v-model="form.label" id="label" class="form-input" placeholder="Ex: Número do NIS" required>
                                                        <div v-if="form.errors.label" class="form-error">{{ form.errors.label }}</div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div>
                                                            <label for="type" class="form-label">Tipo de Campo</label>
                                                            <select v-model="form.type" id="type" class="form-input">
                                                                <option value="text">Texto</option>
                                                                <option value="number">Número</option>
                                                                <option value="date">Data</option>
                                                                <option value="select">Lista de Opções</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label for="is_required" class="form-label mb-3">Obrigatório?</label>
                                                            <label for="is_required" class="toggle-switch-label !justify-start">
                                                                <div class="toggle-switch">
                                                                    <input type="checkbox" v-model="form.is_required" id="is_required" class="toggle-switch-checkbox">
                                                                    <div class="toggle-switch-bg"></div>
                                                                    <div class="toggle-switch-indicator"></div>
                                                                </div>
                                                                <span class="ml-3 font-medium text-gray-900 dark:text-gray-100">{{ form.is_required ? 'Sim' : 'Não' }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                 </div>
                                            </fieldset>

                                            <fieldset v-if="form.type === 'select'">
                                                <legend class="section-title">Opções da Lista</legend>
                                                <div class="mt-4 space-y-4">
                                                    <div class="relative">
                                                        <input type="text" v-model="newOptionText" @keydown.enter.prevent="addOption" class="form-input pr-12" placeholder="Digite uma opção...">
                                                        <button type="button" @click="addOption" class="absolute inset-y-0 right-0 flex items-center justify-center px-3 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300" title="Adicionar Opção">
                                                            <PlusCircle class="w-6 h-6"/>
                                                        </button>
                                                    </div>
                                                    <div v-if="form.errors.options" class="form-error">{{ form.errors.options }}</div>

                                                    <div v-if="form.options.length > 0" class="flex flex-wrap gap-2 rounded-lg border border-gray-200 dark:border-white/10 p-3">
                                                        <div v-for="(option, index) in form.options" :key="index" class="option-chip">
                                                            <span>{{ option }}</span>
                                                            <button @click="removeOption(index)" type="button" class="option-chip-remove">
                                                                <X class="w-3 h-3"/>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Campo' : 'Salvar Campo' }}
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
            :show="confirmingFieldDeletion"
            title="Excluir Campo Personalizado"
            :message="deleteConfirmationMessage"
            @close="confirmingFieldDeletion = false"
            @confirm="deleteField"
            danger
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
.empty-state { @apply text-center py-12 px-6; }

/* --- NOVOS ESTILOS PARA A LISTA --- */
.field-item { @apply flex items-center justify-between p-4; }
.field-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }

.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.badge-type { @apply bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-required { @apply bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300; }
.badge-optional { @apply bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300; }

.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

/* --- ESTILOS DO MODAL E FORMULÁRIO --- */
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.section-title { @apply text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider; }

/* --- ESTILOS PARA OPÇÕES DE SELECT --- */
.option-chip { @apply inline-flex items-center gap-2 pl-3 pr-2 py-1 rounded-full text-sm font-medium; @apply bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200; }
.option-chip-remove { @apply p-0.5 rounded-full transition-colors; @apply text-emerald-600 hover:bg-emerald-200 dark:text-emerald-400 dark:hover:bg-emerald-700; }


/* --- ESTILOS PARA TOGGLE SWITCH --- */
.toggle-switch-label { @apply flex items-center justify-between cursor-pointer text-sm; }
.toggle-switch { @apply relative inline-flex items-center h-6 rounded-full w-11 transition-colors flex-shrink-0; }
.toggle-switch-checkbox { @apply absolute w-full h-full opacity-0 cursor-pointer; }
.toggle-switch-bg { @apply w-full h-full rounded-full transition-colors; @apply bg-gray-200 dark:bg-gray-700; }
.toggle-switch-indicator { @apply absolute left-1 top-1 w-4 h-4 rounded-full transition-transform; @apply bg-white; }
.toggle-switch-checkbox:checked + .toggle-switch-bg { @apply bg-emerald-600 dark:bg-green-500; }
.toggle-switch-checkbox:checked ~ .toggle-switch-indicator { @apply translate-x-5; }
</style>
