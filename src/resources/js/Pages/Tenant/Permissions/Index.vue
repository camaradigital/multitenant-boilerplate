<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import TextInput from '@/Components/TextInput.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, KeyRound, X, ChevronDown, ChevronsDown, ChevronsUp, Search } from 'lucide-vue-next';

const props = defineProps({
    permissions: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const confirmingPermissionDeletion = ref(false);
const permissionToDelete = ref(null);
const openModules = ref({});
const searchQuery = ref('');

const form = useForm({
    id: null,
    name: '',
});

// Agrupa as permissões por módulo
const groupedPermissions = computed(() => {
    const groups = {};
    if (props.permissions) {
        props.permissions.forEach(permission => {
            const [module, ...actionParts] = permission.name.split('.');
            const action = actionParts.join('.');
            if (!groups[module]) {
                groups[module] = [];
            }
            groups[module].push({ ...permission, action });
        });
    }
     // Inicializa o estado de todos os módulos como abertos
    Object.keys(groups).forEach(module => {
        if (openModules.value[module] === undefined) {
            openModules.value[module] = true;
        }
    });
    return groups;
});


const filteredGroupedPermissions = computed(() => {
    if (!searchQuery.value) {
        return groupedPermissions.value;
    }
    const query = searchQuery.value.toLowerCase();
    const filtered = {};
    for (const moduleName in groupedPermissions.value) {
        if (moduleName.toLowerCase().includes(query) || groupedPermissions.value[moduleName].some(p => p.action.toLowerCase().includes(query))) {
            filtered[moduleName] = groupedPermissions.value[moduleName];
        }
    }
    return filtered;
});

// ADICIONADO: Propriedade computada para a mensagem de confirmação
const deleteConfirmationMessage = computed(() => {
    if (permissionToDelete.value) {
        return `Tem certeza que deseja remover a permissão "${permissionToDelete.value.name}"? Esta ação não pode ser desfeita.`;
    }
    return 'Você tem certeza que deseja continuar?';
});

const formatModuleName = (name) => {
    return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const toggleModule = (moduleName) => {
    openModules.value[moduleName] = !openModules.value[moduleName];
};

const expandAll = () => {
    Object.keys(groupedPermissions.value).forEach(module => openModules.value[module] = true);
};

const collapseAll = () => {
    Object.keys(groupedPermissions.value).forEach(module => openModules.value[module] = false);
};


const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editPermission = (permission) => {
    isEditing.value = true;
    form.id = permission.id;
    form.name = permission.name;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const confirmPermissionDeletion = (permission) => {
    permissionToDelete.value = permission;
    confirmingPermissionDeletion.value = true;
};

const deletePermission = () => {
    form.delete(route('admin.permissions.destroy', { permission: permissionToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingPermissionDeletion.value = false;
            permissionToDelete.value = null;
        },
    });
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.permissions.update' : 'admin.permissions.store';
    const params = isEditing.value ? { permission: form.id } : {};

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
    <Head title="Permissões" />

    <TenantLayout title="Permissões">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Permissões
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
                    <div class="form-icon"><KeyRound :size="32" class="icon-in-badge" /></div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Permissões do Sistema</h2>
                            <p class="form-subtitle">Crie e gerencie as permissões agrupadas por módulo.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="openModal" class="btn-primary">
                                <Plus class="h-4 w-4 mr-2" />
                                Nova Permissão
                            </button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-b-dynamic flex flex-col sm:flex-row gap-4 items-center">
                         <div class="relative w-full sm:w-2/3 lg:w-1/3">
                            <TextInput v-model="searchQuery" placeholder="Filtrar módulos ou ações..." class="pl-10" />
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="expandAll" class="btn-secondary-sm">
                                <ChevronsDown class="w-4 h-4 mr-2" />
                                Expandir Tudo
                            </button>
                             <button @click="collapseAll" class="btn-secondary-sm">
                                <ChevronsUp class="w-4 h-4 mr-2" />
                                Recolher Tudo
                            </button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="Object.keys(filteredGroupedPermissions).length > 0" class="space-y-4">
                            <div v-for="(modulePermissions, moduleName) in filteredGroupedPermissions" :key="moduleName" class="module-card">
                                <button @click="toggleModule(moduleName)" class="module-header">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="module-title">{{ formatModuleName(moduleName) }}</h3>
                                        <span class="module-count">{{ modulePermissions.length }} permissões</span>
                                    </div>
                                    <ChevronDown class="w-5 h-5 transition-transform" :class="{ 'rotate-180': !openModules[moduleName] }" />
                                </button>
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="transform opacity-0 -translate-y-2"
                                    enter-to-class="transform opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="transform opacity-100 translate-y-0"
                                    leave-to-class="transform opacity-0 -translate-y-2"
                                >
                                    <div v-show="openModules[moduleName]" class="module-content">
                                        <div v-for="permission in modulePermissions" :key="permission.id" class="permission-chip">
                                            <span class="font-mono text-sm">{{ permission.action }}</span>
                                            <div class="flex items-center space-x-1">
                                                <button @click="editPermission(permission)" class="chip-action-btn hover:text-amber-500" title="Editar"><Pencil class="w-4 h-4" /></button>
                                                <button @click="confirmPermissionDeletion(permission)" class="chip-action-btn hover:text-red-500" title="Excluir"><Trash2 class="w-4 h-4" /></button>
                                            </div>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                         <div v-else class="text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Nenhuma permissão encontrada para "{{ searchQuery }}".</p>
                        </div>
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
                                            <span>{{ isEditing ? 'Editar Permissão' : 'Criar Nova Permissão' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6">
                                            <label for="name" class="form-label">Nome da Permissão</label>
                                            <input type="text" v-model="form.name" id="name" class="form-input font-mono" placeholder="Ex: solicitacoes.criar" required>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-left">Use o formato "modulo.acao" (ex: usuarios.visualizar_todos, servicos.criar).</p>
                                            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar' : 'Salvar' }}
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
            :show="confirmingPermissionDeletion"
            title="Excluir Permissão"
            :message="deleteConfirmationMessage"
            @close="confirmingPermissionDeletion = false"
            @confirm="deletePermission"
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

.module-card { @apply bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden transition-all duration-300; }
.module-header { @apply w-full flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5; }
.module-title { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.module-count { @apply text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full; }
.module-content { @apply p-4 border-t border-gray-200 dark:border-white/10 flex flex-wrap gap-2; }

.permission-chip { @apply flex items-center justify-between bg-emerald-50 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-200 rounded-full pl-3 pr-2 py-1; }
.chip-action-btn { @apply p-1 rounded-full text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors; }

.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-secondary-sm { @apply inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-danger { @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150; }

.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
</style>
