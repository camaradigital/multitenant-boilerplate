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
// Ícone de Globo adicionado para o novo badge
import { Plus, Pencil, Trash2, Briefcase, X, Scale, Globe } from 'lucide-vue-next';

const props = defineProps({
    servicos: Object,
    tiposServico: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    nome: '',
    tipo_servico_id: '',
    descricao: '',
    is_active: true,
    is_juridico: false,
    permite_solicitacao_online: false, // <-- NOVO CAMPO ADICIONADO
    regras_limite: {
        ativo: false,
        quantidade: 1,
        periodo: 'semana',
    },
});

const openModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const editServico = (servico) => {
    isEditing.value = true;
    form.id = servico.id;
    form.nome = servico.nome;
    form.tipo_servico_id = servico.tipo_servico.id;
    form.descricao = servico.descricao || '';
    form.is_active = servico.is_active;
    form.is_juridico = servico.is_juridico;
    form.permite_solicitacao_online = servico.permite_solicitacao_online; // <-- NOVO CAMPO ADICIONADO

    if (servico.regras_limite && servico.regras_limite.ativo) {
        form.regras_limite = { ...servico.regras_limite };
    } else {
        form.regras_limite = { ativo: false, quantidade: 1, periodo: 'semana' };
    }

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = isEditing.value ? 'admin.servicos.update' : 'admin.servicos.store';
    const params = isEditing.value ? { servico: form.id } : {};

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

const deleteServico = (servico) => {
    if (confirm('Tem certeza que deseja remover este serviço?')) {
        router.delete(route('admin.servicos.destroy', servico), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Serviços" />

    <TenantLayout title="Serviços">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Serviços
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-5xl">
                <div class="form-icon"><Briefcase :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Catálogo de Serviços</h2>
                        <p class="form-subtitle">Adicione e gerencie os serviços oferecidos.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Novo Serviço
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="servicos.data.length > 0" class="space-y-4">
                        <div v-for="servico in servicos.data" :key="servico.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ servico.nome }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="badge-permission">
                                        {{ servico.tipo_servico.nome }}
                                    </span>
                                    <span :class="servico.is_active ? 'badge-active' : 'badge-inactive'">
                                        {{ servico.is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                    <span v-if="servico.regras_limite && servico.regras_limite.ativo" class="badge-limit">
                                        Limite: {{ servico.regras_limite.quantidade }} / {{ servico.regras_limite.periodo }}
                                    </span>
                                    <span v-if="servico.is_juridico" class="badge-juridico">
                                        <Scale class="w-3 h-3 mr-1.5" />
                                        Jurídico
                                    </span>
                                     <!-- NOVO BADGE PARA SERVIÇO ONLINE -->
                                    <span v-if="servico.permite_solicitacao_online" class="badge-online">
                                        <Globe class="w-3 h-3 mr-1.5" />
                                        Online
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button @click="editServico(servico)" class="table-action-btn hover:text-amber-600 dark:hover:text-yellow-400" title="Editar Serviço"><Pencil class="w-5 h-5" /></button>
                                <button @click="deleteServico(servico)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Serviço"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhum serviço encontrado.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="servicos.links" />
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
                                            <span>{{ isEditing ? 'Editar Serviço' : 'Criar Novo Serviço' }}</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2">
                                                <label for="nome" class="form-label">Nome do Serviço</label>
                                                <input type="text" v-model="form.nome" id="nome" class="form-input" required>
                                                <div v-if="form.errors.nome" class="form-error">{{ form.errors.nome }}</div>
                                            </div>

                                            <div>
                                                <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                                                <select v-model="form.tipo_servico_id" id="tipo_servico" class="form-input" required>
                                                    <option disabled value="">Selecione um tipo</option>
                                                    <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                                </select>
                                                <div v-if="form.errors.tipo_servico_id" class="form-error">{{ form.errors.tipo_servico_id }}</div>
                                            </div>

                                            <div>
                                                <label for="is_active" class="form-label">Status</label>
                                                <select v-model="form.is_active" id="is_active" class="form-input" required>
                                                    <option :value="true">Ativo</option>
                                                    <option :value="false">Inativo</option>
                                                </select>
                                                <div v-if="form.errors.is_active" class="form-error">{{ form.errors.is_active }}</div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label for="descricao" class="form-label">Descrição (Opcional)</label>
                                                <textarea v-model="form.descricao" id="descricao" rows="3" class="form-input"></textarea>
                                                <div v-if="form.errors.descricao" class="form-error">{{ form.errors.descricao }}</div>
                                            </div>

                                            <!-- LINHA DIVISÓRIA PARA OPÇÕES AVANÇADAS -->
                                            <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                                                <!-- CHECKBOX SERVIÇO JURÍDICO -->
                                                <label class="flex items-center">
                                                    <input type="checkbox" v-model="form.is_juridico" class="form-checkbox">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Este é um serviço de natureza jurídica</span>
                                                </label>

                                                <!-- CHECKBOX PERMITE SOLICITAÇÃO ONLINE -->
                                                <label class="flex items-center">
                                                    <input type="checkbox" v-model="form.permite_solicitacao_online" class="form-checkbox">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Permitir solicitação online por cidadãos</span>
                                                </label>
                                            </div>

                                            <div class="md:col-span-2 p-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl space-y-4">
                                                <div class="flex items-center">
                                                    <input type="checkbox" v-model="form.regras_limite.ativo" id="limite_ativo" class="form-checkbox">
                                                    <label for="limite_ativo" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">
                                                        Ativar Limite de Uso por Cidadão
                                                    </label>
                                                </div>

                                                <div v-if="form.regras_limite.ativo" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="limite_quantidade" class="form-label">Quantidade</label>
                                                        <input type="number" v-model="form.regras_limite.quantidade" id="limite_quantidade" class="form-input" min="1" required>
                                                        <div v-if="form.errors['regras_limite.quantidade']" class="form-error">{{ form.errors['regras_limite.quantidade'] }}</div>
                                                    </div>
                                                    <div>
                                                        <label for="limite_periodo" class="form-label">Período</label>
                                                        <select v-model="form.regras_limite.periodo" id="limite_periodo" class="form-input" required>
                                                            <option value="dia">Por Dia</option>
                                                            <option value="semana">Por Semana</option>
                                                            <option value="mes">Por Mês</option>
                                                            <option value="ano">Por Ano</option>
                                                        </select>
                                                        <div v-if="form.errors['regras_limite.periodo']" class="form-error">{{ form.errors['regras_limite.periodo'] }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            {{ isEditing ? 'Atualizar Serviço' : 'Salvar Serviço' }}
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
.badge-permission { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300; }
.badge-active { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300; }
.badge-inactive { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-300; }
.badge-limit { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300; }
.badge-juridico { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300; }
.badge-online { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-500/10 dark:text-cyan-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2; @apply focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400; @apply disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all; @apply bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4; @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400; @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500; @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400; @apply dark:focus:ring-green-500 dark:focus:border-green-500; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
.form-checkbox { @apply h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600; }
</style>
