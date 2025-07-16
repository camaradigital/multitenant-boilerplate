<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Pencil, Trash2, ShieldCheck, X, Lock } from 'lucide-vue-next';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    permissions: [],
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editRole = (role) => {
    isEditing.value = true;
    form.id = role.id;
    form.name = role.name;
    form.permissions = role.permissions.map(p => p.name);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'central.roles_permissions.update' : 'central.roles_permissions.store';
    const params = isEditing.value ? { roles_permission: form.id } : {};

    if (isEditing.value) {
        form.put(route(routeName, params), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route(routeName, params), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteRole = (roleId) => {
    if (confirm('Tem certeza que deseja remover este papel?')) {
        router.delete(route('central.roles_permissions.destroy', roleId), {
            preserveScroll: true,
        });
    }
};

const groupedPermissions = computed(() => {
    return props.permissions.reduce((acc, permission) => {
        const group = permission.name.split(' ')[1] || 'geral';
        if (!acc[group]) {
            acc[group] = [];
        }
        acc[group].push(permission);
        return acc;
    }, {});
});
</script>

<template>
    <Head title="Gerenciar Papéis e Permissões" />

    <AppLayout title="Papéis e Permissões">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Papéis e Permissões
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><Lock :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Papéis do Sistema</h2>
                        <p class="form-subtitle">Controle o que cada tipo de usuário pode fazer.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Novo Papel
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div class="space-y-4">
                         <div v-for="role in roles" :key="role.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ role.name }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span v-for="permission in role.permissions" :key="permission.id" class="badge-permission">
                                        <ShieldCheck class="w-3.5 h-3.5 mr-1.5" />
                                        {{ permission.name }}
                                    </span>
                                    <span v-if="!role.permissions.length" class="text-xs text-gray-500 italic px-2 py-1">Nenhuma permissão associada.</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editRole(role)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Papel"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteRole(role.id)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Papel"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Criar/Editar Papel -->
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
                                            <span>{{ isEditing ? 'Editar Papel' : 'Criar Novo Papel' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div>
                                                <label for="name" class="form-label">Nome do Papel</label>
                                                <input type="text" v-model="form.name" id="name" class="form-input" required>
                                                <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                            </div>

                                            <div>
                                                <h4 class="form-label mb-3">Permissões</h4>
                                                <div class="space-y-5">
                                                    <div v-for="(permissionGroup, groupName) in groupedPermissions" :key="groupName">
                                                        <p class="font-semibold text-sm capitalize text-gray-600 dark:text-gray-400 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">{{ groupName }}</p>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                                                            <div v-for="permission in permissionGroup" :key="permission.id" class="flex items-center">
                                                                <input type="checkbox" :id="'permission-' + permission.id" :value="permission.name" v-model="form.permissions" class="form-checkbox">
                                                                <label :for="'permission-' + permission.id" class="ml-3 block text-sm text-gray-800 dark:text-gray-300">{{ permission.name }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
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
    </AppLayout>
</template>

<style scoped>
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }

.role-card {
    @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20;
}
.role-name {
    @apply text-lg font-bold text-emerald-800 dark:text-emerald-300;
}

.badge-permission {
    @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300;
}

.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }

.modal-panel {
    @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all;
    @apply bg-white dark:bg-gray-800;
}

.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-checkbox { @apply h-4 w-4 text-emerald-600 border-gray-300 dark:border-gray-500 rounded focus:ring-emerald-500 dark:bg-gray-600 dark:checked:bg-emerald-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
</style>
