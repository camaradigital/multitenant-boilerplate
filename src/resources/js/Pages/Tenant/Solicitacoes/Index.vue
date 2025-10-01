<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Eye, Trash2, ClipboardList, Plus, Pencil, Search } from 'lucide-vue-next';

const props = defineProps({
    solicitacoes: Object,
    categorias: Array,
    statuses: Array, // Adicionado para receber os status do backend
    filters: Object,
});

// Lógica para o modal de exclusão
const confirmingSolicitacaoDeletion = ref(false);
const solicitacaoToDelete = ref(null);

// Variaveis reativas para os novos filtros
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

// Título da página dinâmico
const userRoles = computed(() => usePage().props.auth.user?.roles || []);
const pageTitle = computed(() => {
    if (userRoles.value.includes('Advogado Coordenador')) {
        return 'Supervisão de Solicitações Jurídicas';
    }
    return 'Fila de Atendimento';
});

// Função para aplicar todos os filtros de uma vez
const applyFilters = (newFilters = {}) => {
    // Começa com os filtros já aplicados e os valores atuais dos campos
    const query = {
        categoria: props.filters.categoria,
        search: search.value,
        status: status.value,
        ...newFilters,
    };

    // Remove chaves com valores vazios ou nulos para não poluir a URL
    Object.keys(query).forEach(key => {
        if (!query[key]) {
            delete query[key];
        }
    });

    router.get(route('admin.solicitacoes.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const filterByCategory = (categoria) => {
    applyFilters({ categoria: categoria === 'Todos' ? undefined : categoria });
};

const applySearchAndStatusFilters = () => {
    applyFilters();
};

const confirmSolicitacaoDeletion = (solicitacao) => {
    solicitacaoToDelete.value = solicitacao;
    confirmingSolicitacaoDeletion.value = true;
};

const deleteSolicitacao = () => {
    router.delete(route('admin.solicitacoes.destroy', solicitacaoToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingSolicitacaoDeletion.value = false;
            solicitacaoToDelete.value = null;
        }
    });
};

const getStatusStyle = (cor) => {
    if (!cor) return { backgroundColor: '#cccccc', color: 'black'};
    const hex = cor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    const textColor = brightness > 125 ? 'black' : 'white';
    return { backgroundColor: cor, color: textColor };
};
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
                        <h2 class="header-title">{{ pageTitle }}</h2>
                        <p class="form-subtitle">Visualize e gerencie as solicitações dos cidadãos.</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <Link :href="route('admin.solicitacoes.create')" class="btn-primary">
                            <Plus class="h-4 w-4 mr-2" />
                            Nova Solicitação
                        </Link>
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4">
                    <div class="border-b border-gray-200 dark:border-white/10">
                        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                            <button
                                @click="filterByCategory('Todos')"
                                :class="[
                                    !filters.categoria ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500',
                                    'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors'
                                ]"
                            >
                                Todas
                            </button>
                            <button
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                @click="filterByCategory(categoria.nome)"
                                :class="[
                                    filters.categoria === categoria.nome ? 'border-emerald-500 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500',
                                    'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors'
                                ]"
                            >
                                {{ categoria.nome }}
                            </button>
                        </nav>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-center mt-4">
                        <div class="w-full md:w-1/3">
                            <label for="status-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por Status</label>
                            <select
                                id="status-filter"
                                v-model="status"
                                @change="applySearchAndStatusFilters"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            >
                                <option value="">Todos os Status</option>
                                <option v-for="st in statuses" :key="st.id" :value="st.nome">
                                    {{ st.nome }}
                                </option>
                            </select>
                        </div>

                        <div class="w-full md:w-2/3">
                             <label for="search-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por Cidadão</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input
                                    type="text"
                                    id="search-filter"
                                    v-model="search"
                                    @keydown.enter="applySearchAndStatusFilters"
                                    class="flex-1 block w-full rounded-none rounded-l-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                    placeholder="Digite o nome do cidadão..."
                                />
                                <button
                                    @click="applySearchAndStatusFilters"
                                    class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-md bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <Search class="h-5 w-5" />
                                    <span class="ml-2 hidden sm:block">Buscar</span>
                                </button>
                            </div>
                        </div>
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
                                <Link v-if="solicitacao.can?.view" :href="route('admin.solicitacoes.show', solicitacao.id)" class="table-action-btn hover:text-sky-600 dark:hover:text-sky-400" title="Ver Detalhes">
                                    <Eye class="w-5 h-5" />
                                </Link>
                                <Link v-if="solicitacao.can?.update" :href="route('admin.solicitacoes.edit', solicitacao.id)" class="table-action-btn hover:text-blue-600 dark:hover:text-blue-400" title="Editar Solicitação">
                                    <Pencil class="w-5 h-5" />
                                </Link>
                                <button v-if="solicitacao.can?.delete" @click="confirmSolicitacaoDeletion(solicitacao)" class="table-action-btn hover:text-red-600 dark:hover:text-red-400" title="Excluir Solicitação">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">
                            Nenhuma solicitação encontrada com os filtros aplicados.
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-4">
                    <Pagination :links="solicitacoes.links" />
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="confirmingSolicitacaoDeletion"
            title="Excluir Solicitação"
            message="Tem certeza de que deseja excluir esta solicitação? Esta ação não pode ser desfeita."
            @close="confirmingSolicitacaoDeletion = false"
            @confirm="deleteSolicitacao"
        />
    </TenantLayout>
</template>

<style scoped>
/* Estilos permanecem os mesmos */
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
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
</style>
