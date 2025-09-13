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
import { Plus, Pencil, Trash2, Users, X } from 'lucide-vue-next';

const props = defineProps({
    funcionarios: Object, // Objeto de paginação
    rolesDisponiveis: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    email: '',
    roles: [], // Será um array de IDs
    password: '',
    password_confirmation: '',
    is_active: true, // Campo de status adicionado
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editFuncionario = (funcionario) => {
    isEditing.value = true;
    form.id = funcionario.id;
    form.name = funcionario.name;
    form.email = funcionario.email;
    form.roles = funcionario.roles.map(role => role.id);
    form.is_active = funcionario.is_active;
    form.password = '';
    form.password_confirmation = '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.funcionarios.update' : 'admin.funcionarios.store';
    const params = isEditing.value ? { funcionario: form.id } : {};

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

const deleteFuncionario = (funcionario) => {
    if (confirm('Tem certeza que deseja remover este membro da equipe?')) {
        router.delete(route('admin.funcionarios.destroy', funcionario), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Equipe" />

    <TenantLayout title="Equipe">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Equipe
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><Users :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Membros da Equipe</h2>
                        <p class="form-subtitle">Adicione e gerencie os usuários do painel administrativo.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Novo Membro
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="funcionarios.data.length > 0" class="space-y-4">
                        <div v-for="funcionario in funcionarios.data" :key="funcionario.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ funcionario.name }}</p>
                                <p class="form-subtitle">{{ funcionario.email }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                     <span v-for="role in funcionario.roles" :key="role.id" class="badge-permission">
                                         {{ role.name }}
                                     </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editFuncionario(funcionario)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteFuncionario(funcionario)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                     <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhum funcionário encontrado.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="funcionarios.links" />
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
                                            <span>{{ isEditing ? 'Editar Membro da Equipe' : 'Adicionar Novo Membro' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label for="name" class="form-label">Nome Completo</label>
                                                    <input type="text" v-model="form.name" id="name" class="form-input" required>
                                                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                                </div>
                                                <div>
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" v-model="form.email" id="email" class="form-input" required>
                                                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="form-label">Papéis (Funções)</label>
                                                     <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                                         <label v-for="role in rolesDisponiveis" :key="role.id" class="flex items-center">
                                                             <input type="checkbox" v-model="form.roles" :value="role.id" class="form-checkbox">
                                                             <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ role.name }}</span>
                                                         </label>
                                                     </div>
                                                    <div v-if="form.errors.roles" class="form-error">{{ form.errors.roles }}</div>
                                                </div>
                                                <div>
                                                    <label for="password" class="form-label">{{ isEditing ? 'Nova Senha (Opcional)' : 'Senha' }}</label>
                                                    <input type="password" v-model="form.password" id="password" class="form-input" :required="!isEditing">
                                                    <div v-if="form.errors.password" class="form-error">{{ form.errors.password }}</div>
                                                </div>
                                                <div>
                                                    <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                                                    <input type="password" v-model="form.password_confirmation" id="password_confirmation" class="form-input" :required="form.password !== ''">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="is_active" class="form-label">Status</label>
                                                    <select v-model="form.is_active" id="is_active" class="form-input">
                                                        <option :value="true">Ativo</option>
                                                        <option :value="false">Inativo</option>
                                                    </select>
                                                    <div v-if="form.errors.is_active" class="form-error">{{ form.errors.is_active }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Membro' : 'Salvar Membro' }}
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
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>
