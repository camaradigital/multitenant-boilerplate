<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler } from 'chart.js';
import { TrendingUp, Award, CalendarDays, LineChart, Search, X } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler);

const props = defineProps({
    tendenciasChartData: Object,
    filtros: Object,
});

const form = useForm({
    data_inicio: props.filtros.data_inicio || new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
    data_fim: props.filtros.data_fim || new Date().toISOString().split('T')[0],
});

const buscarDados = () => {
    form.get(route('admin.relatorios.analise-de-tendencias'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    form.reset();
    buscarDados();
};

// --- Reatividade para Dark Mode ---
const isDarkMode = ref(false);
const textColor = computed(() => isDarkMode.value ? '#cbd5e1' : '#4b5563'); // slate-300 / gray-600
const gridColor = computed(() => isDarkMode.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)');

onMounted(() => {
    const observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDarkMode.value = document.documentElement.classList.contains('dark');
});

// --- Cores e Dados para o Gráfico ---
const colors = ['#4A90E2', '#50E3C2', '#F5A623', '#BD10E0', '#9013FE', '#D0021B', '#F8E71C', '#7ED321'];

const chartData = computed(() => {
    if (!props.tendenciasChartData?.datasets) return { labels: [], datasets: [] };

    const labels = props.tendenciasChartData.labels.map(date => new Date(date).toLocaleDateString('pt-BR', { timeZone: 'UTC' }));
    const datasets = props.tendenciasChartData.datasets.map((dataset, index) => {
        const dataPoints = props.tendenciasChartData.labels.map(labelDate => dataset.data[labelDate] || 0);
        const color = colors[index % colors.length];
        return {
            label: dataset.label,
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, `${color}40`);
                gradient.addColorStop(1, `${color}00`);
                return gradient;
            },
            borderColor: color,
            pointBackgroundColor: color,
            pointHoverRadius: 6,
            data: dataPoints,
            fill: true,
            tension: 0.3
        };
    });
    return { labels, datasets };
});

// --- Cálculos para KPIs e Tabela de Resumo ---
const dadosSumarizados = computed(() => {
    if (!chartData.value.datasets) return [];
    return chartData.value.datasets.map(dataset => ({
        label: dataset.label,
        total: dataset.data.reduce((sum, value) => sum + value, 0),
        color: dataset.borderColor,
    })).sort((a, b) => b.total - a.total);
});

const totalSolicitacoes = computed(() => dadosSumarizados.value.reduce((sum, item) => sum + item.total, 0));
const servicoDestaque = computed(() => dadosSumarizados.value[0] || null);
const picoDeDemandas = computed(() => {
    if (!chartData.value.labels.length) return null;
    let maxDemandas = 0;
    let diaPico = '';
    chartData.value.labels.forEach((label, index) => {
        const totalNoDia = chartData.value.datasets.reduce((sum, dataset) => sum + dataset.data[index], 0);
        if (totalNoDia > maxDemandas) {
            maxDemandas = totalNoDia;
            diaPico = label;
        }
    });
    return { dia: diaPico, total: maxDemandas };
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { color: textColor.value } },
        title: {
            display: true,
            text: 'Evolução de Solicitações por Tipo de Serviço',
            color: textColor.value,
            font: { size: 16, weight: 'bold' }
        },
    },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor.value }, grid: { color: gridColor.value } },
        x: { ticks: { color: textColor.value }, grid: { display: false } }
    },
    interaction: { intersect: false, mode: 'index' },
}));
</script>

<template>
    <Head title="Relatório - Análise de Tendências" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Análise de Tendências
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <TrendingUp :size="32" class="text-white" />
                    </div>

                    <div class="pt-12 p-6 text-center border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Análise de Tendências</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visualize a evolução das solicitações por serviço ao longo do tempo.</p>
                    </div>

                    <!-- Filtros -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                        <form @submit.prevent="buscarDados" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            <div class="lg:col-span-1">
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                                <input id="data_inicio" v-model="form.data_inicio" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"/>
                            </div>
                            <div class="lg:col-span-1">
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                                <input id="data_fim" v-model="form.data_fim" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"/>
                            </div>
                            <div class="lg:col-span-2 flex gap-2">
                                <button type="button" @click="limparFiltros" title="Limpar Filtros" class="inline-flex items-center justify-center p-2.5 h-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md shadow-sm transition-colors">
                                    <X class="h-5 w-5"/>
                                </button>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="form.processing">
                                    <Search class="w-4 h-4 mr-2"/>
                                    Analisar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Resultados -->
                    <div class="p-6">
                        <div v-if="!tendenciasChartData || !tendenciasChartData.datasets || tendenciasChartData.datasets.length === 0" class="text-center py-16">
                            <LineChart class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhum dado encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há tendências para o período selecionado. Tente ajustar os filtros.</p>
                        </div>
                        <div v-else class="space-y-8">
                            <!-- KPIs -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><TrendingUp class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total de Solicitações</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalSolicitacoes }}</p>
                                    </div>
                                </div>
                                <div v-if="servicoDestaque" class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><Award class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Serviço em Destaque</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ servicoDestaque.label }}</p>
                                    </div>
                                </div>
                                <div v-if="picoDeDemandas" class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><CalendarDays class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Pico de Demandas</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ picoDeDemandas.dia }} <span class="text-sm font-normal">({{ picoDeDemandas.total }} solicitações)</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráfico e Tabela de Resumo -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                                <div class="lg:col-span-2 h-[32rem]">
                                    <Line :data="chartData" :options="chartOptions" />
                                </div>
                                <div class="lg:col-span-1">
                                    <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">Resumo por Serviço</h3>
                                    <div class="space-y-2 max-h-[32rem] overflow-y-auto pr-2">
                                        <div v-for="item in dadosSumarizados" :key="item.label" class="p-3 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: item.color }"></span>
                                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ item.label }}</p>
                                            </div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ item.total }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

