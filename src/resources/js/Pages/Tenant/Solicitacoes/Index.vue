<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Head, router, Link } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import { Plus, Eye, Trash2, ClipboardList, X } from 'lucide-vue-next';

const props = defineProps({
    solicitacoes: Object,
    cidadaos: Array,
    servicos: Array,
    statusDisponiveis: Array,
    exigirRendaJuridico: Boolean, // Prop para controle
});

const isModalOpen = ref(false);

const form = useForm({
    user_id: '',
    servico_id: '',
    observacoes: '',
    renda_familiar: '', // Novo campo para a renda
});

// Lógica para mostrar o campo de renda dinamicamente
const servicoSelecionado = computed(() => {
    return props.servicos.find(s => s.id === form.servico_id);
});

// --- LÓGICA CORRIGIDA ---
const mostrarCampoRenda = computed(() => {
    // Mostra o campo apenas se o parâmetro global estiver ativo
    // E o serviço selecionado for do tipo jurídico.
    return props.exigirRendaJuridico && servicoSelecionado.value?.is_juridico;
});

// Observa a mudança do cidadão e pré-preenche a renda se já existir
watch(() => form.user_id, (newUserId) => {
    if (newUserId) {
        const cidadao = props.cidadaos.find(c => c.id === newUserId);
        if (cidadao && cidadao.profile_data && cidadao.profile_data.renda_familiar) {
            form.renda_familiar = cidadao.profile_data.renda_familiar;
        } else {
            form.renda_familiar = '';
        }
    }
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submit = () => {
    const routeName = 'admin.solicitacoes.store';
    const options = {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    };
    form.post(route(routeName), options);
};

const deleteSolicitacao = (solicitacao) => {
    if (confirm('Tem certeza que deseja remover esta solicitação?')) {
        router.delete(route('admin.solicitacoes.destroy', solicitacao), {
            preserveScroll: true,
        });
    }
};

const getStatusStyle = (cor) => {
    const hex = cor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    const textColor = brightness > 125 ? 'black' : 'white';
    return { backgroundColor: cor, color: textColor };
};

// Adição para depuração: Loga o tema atual no console
const isDark = computed(() => document.documentElement.classList.contains('dark'));
watch(isDark, (newVal) => {
    console.log('Tema atual:', newVal ? 'Dark' : 'Light');
});
</script>

<template>
    <Head title="Solicitações de Serviço" />

    <TenantLayout title="Solicitações de Serviço">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Solicitações de Serviço
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><ClipboardList :size="32" class="icon-in-badge" /></div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Fila de Atendimento</h2>
                        <p class="form-subtitle">Crie e gerencie as solicitações dos cidadãos.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <button @click="openModal" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Nova Solicitação
                        </button>
                    </div>
                </div>

                <div class="p-4 md:p-6">
                    <div v-if="solicitacoes.data.length > 0" class="space-y-4">
                        <div v-for="solicitacao in solicitacoes.data" :key="solicitacao.id" class="role-card">
                            <div class="flex-1">
                                <p class="role-name">{{ solicitacao.servico.nome }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Solicitante: <span class="font-semibold">{{ solicitacao.cidadao.name }}</span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                                    Atendente: <span class="font-semibold">{{ solicitacao.atendente ? solicitacao.atendente.name : 'N/A' }}</span>
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span :style="getStatusStyle(solicitacao.status.cor)" class="badge-base">
                                        {{ solicitacao.status.nome }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <Link :href="route('admin.solicitacoes.show', solicitacao.id)" class="table-action-btn hover:text-sky-600 dark:hover:text-sky-400" title="Ver Detalhes">
                                    <Eye class="w-5 h-5" />
                                </Link>
                                <button @click="deleteSolicitacao(solicitacao)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Solicitação"><Trash2 class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">Nenhuma solicitação encontrada.</p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="solicitacoes.links" />
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
                                            <span>Criar Nova Solicitação</span>
                                            <button @click="closeModal" type="button" class="table-action-btn"><X class="w-5 h-5" /></button>
                                        </DialogTitle>

                                        <div class="mt-6 space-y-6">
                                            <div>
                                                <label for="cidadao" class="form-label">Cidadão</label>
                                                <select v-model="form.user_id" id="cidadao" class="form-input" required>
                                                    <option disabled value="">Selecione um cidadão</option>
                                                    <option v-for="cidadao in cidadaos" :key="cidadao.id" :value="cidadao.id">{{ cidadao.name }} - {{ cidadao.cpf || 'CPF não informado' }}</option>
                                                </select>
                                                <div v-if="form.errors.user_id" class="form-error">{{ form.errors.user_id }}</div>
                                            </div>

                                            <div>
                                                <label for="servico" class="form-label">Serviço Solicitado</label>
                                                <select v-model="form.servico_id" id="servico" class="form-input" required>
                                                    <option disabled value="">Selecione um serviço</option>
                                                    <option v-for="servico in servicos" :key="servico.id" :value="servico.id">{{ servico.nome }}</option>
                                                </select>
                                                <div v-if="form.errors.servico_id" class="form-error">{{ form.errors.servico_id }}</div>
                                            </div>

                                            <!-- CAMPO DE RENDA DINÂMICO -->
                                            <div v-if="mostrarCampoRenda">
                                                <label for="renda_familiar" class="form-label">Renda Familiar do Cidadão (R$)</label>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    v-model="form.renda_familiar"
                                                    id="renda_familiar"
                                                    class="form-input"
                                                    placeholder="Necessário para serviços jurídicos"
                                                    :required="exigirRendaJuridico"
                                                >
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Este valor será salvo ou atualizado no perfil do cidadão.</p>
                                                <div v-if="form.errors.renda_familiar" class="form-error">{{ form.errors.renda_familiar }}</div>
                                            </div>

                                            <div>
                                                <label for="observacoes" class="form-label">Observações Iniciais (Opcional)</label>
                                                <textarea v-model="form.observacoes" id="observacoes" rows="3" class="form-input"></textarea>
                                                <div v-if="form.errors.observacoes" class="form-error">{{ form.errors.observacoes }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-3 rounded-b-2xl">
                                        <button type="button" @click="closeModal" class="btn-secondary">Cancelar</button>
                                        <button type="submit" :disabled="form.processing" class="btn-primary">
                                            Registrar Solicitação
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
/* Estilos consistentes com seus outros componentes, com adição explícita para light */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.role-card { @apply bg-white dark:bg-white/5 p-5 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between transition hover:shadow-md hover:border-gray-300 dark:hover:border-white/20; }
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400 disabled:opacity-50; }
.btn-secondary { @apply inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.modal-panel { @apply w-full max-w-2xl transform overflow-hidden rounded-2xl text-left align-middle shadow-xl transition-all bg-white dark:bg-gray-800; }
.form-label { @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1; }
.form-input { @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5 px-4 bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 dark:focus:ring-green-500 dark:focus:border-green-500; }
textarea.form-input { @apply h-auto; }
.form-error { @apply text-sm text-red-600 dark:text-red-400 mt-1; }
</style>
