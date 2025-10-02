<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { MapPin } from 'lucide-vue-next';

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

const topBairros = computed(() => props.demandas.slice(0, 10));

const chartData = computed(() => ({
    labels: topBairros.value.map(d => d.bairro),
    datasets: [{
        label: 'Total de Solicitações',
        backgroundColor: '#43DB9E',
        borderColor: '#43DB9E',
        borderWidth: 1,
        hoverBackgroundColor: '#36b382',
        hoverBorderColor: '#36b382',
        data: topBairros.value.map(d => d.total),
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: true,
            text: 'Top 10 Bairros com Mais Demandas',
            font: {
                size: 16,
            }
        }
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
    <Head title="Relatório - Mapeamento de Demandas" />

    <TenantLayout title="Mapeamento de Demandas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Mapeamento de Demandas por Bairro
            </h2>
        </template>

        <div class="flex justify-center items-start py-12 px-4">
            <div class="content-container w-full max-w-7xl">
                <div class="form-icon"><MapPin :size="32" class="icon-in-badge" /></div>

                <div class="p-6 border-b-dynamic">
                    <div>
                        <h2 class="header-title">Mapeamento de Demandas</h2>
                        <p class="form-subtitle">Analise a distribuição de solicitações por bairro e tipo de serviço.</p>
                    </div>
                </div>

                <div class="px-4 md:px-6 pt-4 pb-6">
                    <form @submit.prevent="buscarDados" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end mt-4">
                        <div class="w-full">
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                            <input
                                id="data_inicio"
                                v-model="form.data_inicio"
                                type="date"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            />
                        </div>
                        <div class="w-full">
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                            <input
                                id="data_fim"
                                v-model="form.data_fim"
                                type="date"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            />
                        </div>
                         <div class="w-full">
                            <label for="tipo_servico" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                            <select
                                id="tipo_servico"
                                v-model="form.tipo_servico_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                            >
                                <option value="">Todos</option>
                                <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                            </select>
                        </div>
                        <div class="w-full">
                            <button type="submit" class="btn-primary w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-4 md:p-6 border-t-dynamic">
                     <div v-if="demandas.length > 0">
                        <div>
                            <h3 class="role-name mb-4">Visualização Gráfica</h3>
                             <div class="h-96">
                                <Bar :data="chartData" :options="chartOptions" />
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="role-name mb-4">Dados Detalhados</h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-white/5">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bairro</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo de Serviço</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total de Solicitações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                                        <template v-for="demanda in demandas" :key="demanda.bairro">
                                            <tr v-for="(detalhe, index) in demanda.detalhes" :key="index">
                                                <td v-if="index === 0" :rowspan="demanda.detalhes.length" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 align-top">
                                                    {{ demanda.bairro }} (Total: {{ demanda.total }})
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ detalhe.tipo_servico }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ detalhe.total_solicitacoes }}</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-16">
                         <p class="text-gray-500 dark:text-gray-400">
                            Nenhum dado encontrado para os filtros selecionados.
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
.role-name { @apply text-lg font-bold text-emerald-800 dark:text-emerald-300; }
.btn-primary { @apply flex items-center justify-center px-4 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 dark:bg-[#43DB9E] dark:text-[#0A1E1C] dark:hover:bg-green-500 dark:focus:ring-green-400 disabled:opacity-50; }
</style>
