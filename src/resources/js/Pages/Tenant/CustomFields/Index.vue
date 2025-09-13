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
import { Plus, Pencil, Trash2, ListChecks, X, PlusCircle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    customFields: Object, // Objeto de paginação
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const newOptionText = ref(''); // Para adicionar novas opções ao select

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
    newOptionText.value = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const addOption = () => {
    if (newOptionText.value.trim() !== '') {
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

const deleteField = (field) => {
    if (confirm('Tem certeza que deseja remover este campo personalizado?')) {
        router.delete(route('admin.custom-fields.destroy', field), {
            preserveScroll: true,
        });
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

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
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
                    <div v-if="customFields.data.length > 0" class="space-y-4">
                        <div v-for="field in customFields.data" :key="field.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ field.label }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                     <span class="badge-permission">
                                        Tipo: {{ field.type }}
                                    </span>
                                    <span :class="field.is_required ? 'badge-required' : 'badge-optional'">
                                        {{ field.is_required ? 'Obrigatório' : 'Opcional' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editField(field)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteField(field)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                     <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhum campo personalizado encontrado.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="customFields.links" />
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
                                            <span>{{ isEditing ? 'Editar Campo' : 'Criar Novo Campo' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div>
                                                <label for="label" class="form-label">Nome do Campo (Rótulo)</label>
                                                <input type="text" v-model="form.label" id="label" class="form-input" placeholder="Ex: Número do NIS" required>
                                                <div v-if="form.errors.label" class="form-error">{{ form.errors.label }}</div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                                    <label class="form-label">Obrigatório?</label>
                                                    <div class="flex items-center space-x-4 mt-2 h-12">
                                                        <label class="flex items-center">
                                                            <input type="radio" v-model="form.is_required" :value="true" name="is_required" class="form-radio">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sim</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="radio" v-model="form.is_required" :value="false" name="is_required" class="form-radio">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Não</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="form.type === 'select'" class="space-y-4">
                                                <label class="form-label">Opções da Lista</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="text" v-model="newOptionText" @keydown.enter.prevent="addOption" class="form-input" placeholder="Digite uma opção e tecle Enter">
                                                    <button type="button" @click="addOption" class="btn-primary !p-3"><PlusCircle class="w-5 h-5"/></button>
                                                </div>
                                                <div v-if="form.errors.options" class="form-error">{{ form.errors.options }}</div>

                                                <div v-if="form.options.length > 0" class="space-y-2">
                                                    <div v-for="(option, index) in form.options" :key="index" class="flex items-center justify-between bg-gray-100 dark:bg-gray-700/50 p-2 rounded-lg">
                                                        <span class="text-sm text-gray-800 dark:text-gray-200">{{ option }}</span>
                                                        <button @click="removeOption(index)" type="button" class="text-red-500 hover:text-red-700">
                                                            <XCircle class="w-5 h-5"/>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
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
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-required { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300; }
.badge-optional { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-radio { @apply h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>
