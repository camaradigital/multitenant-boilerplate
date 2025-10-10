<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import TextInput from '@/Components/TextInput.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, ShieldCheck, X, Search, Lock } from 'lucide-vue-next';

const props = defineProps({
    roles: Object,
    permissionsDisponiveis: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const confirmingRoleDeletion = ref(false);
const roleToDelete = ref(null);
const searchQuery = ref('');

const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

const filteredRoles = computed(() => {
    if (!searchQuery.value) {
        return props.roles.data;
    }
    return props.roles.data.filter(role =>
        role.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const groupedPermissions = computed(() => {
    const groups = {};
    props.permissionsDisponiveis.forEach(permission => {
        const [module, ...actionParts] = permission.name.split('.');
        const action = actionParts.join('.');

        if (!groups[module]) {
            groups[module] = [];
        }
        groups[module].push({ ...permission, action });
    });
    return Object.keys(groups).sort().reduce((sorted, key) => {
        sorted[key] = groups[key];
        return sorted;
    }, {});
});

const formatModuleName = (name) => {
    return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editRole = (role) => {
    isEditing.value = true;
    form.id = role.id;
    form.name = role.name;
    form.permissions = role.permissions.map(p => p.id);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const confirmRoleDeletion = (role) => {
    if (role.name === 'Admin Tenant') return;
    roleToDelete.value = role;
    confirmingRoleDeletion.value = true;
};

const deleteRole = () => {
    router.delete(route('admin.roles-permissions.destroy', roleToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingRoleDeletion.value = false;
            roleToDelete.value = null;
        }
    });
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.roles-permissions.update' : 'admin.roles-permissions.store';

    // CORREÇÃO APLICADA AQUI: O nome do parâmetro deve ser 'rolesPermission' para corresponder à definição da rota no Laravel.
    const params = isEditing.value ? { rolesPermission: form.id } : {};

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

const toggleAllPermissionsInGroup = (groupPermissions, isChecked) => {
    const permissionIds = groupPermissions.map(p => p.id);
    if (isChecked) {
        form.permissions = [...new Set([...form.permissions, ...permissionIds])];
    } else {
        form.permissions = form.permissions.filter(id => !permissionIds.includes(id));
    }
};

const isAllSelected = (groupPermissions) => {
    if (!groupPermissions || groupPermissions.length === 0) return false;
    const permissionIds = groupPermissions.map(p => p.id);
    return permissionIds.every(id => form.permissions.includes(id));
};

</script>

<template>
    <Head title="Papéis e Permissões" />

    <TenantLayout title="Papéis e Permissões">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Papéis e Permissões
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="content-container">
                    <div class="form-icon"><ShieldCheck :size="32" class="icon-in-badge" /></div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                        <div>
                            <h2 class="header-title">Papéis de Usuário</h2>
                            <p class="form-subtitle">Crie e gerencie os papéis e suas respectivas permissões no sistema.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button @click="openModal" class="btn-primary">
                                <Plus class="h-4 w-4 mr-2" />
                                Novo Papel
                            </button>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 border-b-dynamic">
                        <div class="relative">
                            <TextInput v-model="searchQuery" placeholder="Buscar papel..." class="pl-10 w-full md:w-1/3" />
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="p-4 md:p-6">
                        <div v-if="filteredRoles.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="role in filteredRoles" :key="role.id" class="role-card">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <Lock v-if="role.name === 'Admin Tenant'" class="w-5 h-5 text-amber-500" />
                                        <p class="role-name">{{ role.name }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ role.permissions.length }} permissões</p>
                                     <div class="mt-3 flex flex-wrap gap-1.5" v-if="role.permissions.length > 0">
                                        <span v-for="permission in role.permissions.slice(0, 4)" :key="permission.id" class="badge-permission">
                                            {{ permission.name }}
                                        </span>
                                        <span v-if="role.permissions.length > 4" class="badge-permission-more">
                                            +{{ role.permissions.length - 4 }} mais
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center space-y-2 ml-4">
                                    <button @click="editRole(role)" class="table-action-btn hover:text-amber-600" title="Editar"><Pencil class="w-5 h-5" /></button>
                                    <button
                                        @click="confirmRoleDeletion(role)"
                                        class="table-action-btn"
                                        :class="{'opacity-50 cursor-not-allowed': role.name === 'Admin Tenant', 'hover:text-red-600': role.name !== 'Admin Tenant'}"
                                        :disabled="role.name === 'Admin Tenant'"
                                        title="Excluir">
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                         <div v-else class="text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Nenhum papel encontrado para "{{ searchQuery }}".</p>
                        </div>
                    </div>

                    <div v-if="roles.links.length > 3" class="px-6 pb-4">
                        <Pagination :links="roles.links" />
                    </div>
                </div>
            </div>
        </div>

        <TransitionRoot appear :show="isModalOpen" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="modal-panel">
                                <form @submit.prevent="submit" class="flex flex-col h-full">
                                    <div class="p-6 flex-shrink-0">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                                            <span>{{ isEditing ? 'Editar Papel' : 'Criar Novo Papel' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>
                                    </div>

                                    <div class="px-6 flex-grow overflow-y-auto space-y-6">
                                        <div>
                                            <label for="name" class="form-label">Nome do Papel</label>
                                            <input type="text" v-model="form.name" id="name" class="form-input" required>
                                            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                        </div>

                                        <div>
                                            <div class="flex justify-between items-center mb-2">
                                                <label class="form-label">Permissões</label>
                                                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ form.permissions.length }} / {{ permissionsDisponiveis.length }} selecionadas</span>
                                            </div>
                                            <div v-if="form.errors.permissions" class="form-error mb-2">{{ form.errors.permissions }}</div>
                                            <div class="permission-grid">
                                                <div v-for="(groupPermissions, moduleName) in groupedPermissions" :key="moduleName" class="permission-group">
                                                    <div class="permission-group-header">
                                                        <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-200 capitalize">{{ formatModuleName(moduleName) }}</h4>
                                                        <label class="flex items-center text-xs cursor-pointer">
                                                            <input type="checkbox" @change="toggleAllPermissionsInGroup(groupPermissions, $event.target.checked)" :checked="isAllSelected(groupPermissions)" class="form-checkbox-sm">
                                                            <span class="ml-2">Marcar Todos</span>
                                                        </label>
                                                    </div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-2 pt-3">
                                                        <label v-for="permission in groupPermissions" :key="permission.id" class="flex items-center cursor-pointer">
                                                            <input type="checkbox" v-model="form.permissions" :value="permission.id" class="form-checkbox">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 font-mono">{{ permission.action }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl flex-shrink-0">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Papel' : 'Salvar Papel' }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <ConfirmationModal :show="confirmingRoleDeletion" @close="confirmingRoleDeletion = false">
            <template #title>
                Excluir Papel
            </template>
            <template #content>
                Tem certeza que deseja remover o papel "<strong>{{ roleToDelete?.name }}</strong>"? Esta ação não pode ser desfeita.
            </template>
            <template #footer>
                <button class="btn-secondary" @click="confirmingRoleDeletion = false">
                    Cancelar
                </button>
                <button class="btn-danger ml-3" @click="deleteRole" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Excluir
                </button>
            </template>
        </ConfirmationModal>
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
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-start justify-between transition hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-600; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-permission { @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-permission-more { @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2; }
.btn-danger { @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-4xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all h-[90vh] max-h-[700px]; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 text-left; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1 text-left; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
.form-checkbox-sm { @apply h-3.5 w-3.5 rounded-sm border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }

.permission-grid { @apply space-y-4; }
.permission-group { @apply p-4 rounded-lg border border-gray-200 dark:border-gray-700; }
.permission-group-header { @apply flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2 mb-2; }
</style>
