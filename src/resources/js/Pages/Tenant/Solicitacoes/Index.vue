<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
// Importação de todos os ícones necessários, incluindo os novos
import {
    Eye,
    ClipboardList,
    Plus,
    Search,
    ListChecks,
    Hourglass,
    CheckCircle,
    FileText,
    XCircle,
    User,
    PlayCircle, // NOVO: Ícone para iniciar atendimento
    UserCheck,  // NOVO: Ícone para atendimento ativo
} from 'lucide-vue-next';

// --- TIPAGEM ATUALIZADA PARA REFLETIR A NOVA ESTRUTURA DE DADOS ---
interface Auth {
    user: {
        roles: string[];
    };
}

interface Solicitacao {
    id: number;
    servico: { nome: string };
    cidadao: { name: string };
    atendente: { name: string } | null;
    status: { nome: string; cor: string };
    can: { view: boolean; delete: boolean };
    created_at: string; // Adicionado para exibir a data/hora de entrada na fila
}

interface PaginationData {
    data: Solicitacao[];
    links: object[];
}

interface Stat {
    nome: string;
    contagem: number;
    cor: string;
    icone: string;
}

// --- PROPS ATUALIZADAS ---
// O backend agora envia 'atendimentoAtual', 'proximaSolicitacao' e 'filaRestante'
const props = withDefaults(defineProps<{
    atendimentoAtual?: Solicitacao | null;      // NOVO: O que o usuário já está atendendo
    proximaSolicitacao?: Solicitacao | null;   // NOVO: O próximo item livre na fila
    filaRestante: PaginationData;              // ALTERADO: O nome mudou de 'solicitacoes'
    categorias: { id: number; nome: string }[];
    statuses: { id: number; nome: string }[];
    filters: { search?: string; status?: string; categoria?: string };
    estatisticas?: Stat[];
}>(), {
    estatisticas: () => ([]),
});

const confirmingSolicitacaoDeletion = ref(false);
const solicitacaoToDelete = ref<Solicitacao | null>(null);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const page = usePage<{ auth: Auth }>();
const userRoles = computed(() => page.props.auth.user?.roles || []);
const pageTitle = computed(() => userRoles.value.includes('Advogado Coordenador') ? 'Supervisão Jurídica' : 'Fila de Atendimento');
const selectedCategoriaName = computed(() => props.filters.categoria || 'Geral');

// --- FILTROS ---
const applyFilters = (newFilters = {}) => {
    const query = {
        categoria: props.filters.categoria,
        search: search.value,
        status: status.value,
        ...newFilters,
    };
    const cleanQuery = Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== '' && value !== null && value !== undefined)
    );

    router.get(route('admin.solicitacoes.index'), cleanQuery, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// ALTERADO: Agora seleciona a fila, limpando outros filtros para clareza
const selecionarFila = (categoriaNome: string) => {
    search.value = '';
    status.value = '';
    applyFilters({
        categoria: categoriaNome === 'Todos' ? undefined : categoriaNome,
        status: undefined,
        search: undefined
    });
}

const applySearchAndStatusFilters = () => applyFilters();

const filterByStat = (statName: string) => {
    const newStatus = statName === 'Total' ? undefined : statName;
    status.value = newStatus || '';
    search.value = ''; // Limpa a busca ao clicar na estatística
    applyFilters({ status: newStatus, categoria: props.filters.categoria, search: undefined });
};


// --- AÇÕES ---
// NOVO: Função para iniciar o atendimento
const iniciarAtendimento = (solicitacaoId: number) => {
    router.post(route('admin.solicitacoes.atender', solicitacaoId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // O Inertia recarregará os props e o backend redirecionará para a página de detalhes
        },
        onError: (errors: any) => {
            // Opcional: Adicionar notificação de erro (ex: com vue-toastification)
            alert(errors.message || 'Não foi possível iniciar o atendimento. A solicitação pode já ter sido atendida.');
        }
    });
};

const confirmSolicitacaoDeletion = (solicitacao: Solicitacao) => {
    solicitacaoToDelete.value = solicitacao;
    confirmingSolicitacaoDeletion.value = true;
};
const deleteSolicitacao = () => {
    if (!solicitacaoToDelete.value) return;
    router.delete(route('admin.solicitacoes.destroy', solicitacaoToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
const closeModal = () => {
    confirmingSolicitacaoDeletion.value = false;
    solicitacaoToDelete.value = null;
};

// --- UTILS ---
const iconMap = {
    ListChecks, Hourglass, CheckCircle, FileText, XCircle,
};
const getIconComponent = (iconName: string) => iconMap[iconName as keyof typeof iconMap] || ClipboardList;

const filteredStats = computed(() => {
    const requiredNames = ['Total', 'Andamento', 'Concluído', 'Cancelado'];
    return props.estatisticas.filter(stat => requiredNames.includes(stat.nome));
});

const getStatusStyle = (cor?: string) => {
    const defaultStyle = { backgroundColor: '#e5e7eb', color: '#1f2937' };
    if (!cor) return defaultStyle;
    try {
        const hex = cor.startsWith('#') ? cor.slice(1) : cor;
        if (hex.length !== 6) return defaultStyle;
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return { backgroundColor: cor, color: brightness > 180 ? '#1f2937' : '#ffffff' };
    } catch (e) {
        return defaultStyle;
    }
};

const getInitials = (name?: string | null) => {
    if (!name) return '??';
    const parts = name.split(' ');
    if (parts.length > 1) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return (parts[0].substring(0, 2)).toUpperCase();
};

// NOVO: Formatador de data para exibir a hora de chegada na fila
const formatDateTime = (dateString: string) => {
    if (!dateString) return 'Data indisponível';
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Fila de Atendimento" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Solicitações
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl rounded-2xl transition-all duration-300">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/50">
                        <ClipboardList :size="32" class="text-white" />
                    </div>

                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pt-12 p-6 border-b border-gray-100 dark:border-gray-800/50">
                        <div>
                            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ pageTitle }}</h2>
                            <p class="mt-1 text-base text-gray-500 dark:text-gray-400">Atenda o próximo da fila ou gerencie as solicitações.</p>
                        </div>
                        <Link :href="route('admin.solicitacoes.create')" class="flex-shrink-0 inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 shadow-md hover:shadow-lg shadow-emerald-500/30">
                            <Plus class="h-5 w-5 mr-1.5" />
                            Nova Solicitação
                        </Link>
                    </div>

                    <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div v-for="stat in filteredStats" :key="stat.nome"
                            class="p-3 rounded-xl border border-transparent flex flex-col items-start transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
                            :style="{ backgroundColor: stat.cor, color: getStatusStyle(stat.cor).color }" @click="filterByStat(stat.nome)">
                            <component :is="getIconComponent(stat.icone)" class="h-5 w-5 mb-1 opacity-80" />
                            <p class="text-xs font-semibold opacity-90">{{ stat.nome }}</p>
                            <p class="text-2xl font-extrabold mt-0.5">{{ stat.contagem }}</p>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border-y border-gray-100 dark:border-gray-800">
                        <nav class="flex space-x-4 overflow-x-auto pb-4 -mb-4" aria-label="Tabs">
                            <button @click="selecionarFila('Todos')"
                                :class="['whitespace-nowrap pb-2 px-1 border-b-2 font-semibold text-sm transition-colors', !filters.categoria ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                                Todas as Solicitações
                            </button>
                            <button v-for="categoria in categorias" :key="categoria.id" @click="selecionarFila(categoria.nome)"
                                :class="['whitespace-nowrap pb-2 px-1 border-b-2 font-semibold text-sm transition-colors', filters.categoria === categoria.nome ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300']">
                                Fila {{ categoria.nome }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6 space-y-6">
                        <div v-if="atendimentoAtual" class="bg-blue-50 dark:bg-blue-900/50 border-2 border-dashed border-blue-400 rounded-xl p-5 text-center">
                             <UserCheck class="mx-auto h-12 w-12 text-blue-500 mb-3" />
                             <h3 class="text-xl font-bold text-gray-900 dark:text-white">Seu Atendimento Atual</h3>
                             <p class="text-gray-600 dark:text-gray-300 mt-1">Você está atendendo: <strong class="dark:text-white">{{ atendimentoAtual.cidadao.name }}</strong> para o serviço <strong class="dark:text-white">{{ atendimentoAtual.servico.nome }}</strong>.</p>
                             <Link :href="route('admin.solicitacoes.show', atendimentoAtual.id)" class="mt-4 inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm transition-all bg-blue-600 text-white hover:bg-blue-700 shadow-md hover:shadow-lg shadow-blue-500/30">
                                 <Eye class="h-4 w-4 mr-1.5" />
                                 Continuar Atendimento
                             </Link>
                        </div>

                        <div v-else-if="proximaSolicitacao" class="bg-emerald-50 dark:bg-emerald-900/50 border-2 border-dashed border-emerald-400 rounded-xl p-5 text-center">
                             <PlayCircle class="mx-auto h-12 w-12 text-emerald-500 mb-3" />
                             <h3 class="text-xl font-bold text-gray-900 dark:text-white">Próximo da Fila: <span class="text-emerald-500">{{ selectedCategoriaName }}</span></h3>
                             <p class="text-gray-600 dark:text-gray-300 mt-1">
                                 O próximo cidadão é <strong>{{ proximaSolicitacao.cidadao.name }}</strong> para o serviço <strong>{{ proximaSolicitacao.servico.nome }}</strong>.
                             </p>
                             <p class="text-sm text-gray-500 dark:text-gray-400">Entrou na fila em: {{ formatDateTime(proximaSolicitacao.created_at) }}</p>

                             <button @click="iniciarAtendimento(proximaSolicitacao.id)" class="mt-4 inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 shadow-md hover:shadow-lg shadow-emerald-500/30">
                                 <PlayCircle class="h-4 w-4 mr-1.5" />
                                 Iniciar Atendimento
                             </button>
                        </div>

                         <div v-else-if="!atendimentoAtual && filters.categoria" class="text-center py-10">
                            <CheckCircle class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
                            <h3 class="mt-6 text-xl font-bold text-gray-900 dark:text-white">Fila "{{ filters.categoria }}" Vazia!</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">Ótimo trabalho! Não há ninguém aguardando atendimento nesta fila.</p>
                        </div>

                         <div v-else-if="!atendimentoAtual && !filters.categoria" class="text-center py-10">
                            <ClipboardList class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" />
                            <h3 class="mt-6 text-xl font-bold text-gray-900 dark:text-white">Selecione uma Fila</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">Clique em uma das filas acima para ver o próximo atendimento disponível.</p>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 dark:border-gray-800/50">
                         <div class="pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ filters.categoria ? 'Restante na Fila' : 'Todas as Solicitações' }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ filters.categoria ? 'Lista das próximas solicitações aguardando atendimento.' : 'Visão geral de todas as solicitações no sistema.' }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center pb-6">
                            <div class="md:col-span-2">
                                <label for="search-filter" class="sr-only">Buscar</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><Search class="h-5 w-5 text-gray-400" /></div>
                                    <input type="text" id="search-filter" v-model="search" @keyup.enter="applySearchAndStatusFilters" class="block w-full rounded-xl border-gray-300 pl-10 pr-4 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 transition-colors" placeholder="Buscar por cidadão, atendente ou serviço...">
                                </div>
                            </div>
                            <div>
                                <label for="status-filter" class="sr-only">Filtrar por Status</label>
                                <select id="status-filter" v-model="status" @change="applySearchAndStatusFilters" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                    <option value="">Todos os Status</option>
                                    <option v-for="st in statuses" :key="st.id" :value="st.nome">{{ st.nome }}</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="filaRestante.data.length === 0" class="text-center py-10">
                            <ClipboardList class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                            <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Nenhuma solicitação encontrada</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajuste os filtros ou crie uma nova solicitação para começar.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="solicitacao in filaRestante.data" :key="solicitacao.id" class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col sm:flex-row items-start justify-between gap-4 transition-shadow duration-200 hover:shadow-md dark:hover:shadow-gray-700/50">
                                <div class="flex items-start gap-4 flex-grow">
                                     <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center ring-2 ring-gray-500/20">
                                        <span class="text-base font-bold text-gray-600 dark:text-gray-300">{{ getInitials(solicitacao.cidadao.name) }}</span>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-3">
                                            <p class="font-extrabold text-lg text-gray-900 dark:text-white leading-tight">{{ solicitacao.servico.nome }}</p>
                                            <span :style="getStatusStyle(solicitacao.status.cor)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">{{ solicitacao.status.nome }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">#{{ solicitacao.id }} • <span class="font-semibold text-gray-700 dark:text-gray-300">{{ solicitacao.cidadao.name }}</span></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Atendente: <span class="font-medium">{{ solicitacao.atendente?.name || 'Aguardando' }}</span></p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Chegada: {{ formatDateTime(solicitacao.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 flex justify-end w-full sm:w-auto mt-2 sm:mt-0">
                                    <Link v-if="solicitacao.can?.view" :href="route('admin.solicitacoes.show', solicitacao.id)" class="inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold text-sm transition-all bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <Eye class="h-4 w-4 mr-1.5" />
                                        Ver Detalhes
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <Pagination class="pt-8" :links="filaRestante.links" v-if="filaRestante.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingSolicitacaoDeletion" @close="closeModal">
            <template #title>Excluir Solicitação</template>
            <template #content>
                <p class="text-gray-600 dark:text-gray-400">Tem certeza que deseja excluir a solicitação **#{{ solicitacaoToDelete?.id }}**? Esta ação é irreversível e removerá todos os dados associados.</p>
            </template>
            <template #footer>
                <button @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">Cancelar</button>
                <button @click="deleteSolicitacao" class="ml-3 inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-bold text-sm transition-all bg-red-600 text-white hover:bg-red-700 shadow-md hover:shadow-lg shadow-red-500/30">
                    Excluir Definitivamente
                </button>
            </template>
        </ConfirmationModal>
    </TenantLayout>
</template>
