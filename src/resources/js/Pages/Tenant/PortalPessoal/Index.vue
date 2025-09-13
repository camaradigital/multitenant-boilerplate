<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import {
    FileText, Eye, PlusCircle, Star, MessageSquareText, Search,
    Filter, X, History, RefreshCw, BarChart2,
    Clock, CheckCircle, Package
} from 'lucide-vue-next';
import Chart from 'chart.js/auto';

const props = defineProps({
    solicitacoes: Object,
    canCreateOnlineSolicitacao: Boolean,
    todosOsStatus: { type: Array, default: () => [] },
});

const searchQuery = ref('');
const filterStatus = ref('todos');
const isLoading = ref(false);
const showAvaliarModal = ref(false);
const solicitacaoAvaliar = ref(null);
const showHistoricoModal = ref(false);
const historicoSolicitacao = ref(null);
const showEstatisticasModal = ref(false);

const formAvaliacao = useForm({
    nota: 0,
    comentario: '',
});

const statusCounts = computed(() => {
    return props.solicitacoes.data.reduce((acc, solicitacao) => {
        const statusName = solicitacao.status.nome;
        if (!acc[statusName]) {
            acc[statusName] = 0;
        }
        acc[statusName]++;
        return acc;
    }, {});
});

const filteredSolicitacoes = computed(() => {
    let filtered = props.solicitacoes.data;

    if (filterStatus.value !== 'todos') {
        const isFinal = filterStatus.value === 'finalizados';
        filtered = filtered.filter(s => s.status.is_final === isFinal);
    }

    if (searchQuery.value) {
        const lowerQuery = searchQuery.value.toLowerCase();
        filtered = filtered.filter(s =>
            s.servico.nome.toLowerCase().includes(lowerQuery) ||
            s.id.toString().includes(searchQuery.value) ||
            (s.status.nome && s.status.nome.toLowerCase().includes(lowerQuery))
        );
    }

    return filtered;
});

const totalSolicitacoes = computed(() => props.solicitacoes.data.length);
const emAndamentoCount = computed(() => props.solicitacoes.data.filter(s => !s.status.is_final).length);
const finalizadasCount = computed(() => props.solicitacoes.data.filter(s => s.status.is_final).length);

let chartInstance = null;
const createChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }
    const ctx = document.getElementById('estatisticasChart');
    if (ctx) {
        const data = {
            labels: ['Em Andamento', 'Finalizadas'],
            datasets: [{
                data: [emAndamentoCount.value, finalizadasCount.value],
                backgroundColor: ['#10B981', '#6B7280'],
                hoverOffset: 4
            }]
        };
        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                return `${label}: ${value}`;
                            }
                        }
                    }
                }
            }
        });
    }
};

const openAvaliarModal = (solicitacao) => {
    solicitacaoAvaliar.value = solicitacao;
    formAvaliacao.reset();
    showAvaliarModal.value = true;
};

const openHistoricoModal = (solicitacao) => {
    historicoSolicitacao.value = solicitacao;
    showHistoricoModal.value = true;
};

const submitAvaliacao = () => {
    if (!solicitacaoAvaliar.value) return;

    formAvaliacao.post(route('portal.solicitacoes.avaliar', solicitacaoAvaliar.value.id), {
        onSuccess: () => {
            const index = props.solicitacoes.data.findIndex(s => s.id === solicitacaoAvaliar.value.id);
            if (index !== -1) {
                props.solicitacoes.data[index].pesquisa_satisfacao = {
                    nota: formAvaliacao.nota,
                    comentario: formAvaliacao.comentario,
                };
            }
            showAvaliarModal.value = false;
        },
        preserveScroll: true,
    });
};

const getStatusStyle = (cor) => {
    if (!cor) return {};
    const hex = cor.replace('#', '');
    if (hex.length < 6) return { backgroundColor: cor, color: 'white' };
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    const textColor = brightness > 125 ? 'black' : 'white';
    return { backgroundColor: cor, color: textColor };
};

const getEventDescription = (event, properties) => {
    switch (event) {
        case 'updated':
            if (properties.attributes.status_id) {
                const status = props.todosOsStatus.find(st => st.id === properties.attributes.status_id);
                return `Status alterado para "${status ? status.nome : 'Desconhecido'}"`;
            }
            if (properties.attributes.atendente_id) {
                const atendenteNome = properties.attributes.atendente ? properties.attributes.atendente.name : 'Ninguém';
                return `Atendente atribuído: ${atendenteNome}`;
            }
            if (properties.attributes.observacoes) {
                return 'Nova observação adicionada.';
            }
            return 'Atualização no atendimento.';
        case 'created':
            return `Solicitação criada.`;
        default:
            return event;
    }
};

const refreshData = () => {
    isLoading.value = true;
    Inertia.reload({
        preserveState: true,
        onFinish: () => { isLoading.value = false; }
    });
};

watch(showEstatisticasModal, (newValue) => {
    if (newValue) {
        requestAnimationFrame(() => {
            createChart();
        });
    }
});

onMounted(() => {
    const solicitacaoNaoAvaliada = props.solicitacoes.data.find(
        (sol) => sol.status.is_final && !sol.pesquisa_satisfacao
    );
    if (solicitacaoNaoAvaliada) {
        openAvaliarModal(solicitacaoNaoAvaliada);
    }
});
</script>

<template>
    <Head title="Meu Painel" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Meu Painel
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="content-container p-6 md:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Minhas Solicitações</h3>
                            <p class="text-gray-500 dark:text-gray-400 mt-1">Acompanhe o status e histórico de todos os seus pedidos de serviço.</p>
                        </div>
                        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
                            <button @click="showEstatisticasModal = true" class="action-button flex items-center">
                                <BarChart2 class="w-5 h-5 mr-2" />
                                Ver Estatísticas
                            </button>
                            <Link v-if="canCreateOnlineSolicitacao" :href="route('portal.solicitacoes.create')" class="action-button">
                                <PlusCircle class="w-5 h-5 mr-2" />
                                Nova Solicitação
                            </Link>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-4 items-center">
                        <div class="relative w-full sm:w-auto flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <Search class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por serviço, ID ou status..."
                            class="form-input !pl-12"
                        />
                    </div>

                        <div class="flex w-full sm:w-auto p-1 bg-gray-200 dark:bg-gray-800 rounded-lg flex-shrink-0">
                            <button
                                @click="filterStatus = 'todos'"
                                :class="{ 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm': filterStatus === 'todos' }"
                                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors duration-200"
                            >
                                Todos
                            </button>
                            <button
                                @click="filterStatus = 'andamento'"
                                :class="{ 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm': filterStatus === 'andamento' }"
                                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors duration-200"
                            >
                                Em Andamento
                            </button>
                            <button
                                @click="filterStatus = 'finalizados'"
                                :class="{ 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm': filterStatus === 'finalizados' }"
                                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors duration-200"
                            >
                                Finalizados
                            </button>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div v-if="filteredSolicitacoes.length > 0" class="border-t border-gray-200 dark:border-gray-700 mt-4">
                            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <li v-for="solicitacao in filteredSolicitacoes" :key="solicitacao.id" class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4 py-5 sm:flex-nowrap transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                                            <FileText class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div>
                                            <p class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ solicitacao.servico.nome }}</p>
                                            <p class="text-sm text-gray-500">Solicitado em {{ new Date(solicitacao.created_at).toLocaleDateString('pt-BR') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex w-full flex-none justify-between gap-x-8 sm:w-auto items-center">
                                        <div class="flex flex-col items-end">
                                            <span class="text-sm leading-6 text-gray-500">Status</span>
                                            <span v-if="solicitacao.status" class="badge-base" :style="getStatusStyle(solicitacao.status.cor)">
                                                {{ solicitacao.status.nome }}
                                            </span>
                                            <div v-if="solicitacao.status.is_final" class="flex items-center mt-1">
                                                <template v-if="solicitacao.pesquisa_satisfacao">
                                                    <Star v-for="n in 5" :key="n" class="w-4 h-4" :class="{ 'text-yellow-400 fill-current': n <= solicitacao.pesquisa_satisfacao.nota, 'text-gray-300 dark:text-gray-600': n > solicitacao.pesquisa_satisfacao.nota }" />
                                                    <MessageSquareText v-if="solicitacao.pesquisa_satisfacao.comentario" class="w-4 h-4 text-gray-500 ml-1" title="Comentário disponível"/>
                                                </template>
                                                <template v-else>
                                                    <div class="flex items-center space-x-1 cursor-pointer hover:opacity-75" @click="openAvaliarModal(solicitacao)" title="Avaliar Atendimento">
                                                        <Star v-for="n in 5" :key="n" class="w-4 h-4 text-gray-300 dark:text-gray-600" />
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button @click="openHistoricoModal(solicitacao)" class="table-action-btn" title="Ver Histórico">
                                                <History class="w-5 h-5 text-gray-500" />
                                            </button>
                                            <Link :href="route('portal.solicitacoes.show', solicitacao.id)" class="table-action-btn" title="Ver Detalhes">
                                                <Eye class="w-5 h-5" />
                                            </Link>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-center py-16 text-gray-500 dark:text-gray-400">
                            <p class="text-lg">Nenhum resultado encontrado.</p>
                            <p class="text-sm mt-1">Tente ajustar os filtros ou a busca.</p>
                        </div>
                    </div>
                    <div v-if="props.solicitacoes.data.length > 0" class="mt-8">
                        <Pagination :links="props.solicitacoes.links" />
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showAvaliarModal" @close="showAvaliarModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Avaliar Atendimento</h3>
                <div v-if="solicitacaoAvaliar" class="bg-gray-100 dark:bg-gray-800/50 p-4 rounded-lg mt-4 mb-6 text-sm text-gray-700 dark:text-gray-300">
                    <p class="font-semibold">Solicitação #{{ solicitacaoAvaliar.id }}</p>
                    <p>Serviço: {{ solicitacaoAvaliar.servico?.nome }}</p>
                    <p>Data de Abertura: {{ new Date(solicitacaoAvaliar.created_at).toLocaleDateString('pt-BR') }}</p>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Sua opinião é muito importante! Avalie nosso serviço de 1 a 5 estrelas e adicione um comentário, se desejar.
                </p>
                <form @submit.prevent="submitAvaliacao">
                    <div class="mt-4 flex items-center space-x-1">
                        <template v-for="n in 5" :key="n">
                            <Star
                                @click="formAvaliacao.nota = n"
                                :class="{'text-yellow-400 fill-current': formAvaliacao.nota >= n, 'text-gray-300 dark:text-gray-600': formAvaliacao.nota < n}"
                                class="w-8 h-8 cursor-pointer transition-transform hover:scale-110"
                            />
                        </template>
                        <InputError :message="formAvaliacao.errors.nota" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <label for="comentario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comentário</label>
                        <textarea id="comentario" v-model="formAvaliacao.comentario" rows="3" class="form-input"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="showAvaliarModal = false" type="button" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none">
                            Cancelar
                        </button>
                        <button type="submit" class="action-button" :disabled="formAvaliacao.processing || formAvaliacao.nota === 0">
                            Enviar Avaliação
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showHistoricoModal" @close="showHistoricoModal = false">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Histórico do Atendimento</h3>
                    <button @click="showHistoricoModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <X class="w-6 h-6" />
                    </button>
                </div>
                <div v-if="historicoSolicitacao" class="bg-gray-100 dark:bg-gray-800/50 p-4 rounded-lg mt-4 mb-6 text-sm text-gray-700 dark:text-gray-300">
                    <p class="font-semibold">Solicitação #{{ historicoSolicitacao.id }}</p>
                    <p>Serviço: {{ historicoSolicitacao.servico?.nome }}</p>
                    <p>Data de Abertura: {{ new Date(historicoSolicitacao.created_at).toLocaleDateString('pt-BR') }}</p>
                </div>
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    <div v-if="historicoSolicitacao?.activity && historicoSolicitacao.activity.length > 0">
                        <div v-for="activity in historicoSolicitacao.activity" :key="activity.id" class="flex gap-4 p-4 border rounded-lg bg-white dark:bg-gray-900 dark:border-gray-700 transition-all duration-200 hover:shadow-md">
                            <div class="flex-shrink-0">
                                <History class="w-6 h-6 text-emerald-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ getEventDescription(activity.event, activity.properties) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Por {{ activity.causer?.name || 'Sistema' }} em {{ new Date(activity.created_at).toLocaleString('pt-BR') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
                        Nenhum histórico de atividade disponível para esta solicitação.
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showEstatisticasModal" @close="showEstatisticasModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Estatísticas das Solicitações</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Visão geral de todas as suas solicitações.
                </p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center gap-4">
                        <Package class="w-8 h-8 text-gray-500" />
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalSolicitacoes }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center gap-4">
                        <Clock class="w-8 h-8 text-emerald-600" />
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Em Andamento</p>
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ emAndamentoCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center gap-4">
                        <CheckCircle class="w-8 h-8 text-gray-600" />
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Finalizadas</p>
                            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ finalizadasCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Distribuição por Status</h4>
                    <div class="mt-4">
                        <canvas id="estatisticasChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </Modal>
    </TenantLayout>
</template>

<style scoped>
.content-container { @apply w-full rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.table-action-btn { @apply p-2 rounded-full transition-colors text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-white/10; }
.badge-base { @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shadow-sm; }
.action-button { @apply inline-flex items-center justify-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150; }
.form-input {
    @apply block w-full text-sm rounded-xl transition-all h-12 py-3.5;
    @apply bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400;
    @apply focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500;
    @apply dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:placeholder-gray-400;
    @apply dark:focus:ring-green-500 dark:focus:border-green-500;
}
.section-title { @apply text-xl font-bold text-gray-800 dark:text-gray-100 mb-2; }
</style>
