<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js';
import { TrendingUp } from 'lucide-vue-next';

// Registro dos componentes do Chart.js
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

const props = defineProps({
    tendenciasChartData: Object,
    filtros: Object,
});

// Formulário para os filtros de data
const form = useForm({
    data_inicio: props.filtros.data_inicio || new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
    data_fim: props.filtros.data_fim || new Date().toISOString().split('T')[0],
});

// Função para buscar os dados com os filtros aplicados
const buscarDados = () => {
    form.get(route('tenant.relatorios.analise-de-tendencias'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Cores dinâmicas para as linhas do gráfico
const colors = ['#4A90E2', '#50E3C2', '#F5A623', '#BD10E0', '#9013FE', '#D0021B', '#F8E71C', '#7ED321'];

// Dados processados para o componente de gráfico
const chartData = computed(() => {
    if (!props.tendenciasChartData || !props.tendenciasChartData.datasets) {
        return { labels: [], datasets: [] };
    }

    const labels = props.tendenciasChartData.labels.map(date => new Date(date).toLocaleDateString('pt-BR', { timeZone: 'UTC' }));

    const datasets = props.tendenciasChartData.datasets.map((dataset, index) => {
        const dataPoints = props.tendenciasChartData.labels.map(labelDate => dataset.data[labelDate] || 0);
        const color = colors[index % colors.length];

        return {
            label: dataset.label,
            backgroundColor: color,
            borderColor: color,
            data: dataPoints,
            fill: false,
            tension: 0.1
        };
    });

    return {
        labels: labels,
        datasets: datasets,
    };
});

// Opções de configuração do gráfico
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Evolução de Solicitações por Tipo de Serviço',
            font: {
                size: 16,
            }
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};
</script>

<template>
    <Head title="Relatório - Análise de Tendências" />

    <TenantLayout title="Análise de Tendências">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Análise de Tendências
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><TrendingUp :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Análise de Tendências</h2>
                        <p class="form-subtitle">Visualize a evolução das solicitações por serviço ao longo do tempo.</p>
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4 pb-6">
                    <form @submit.prevent="buscarDados" class="flex flex-col md:flex-row gap-4 items-end mt-4">
                        <div class="w-full md:w-1/3">
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                            <input
                                id="data_inicio"
                                v-model="form.data_inicio"
                                type="date"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            />
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                            <input
                                id="data_fim"
                                v-model="form.data_fim"
                                type="date"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            />
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="btn-primary w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Analisar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                    <div v-if="tendenciasChartData && tendenciasChartData.datasets && tendenciasChartData.datasets.length > 0">
                        <div class="h-[32rem]">
                            <Line :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                    <div v-else class="text-center py-16">
                         <p class="text-gray-500 dark:text-gray-400">
                            Nenhuma tendência encontrada para o período selecionado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
/* Estilos importados diretamente do modelo para consistência */
.content-container { @apply relative w-full pt-16 rounded-3xl shadow-xl transition-all duration-300 bg-white border border-gray-200 dark:bg-[#102C26]/60 dark:border-2 dark:border-green-400/25 dark:backdrop-blur-sm; }
.border-b-dynamic { @apply border-b border-gray-200 dark:border-green-400/10; }
.border-t-dynamic { @apply border-t border-gray-200 dark:border-green-400/10; }
.form-icon { @apply absolute -top-8 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full flex justify-center items-center shadow-lg bg-emerald-600 shadow-emerald-500/30 dark:bg-[#43DB9E] dark:shadow-green-400/30; }
.icon-in-badge { @apply text-white; }
.header-title { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.form-subtitle { @apply text-sm mt-1 text-gray-500 dark:text-gray-400; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400 disabled:opacity-50; }
</style>
