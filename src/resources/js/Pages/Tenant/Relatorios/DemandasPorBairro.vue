<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { MapPin, BarChart2, Users, Trophy, Search, X } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    demandas: Array,
    filtros: Object,
    tiposServico: Array,
});

const form = useForm({
    data_inicio: props.filtros.data_inicio || '',
    data_fim: props.filtros.data_fim || '',
    tipo_servico_id: props.filtros.tipo_servico_id || '',
});

const buscarDados = () => {
    form.get(route('tenant.relatorios.demandas-por-bairro'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    form.reset();
    buscarDados();
};

// --- Reatividade para Dark Mode no Gráfico ---
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

// --- Dados para o Gráfico e KPIs ---
const topBairros = computed(() => props.demandas.slice(0, 10));

const totalDemandas = computed(() => props.demandas.reduce((acc, bairro) => acc + bairro.total, 0));
const totalBairros = computed(() => props.demandas.length);
const bairroDestaque = computed(() => props.demandas.length > 0 ? props.demandas[0] : null);


const chartData = computed(() => ({
    labels: topBairros.value.map(d => d.bairro),
    datasets: [{
        label: 'Total de Solicitações',
        backgroundColor: '#43DB9E',
        borderRadius: 4,
        data: topBairros.value.map(d => d.total),
    }],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        title: {
            display: true,
            text: 'Top 10 Bairros com Mais Demandas',
            color: textColor.value,
            font: { size: 16, weight: 'bold' }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                color: textColor.value,
            },
            grid: { color: gridColor.value }
        },
        x: {
            ticks: { color: textColor.value },
            grid: { display: false }
        }
    }
}));
</script>

<template>
    <Head title="Relatório - Mapeamento de Demandas" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Mapeamento de Demandas por Bairro
            </h2>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                 <div class="relative bg-white dark:bg-gray-900/70 dark:backdrop-blur-sm border border-gray-200 dark:border-white/10 shadow-lg rounded-2xl">
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-16 h-16 bg-emerald-600 dark:bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <MapPin :size="32" class="text-white" />
                    </div>

                    <div class="pt-12 p-6 text-center border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Mapeamento de Demandas</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analise a distribuição de solicitações por bairro e tipo de serviço.</p>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                        <form @submit.prevent="buscarDados" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                                <input id="data_inicio" v-model="form.data_inicio" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"/>
                            </div>
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                                <input id="data_fim" v-model="form.data_fim" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"/>
                            </div>
                            <div class="lg:col-span-2">
                                <label for="tipo_servico" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                                <select id="tipo_servico" v-model="form.tipo_servico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Todos os tipos</option>
                                    <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="limparFiltros" title="Limpar Filtros" class="inline-flex items-center justify-center p-2.5 h-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md shadow-sm transition-colors">
                                    <X class="h-5 w-5"/>
                                </button>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50" :disabled="form.processing">
                                    <Search class="w-4 h-4 mr-2"/>
                                    Buscar
                                </button>
                            </div>
                        </form>
                    </div>

                     <div class="p-6">
                        <div v-if="demandas.length === 0" class="text-center py-16">
                            <BarChart2 class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Nenhum dado encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajuste os filtros ou aguarde novas solicitações para visualizar o relatório.</p>
                        </div>

                         <div v-else>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                                <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><BarChart2 class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total de Demandas</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalDemandas }}</p>
                                    </div>
                                </div>
                                 <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><Users class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Bairros Impactados</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalBairros }}</p>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-100 dark:bg-gray-800/50 rounded-lg flex items-center gap-4">
                                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-full text-emerald-600 dark:text-emerald-300"><Trophy class="h-6 w-6"/></div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Bairro em Destaque</p>
                                        <p class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ bairroDestaque.bairro }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div class="h-[500px]">
                                    <Bar :data="chartData" :options="chartOptions" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-300 mb-4">Dados Detalhados por Bairro</h3>
                                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                                        <div v-for="demanda in demandas" :key="demanda.bairro" class="p-4 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800/50">
                                            <div class="flex justify-between items-center">
                                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ demanda.bairro }}</p>
                                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Total: <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ demanda.total }}</span></p>
                                            </div>
                                            <ul class="mt-3 pt-3 border-t border-gray-200 dark:border-white/10 space-y-2 text-sm">
                                                <li v-for="(detalhe, index) in demanda.detalhes" :key="index" class="flex justify-between text-gray-600 dark:text-gray-400">
                                                    <span>{{ detalhe.tipo_servico }}</span>
                                                    <span class="font-medium">{{ detalhe.total_solicitacoes }}</span>
                                                </li>
                                            </ul>
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
