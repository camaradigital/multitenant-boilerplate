<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import {
    Users, ClipboardList, Clock, Star, BarChart3, ListChecks, ArrowRight, PlusCircle, Settings,
} from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    kpis: Object,
    atendimentosChartData: Object,
    solicitacoesRecentes: Array,
});

const page = usePage();
const tenant = page.props.tenant;

// --- Reatividade para Dark Mode ---
const isDarkMode = ref(false);
onMounted(() => {
    const observer = new MutationObserver(() => isDarkMode.value = document.documentElement.classList.contains('dark'));
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDarkMode.value = document.documentElement.classList.contains('dark');
});

const getInitials = (name) => (!name ? '??' : name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase());

const actionCards = [
    { title: 'Novo Atendimento', description: 'Inicie um novo registro de serviço.', href: route('admin.solicitacoes.index'), icon: PlusCircle, color: 'emerald' },
    { title: 'Gerir Funcionários', description: 'Adicione, edite ou remova usuários.', href: route('admin.funcionarios.index'), icon: Users, color: 'blue' },
    { title: 'Gerir Serviços', description: 'Configure os tipos de solicitação.', href: route('admin.servicos.index'), icon: ClipboardList, color: 'purple' },
];

const metricCards = computed(() => [
    { title: 'Atendimentos Hoje', value: props.kpis.atendimentosHoje, icon: ClipboardList, color: 'sky' },
    { title: 'Solicitações Pendentes', value: props.kpis.solicitacoesPendentes, icon: Clock, color: 'yellow' },
    { title: 'Tempo Médio (7d)', value: `${props.kpis.tempoMedioFinalizacao}h`, icon: Clock, color: 'rose' },
    { title: 'Satisfação Média (30d)', value: `${props.kpis.notaMediaSatisfacao} / 5`, icon: Star, color: 'emerald' },
]);

// --- Configuração do Gráfico ---
const chartData = computed(() => {
    const color = isDarkMode.value ? '#34D399' : '#10B981'; // emerald-400 / emerald-600
    return {
        labels: props.atendimentosChartData.labels,
        datasets: [{
            label: 'Atendimentos Realizados',
            borderColor: color,
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, `${color}40`);
                gradient.addColorStop(1, `${color}00`);
                return gradient;
            },
            pointBackgroundColor: color,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            data: props.atendimentosChartData.values,
        }],
    };
});

const chartOptions = computed(() => {
    const textColor = isDarkMode.value ? '#9ca3af' : '#6b7280'; // gray-400 / gray-500
    const gridColor = isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0, color: textColor },
                grid: { color: gridColor },
            },
            x: {
                grid: { display: false },
                ticks: { color: textColor },
            },
        },
        interaction: { intersect: false, mode: 'index' },
    };
});
</script>

<template>
    <Head title="Dashboard" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Bem-vindo(a), {{ page.props.auth.user.name }}! 👋
                    </h2>
                    <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                        Visão geral do seu painel de controle para a <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ tenant.name }}</span>.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="action in actionCards" :key="action.title" :href="action.href"
                          class="group flex items-center p-5 rounded-xl border transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                          :class="{
                              'bg-emerald-50 dark:bg-emerald-900/50 border-emerald-200 dark:border-emerald-500/30 hover:border-emerald-300 dark:hover:border-emerald-500/50': action.color === 'emerald',
                              'bg-blue-50 dark:bg-blue-900/50 border-blue-200 dark:border-blue-500/30 hover:border-blue-300 dark:hover:border-blue-500/50': action.color === 'blue',
                              'bg-purple-50 dark:bg-purple-900/50 border-purple-200 dark:border-purple-500/30 hover:border-purple-300 dark:hover:border-purple-500/50': action.color === 'purple'
                          }">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center shadow-md mr-4 flex-shrink-0"
                             :class="{
                                'bg-emerald-600 dark:bg-emerald-500': action.color === 'emerald',
                                'bg-blue-600 dark:bg-blue-500': action.color === 'blue',
                                'bg-purple-600 dark:bg-purple-500': action.color === 'purple'
                             }">
                            <component :is="action.icon" class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-bold" :class="{
                                'text-emerald-900 dark:text-emerald-200': action.color === 'emerald',
                                'text-blue-900 dark:text-blue-200': action.color === 'blue',
                                'text-purple-900 dark:text-purple-200': action.color === 'purple'
                            }">{{ action.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ action.description }}</p>
                        </div>
                        <ArrowRight class="ml-auto h-5 w-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                    :class="{
                                        'text-emerald-500': action.color === 'emerald',
                                        'text-blue-500': action.color === 'blue',
                                        'text-purple-500': action.color === 'purple'
                                    }"/>
                    </Link>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Métricas Principais</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div v-for="metric in metricCards" :key="metric.title"
                             class="p-5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.title }}</p>
                                <div class="p-2 rounded-full" :class="{
                                    'bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400': metric.color === 'sky',
                                    'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400': metric.color === 'yellow',
                                    'bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400': metric.color === 'rose',
                                    'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400': metric.color === 'emerald',
                                }">
                                    <component :is="metric.icon" class="h-5 w-5" />
                                </div>
                            </div>
                            <p class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2 text-left">{{ metric.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="lg:col-span-2 p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Atendimentos nos Últimos 7 Dias</h3>
                        <div class="h-80">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Ações Rápidas</h3>
                            <div class="space-y-2">
                                <Link :href="route('admin.relatorios.atendimentos')" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                    <BarChart3 class="w-5 h-5 mr-3 text-emerald-500"/>
                                    <span class="font-medium text-sm text-gray-700 dark:text-gray-300">Relatório de Atendimentos</span>
                                    <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"/>
                                </Link>
                                <Link :href="route('admin.relatorios.satisfacao')" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                    <Star class="w-5 h-5 mr-3 text-yellow-500"/>
                                    <span class="font-medium text-sm text-gray-700 dark:text-gray-300">Relatório de Satisfação</span>
                                    <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"/>
                                </Link>
                                <Link :href="route('admin.parametros.index')" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                    <Settings class="w-5 h-5 mr-3 text-slate-500"/>
                                    <span class="font-medium text-sm text-gray-700 dark:text-gray-300">Configurações Gerais</span>
                                    <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"/>
                                </Link>
                            </div>
                        </div>

                        <div class="p-6 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 shadow-sm">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Solicitações Pendentes</h3>
                            <div v-if="solicitacoesRecentes.length > 0" class="space-y-2 max-h-48 overflow-y-auto">
                                <Link v-for="solicitacao in solicitacoesRecentes" :key="solicitacao.id" :href="route('admin.solicitacoes.show', solicitacao.id)" class="group flex items-center w-full p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mr-3">
                                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ getInitials(solicitacao.cidadao.name) }}</span>
                                    </div>
                                    <div class="flex-grow overflow-hidden">
                                        <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ solicitacao.servico.nome }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">#{{ solicitacao.id }} • {{ solicitacao.cidadao.name }}</p>
                                    </div>
                                    <ArrowRight class="w-4 h-4 ml-auto text-gray-400 opacity-0 group-hover:opacity-100 transition-all flex-shrink-0"/>
                                </Link>
                            </div>
                            <p v-else class="text-sm text-center text-gray-500 dark:text-gray-400 py-8">Nenhuma solicitação pendente no momento. ✨</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
