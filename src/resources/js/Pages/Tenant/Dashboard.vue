<script setup>
import { computed } from 'vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Users,
    ClipboardList,
    Clock,
    Star,
    BarChart3,
    ListChecks,
    ArrowRight,
    PlusCircle,
    Settings,
} from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

// Registra os componentes necessários do Chart.js
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

// Define as novas props que vêm do DashboardController aprimorado
const props = defineProps({
    kpis: Object,
    atendimentosChartData: Object,
    solicitacoesRecentes: Array,
});

const page = usePage();
const tenant = page.props.tenant;

// Detecta o tema dark
const isDark = computed(() => {
    if (typeof window === 'undefined') return false;
    return window.document.documentElement.classList.contains('dark');
});

// Mapeamento de cores para classes completas do Tailwind, do design original.
const colorMap = {
    emerald: {
        card: 'bg-emerald-50 dark:bg-emerald-900/50 border-emerald-200 dark:border-emerald-500/30',
        icon: 'bg-emerald-600 dark:bg-emerald-500',
        text: 'text-emerald-900 dark:text-emerald-200',
        arrow: 'text-emerald-500',
        metricIcon: 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400',
    },
    blue: {
        card: 'bg-blue-50 dark:bg-blue-900/50 border-blue-200 dark:border-blue-500/30',
        icon: 'bg-blue-600 dark:bg-blue-500',
        text: 'text-blue-900 dark:text-blue-200',
        arrow: 'text-blue-500',
        metricIcon: 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400',
    },
    purple: {
        card: 'bg-purple-50 dark:bg-purple-900/50 border-purple-200 dark:border-purple-500/30',
        icon: 'bg-purple-600 dark:bg-purple-500',
        text: 'text-purple-900 dark:text-purple-200',
        arrow: 'text-purple-500',
        metricIcon: 'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400',
    },
    sky: {
        metricIcon: 'bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400',
    },
    yellow: {
        metricIcon: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400',
    },
    rose: {
        metricIcon: 'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400',
    },
    slate: {
        metricIcon: 'bg-slate-100 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400',
    }
};

// Mapeamento específico para classes de cartões de métricas no tema claro (sem dark para permitir override)
const metricCardClasses = {
    sky: 'bg-sky-50 border-sky-200',
    yellow: 'bg-yellow-50 border-yellow-200',
    rose: 'bg-rose-50 border-rose-200',
    emerald: 'bg-emerald-50 border-emerald-200',
};

// Cartões de ação do design original
const actionCards = [
    {
        title: 'Novo Atendimento',
        description: 'Inicie um novo registro de serviço.',
        href: route('admin.solicitacoes.index'),
        icon: PlusCircle,
        color: 'emerald'
    },
    {
        title: 'Gerir Funcionários',
        description: 'Adicione, edite ou remova usuários.',
        href: route('admin.funcionarios.index'),
        icon: Users,
        color: 'blue'
    },
    {
        title: 'Gerir Serviços',
        description: 'Configure os tipos de solicitação.',
        href: route('admin.servicos.index'),
        icon: ClipboardList,
        color: 'purple'
    },
];

// --- CORREÇÃO 1: A lista de métricas agora contém apenas as 4 principais. ---
const metricCards = computed(() => [
    { title: 'Atendimentos Hoje', value: props.kpis.atendimentosHoje, icon: ClipboardList, color: 'sky' },
    { title: 'Solicitações Pendentes', value: props.kpis.solicitacoesPendentes, icon: Clock, color: 'yellow' },
    { title: 'Tempo Médio (7d)', value: `${props.kpis.tempoMedioFinalizacao}h`, icon: Clock, color: 'rose' },
    { title: 'Satisfação Média (30d)', value: `${props.kpis.notaMediaSatisfacao} / 5`, icon: Star, color: 'emerald' },
]);


// Prepara os dados para o gráfico de linha de atendimentos
const chartData = computed(() => {
    const borderColor = isDark.value ? '#34D399' : '#10B981';
    const backgroundColor = isDark.value ? 'rgba(52, 211, 153, 0.1)' : 'rgba(16, 185, 129, 0.1)';
    const pointBorderColor = isDark.value ? '#1F2937' : '#fff';
    const pointHoverBackgroundColor = isDark.value ? '#1F2937' : '#fff';

    return {
        labels: props.atendimentosChartData.labels,
        datasets: [
            {
                label: 'Atendimentos Realizados',
                borderColor,
                backgroundColor,
                pointBackgroundColor: borderColor,
                pointBorderColor,
                pointHoverBackgroundColor,
                pointHoverBorderColor: borderColor,
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                data: props.atendimentosChartData.values,
            },
        ],
    };
});

// Opções de configuração para o gráfico
const chartOptions = computed(() => {
    const textColor = isDark.value ? '#D1D5DB' : '#6B7280';
    const gridColor = isDark.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: isDark.value ? '#1F2937' : '#fff',
                titleColor: isDark.value ? '#fff' : '#000',
                bodyColor: textColor,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    color: textColor,
                },
                grid: {
                    color: gridColor,
                    drawBorder: false,
                },
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: textColor,
                },
                border: {
                    color: gridColor,
                },
            },
        },
    };
});
</script>

<template>
    <Head title="Dashboard" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12 px-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cabeçalho de Boas-vindas -->
                <div class="p-6 md:px-8 text-left mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Bem-vindo(a), {{ page.props.auth.user.name }}! 👋
                    </h2>
                    <p class="text-base mt-2 text-gray-500 dark:text-gray-400">
                        Visão geral do seu painel de controle para a
                        <span class="font-semibold">{{ tenant.name }}</span>.
                    </p>
                </div>

                <!-- Cartões de Ação (Layout Original) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 px-6 md:px-8">
                    <Link v-for="action in actionCards" :key="action.title" :href="action.href"
                          class="action-card group" :class="colorMap[action.color].card">
                        <div class="action-icon" :class="colorMap[action.color].icon">
                            <component :is="action.icon" class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h3 class="action-title" :class="colorMap[action.color].text">{{ action.title }}</h3>
                            <p class="action-description">{{ action.description }}</p>
                        </div>
                        <span class="action-arrow" :class="colorMap[action.color].arrow">&rarr;</span>
                    </Link>
                </div>

                <!-- Cartões de Métrica (Layout Original com Novos Dados) -->
                <h3 class="px-6 md:px-8 text-xl font-bold text-gray-800 dark:text-gray-200 mb-5">
                    Métricas Principais
                </h3>
                <!-- --- CORREÇÃO 3: O grid agora tem 4 colunas em telas grandes, dando mais espaço. --- -->
                <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 px-6 md:px-8">
                    <component :is="metric.href ? Link : 'div'" v-for="metric in metricCards" :key="metric.title" :href="metric.href" class="stat-card" :class="metricCardClasses[metric.color]">
                        <div class="flex items-center">
                            <div class="stat-icon" :class="colorMap[metric.color].metricIcon">
                                <component :is="metric.icon" class="h-5 w-5" />
                            </div>
                            <p class="stat-title">{{ metric.title }}</p>
                        </div>
                        <p class="stat-value">{{ metric.value }}</p>
                    </component>
                </div>

                <!-- Nova Seção com Gráfico e Pendências -->
                <div class="mt-10 px-6 md:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 content-container p-6">
                            <h3 class="header-title mb-4">Atendimentos nos Últimos 7 Dias</h3>
                            <div class="h-80">
                                 <Line :data="chartData" :options="chartOptions" />
                            </div>
                        </div>

                        <div class="space-y-6">
                             <!-- --- CORREÇÃO 2: Ações Rápidas agora incluem os links de Relatórios e Configurações --- -->
                            <div class="content-container p-6">
                                <h3 class="header-title mb-4">Ações Rápidas</h3>
                                <div class="space-y-3">
                                    <Link :href="route('admin.relatorios.atendimentos')" class="action-link group">
                                        <BarChart3 class="w-5 h-5 mr-3 text-emerald-500"/>
                                        <span>Relatório de Atendimentos</span>
                                        <ArrowRight class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity"/>
                                    </Link>
                                    <Link :href="route('admin.relatorios.satisfacao')" class="action-link group">
                                        <Star class="w-5 h-5 mr-3 text-yellow-500"/>
                                        <span>Relatório de Satisfação</span>
                                        <ArrowRight class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity"/>
                                    </Link>
                                    <Link :href="route('admin.parametros.index')" class="action-link group">
                                        <Settings class="w-5 h-5 mr-3 text-slate-500"/>
                                        <span>Configurações Gerais</span>
                                        <ArrowRight class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity"/>
                                    </Link>
                                </div>
                            </div>
                            <div class="content-container p-6">
                                <h3 class="header-title mb-4">Solicitações Pendentes</h3>
                                <div v-if="solicitacoesRecentes.length > 0" class="space-y-4">
                                    <Link v-for="solicitacao in solicitacoesRecentes" :key="solicitacao.id" :href="route('admin.solicitacoes.show', solicitacao.id)" class="recent-item group">
                                        <div class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 rounded-full p-2 mr-4">
                                            <ListChecks class="w-5 h-5 text-gray-500 dark:text-gray-400"/>
                                        </div>
                                        <div class="flex-grow">
                                            <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">#{{ solicitacao.id }} - {{ solicitacao.servico.nome }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ solicitacao.cidadao.name }}</p>
                                        </div>
                                        <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-all"/>
                                    </Link>
                                </div>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-400 mt-4">Nenhuma solicitação pendente no momento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos do design original */
.action-card { @apply flex items-center p-6 rounded-2xl border transition-all duration-300 hover:shadow-lg hover:border-transparent hover:-translate-y-1; }
.action-icon { @apply w-12 h-12 rounded-full flex items-center justify-center shadow-md mr-5 flex-shrink-0; }
.action-title { @apply text-lg font-bold; }
.action-description { @apply text-sm text-gray-600 dark:text-gray-400; }
.action-arrow { @apply ml-auto text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300; }
.stat-card { @apply p-5 rounded-2xl shadow-sm border border-transparent dark:border-green-400/10 dark:bg-[#102C26]/60 transition-all duration-300 hover:shadow-md hover:-translate-y-1; }
.stat-icon { @apply w-10 h-10 rounded-full flex items-center justify-center mr-4; }
.stat-title { @apply text-sm font-medium text-gray-600 dark:text-gray-400; }
.stat-value { @apply text-3xl font-extrabold text-gray-900 dark:text-white mt-2 text-left; }

/* Novos estilos para os novos componentes */
.content-container { @apply w-full rounded-3xl shadow-xl transition-all duration-300; @apply bg-white border border-gray-200; @apply dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.header-title { @apply text-xl font-bold text-gray-900 dark:text-white; }
.recent-item { @apply flex items-center w-full p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors; }
.action-link { @apply flex items-center w-full p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors; }
</style>
