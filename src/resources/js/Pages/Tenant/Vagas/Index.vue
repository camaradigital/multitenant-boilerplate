<script setup>
import { ref } from 'vue';
import { useForm, Head, router, Link } from '@inertiajs/vue3'; // Importado Link
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
// Importado Users
import { Plus, Pencil, Trash2, Briefcase, X, Building2, MapPin, Users } from 'lucide-vue-next';

const props = defineProps({
    vagas: Object,
    empresas: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    titulo: '',
    empresa_id: '',
    descricao: '',
    responsabilidades: '',
    requisitos: '',
    salario: null,
    tipo_contratacao: 'CLT',
    localizacao: '',
    status: 'aberta',
    data_expiracao: '',
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editVaga = (vaga) => {
    isEditing.value = true;
    form.id = vaga.id;
    form.titulo = vaga.titulo;
    form.empresa_id = vaga.empresa_id;
    form.descricao = vaga.descricao || '';
    form.responsabilidades = vaga.responsabilidades || '';
    form.requisitos = vaga.requisitos || '';
    form.salario = vaga.salario;
    form.tipo_contratacao = vaga.tipo_contratacao;
    form.localizacao = vaga.localizacao;
    form.status = vaga.status;
    // Formata a data para o input type="date"
    form.data_expiracao = vaga.data_expiracao ? new Date(vaga.data_expiracao).toISOString().split('T')[0] : '';
    isModalOpen.value = true;
};


const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.vagas.update' : 'admin.vagas.store';
    const params = isEditing.value ? { vaga: form.id } : {};

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

const deleteVaga = (vaga) => {
    if (confirm(`Tem certeza que deseja remover a vaga "${vaga.titulo}"?`)) {
        router.delete(route('admin.vagas.destroy', { vaga: vaga.id }), {
            preserveScroll: true,
        });
    }
};

const getStatusClass = (status) => {
    const classes = {
        aberta: 'badge-active',
        fechada: 'badge-inactive',
        pausada: 'badge-paused'
    };
    return classes[status] || 'badge-info';
};
</script>

<template>
    <Head title="Vagas de Emprego" />

    <TenantLayout title="Vagas de Emprego">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Vagas de Emprego
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><Briefcase :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Mural de Vagas</h2>
                        <p class="form-subtitle">Adicione e gerencie as vagas de emprego disponíveis.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Nova Vaga
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="props.vagas.data.length > 0" class="space-y-4">
                        <div v-for="vaga in props.vagas.data" :key="vaga.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ vaga.titulo }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="badge-permission">
                                        <Building2 class="w-3 h-3 mr-1.5" />
                                        {{ vaga.empresa.nome_fantasia }}
                                    </span>
                                     <span class="badge-info">
                                        <MapPin class="w-3 h-3 mr-1.5" />
                                        {{ vaga.localizacao }}
                                    </span>
                                    <span :class="getStatusClass(vaga.status)">
                                        {{ vaga.status.charAt(0).toUpperCase() + vaga.status.slice(1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <!-- BOTÃO PARA VER CANDIDATURAS -->
                                 <Link :href="route('admin.vagas.candidaturas.index', { vaga: vaga.id })" class="table-action-btn hover:text-blue-600 dark:hover:text-blue-400 relative" title="Ver Candidaturas">
                                    <Users class="w-5 h-5" />
                                    <span v-if="vaga.candidaturas_count > 0" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">
                                        {{ vaga.candidaturas_count }}
                                    </span>
                                </Link>
                                <button @click="editVaga(vaga)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Vaga"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteVaga(vaga)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Vaga"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma vaga encontrada.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="props.vagas.links" />
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
                                            <span>{{ isEditing ? 'Editar Vaga' : 'Criar Nova Vaga' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2">
                                                <label for="titulo" class="form-label">Título da Vaga</label>
                                                <input type="text" v-model="form.titulo" id="titulo" class="form-input" required>
                                                <div v-if="form.errors.titulo" class="form-error">{{ form.errors.titulo }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="empresa_id" class="form-label">Empresa</label>
                                                <select v-model="form.empresa_id" id="empresa_id" class="form-input" required>
                                                    <option disabled value="">Selecione uma empresa</option>
                                                    <option v-for="empresa in props.empresas" :key="empresa.id" :value="empresa.id">{{ empresa.nome_fantasia }}</option>
                                                </select>
                                                <div v-if="form.errors.empresa_id" class="form-error">{{ form.errors.empresa_id }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="descricao" class="form-label">Descrição</label>
                                                <textarea v-model="form.descricao" id="descricao" rows="4" class="form-input" required></textarea>
                                                <div v-if="form.errors.descricao" class="form-error">{{ form.errors.descricao }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="responsabilidades" class="form-label">Responsabilidades (Opcional)</label>
                                                <textarea v-model="form.responsabilidades" id="responsabilidades" rows="3" class="form-input"></textarea>
                                                <div v-if="form.errors.responsabilidades" class="form-error">{{ form.errors.responsabilidades }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="requisitos" class="form-label">Requisitos (Opcional)</label>
                                                <textarea v-model="form.requisitos" id="requisitos" rows="3" class="form-input"></textarea>
                                                <div v-if="form.errors.requisitos" class="form-error">{{ form.errors.requisitos }}</div>
                                            </div>

                                            <div>
                                                <label for="salario" class="form-label">Salário (Opcional)</label>
                                                <input type="number" step="0.01" v-model="form.salario" id="salario" class="form-input" placeholder="Ex: 2500.00">
                                                <div v-if="form.errors.salario" class="form-error">{{ form.errors.salario }}</div>
                                            </div>
                                            <div>
                                                <label for="tipo_contratacao" class="form-label">Tipo de Contratação</label>
                                                <select v-model="form.tipo_contratacao" id="tipo_contratacao" class="form-input" required>
                                                    <option>CLT</option>
                                                    <option>PJ</option>
                                                    <option>Estágio</option>
                                                    <option>Temporário</option>
                                                    <option>Outro</option>
                                                </select>
                                                <div v-if="form.errors.tipo_contratacao" class="form-error">{{ form.errors.tipo_contratacao }}</div>
                                            </div>
                                            <div>
                                                <label for="localizacao" class="form-label">Localização</label>
                                                <input type="text" v-model="form.localizacao" id="localizacao" class="form-input" placeholder="Ex: Cidade, Estado" required>
                                                <div v-if="form.errors.localizacao" class="form-error">{{ form.errors.localizacao }}</div>
                                            </div>
                                            <div>
                                                <label for="status" class="form-label">Status</label>
                                                <select v-model="form.status" id="status" class="form-input" required>
                                                    <option value="aberta">Aberta</option>
                                                    <option value="pausada">Pausada</option>
                                                    <option value="fechada">Fechada</option>
                                                </select>
                                                <div v-if="form.errors.status" class="form-error">{{ form.errors.status }}</div>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label for="data_expiracao" class="form-label">Data de Expiração (Opcional)</label>
                                                <input type="date" v-model="form.data_expiracao" id="data_expiracao" class="form-input">
                                                <div v-if="form.errors.data_expiracao" class="form-error">{{ form.errors.data_expiracao }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Vaga' : 'Salvar Vaga' }}
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
/* Estilos consistentes com o seu design */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg; @apply bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-500/10 dark:text-sky-300; }
.badge-info { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-500/10 dark:text-slate-300; }
.badge-active { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-inactive { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300; }
.badge-paused { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-4xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>

