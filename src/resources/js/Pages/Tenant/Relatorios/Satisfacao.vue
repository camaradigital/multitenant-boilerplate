<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import KpiCard from '@/Components/KpiCard.vue';
import StarRating from '@/Components/StarRating.vue'; // 1. Importar o novo componente reutilizável
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    pesquisas: Object,
    tiposServico: Array,
    funcionarios: Array,
    filtros: Object,
    estatisticas: Object,
});

const form = useForm({
    data_inicio: props.filtros.data_inicio || '',
    data_fim: props.filtros.data_fim || '',
    tipo_servico_id: props.filtros.tipo_servico_id || '',
    funcionario_id: props.filtros.funcionario_id || '',
});

const filtrar = () => {
    form.get(route('admin.relatorios.satisfacao'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limparFiltros = () => {
    form.reset();
    filtrar();
};

const chartData = computed(() => {
    const labels = props.estatisticas.distribuicaoNotas ? Object.keys(props.estatisticas.distribuicaoNotas) : [];
    const data = props.estatisticas.distribuicaoNotas ? Object.values(props.estatisticas.distribuicaoNotas) : [];

    return {
        labels: labels.map(label => `${label} Estrela(s)`),
        datasets: [{
            label: 'Total de Avaliações',
            backgroundColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'],
            borderColor: ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'],
            borderRadius: 4,
            data: data,
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: { precision: 0 },
        }
    }
};

// 2. A definição do StarRating em JSX foi REMOVIDA daqui.

</script>

<template>
    <Head title="Relatório de Satisfação" />

    <TenantLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Painel de Satisfação do Cidadão
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <KpiCard title="Nota Média Geral" :value="estatisticas.notaMedia" unit="/ 5" colorClass="text-emerald-700 dark:text-emerald-200" />
                    <KpiCard title="Total de Respostas" :value="estatisticas.totalRespostas" unit="no período" colorClass="text-sky-700 dark:text-sky-200" />
                    <KpiCard title="Taxa de Resposta" :value="estatisticas.taxaResposta" unit="%" colorClass="text-amber-700 dark:text-amber-200" />
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <form @submit.prevent="filtrar">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Início</label>
                                <input type="date" id="data_inicio" v-model="form.data_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            </div>
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Fim</label>
                                <input type="date" id="data_fim" v-model="form.data_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            </div>
                            <div>
                                <label for="tipo_servico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Serviço</label>
                                <select id="tipo_servico_id" v-model="form.tipo_servico_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Todos</option>
                                    <option v-for="tipo in tiposServico" :key="tipo.id" :value="tipo.id">{{ tipo.nome }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Funcionário</label>
                                <select id="funcionario_id" v-model="form.funcionario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                    <option value="">Todos</option>
                                    <option v-for="func in funcionarios" :key="func.id" :value="func.id">{{ func.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4 space-x-3">
                            <SecondaryButton type="button" @click="limparFiltros" :disabled="form.processing">Limpar</SecondaryButton>
                            <PrimaryButton :disabled="form.processing">Analisar</PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Distribuição das Notas</h3>
                        <div class="h-80 relative">
                             <Bar v-if="pesquisas.total > 0" :data="chartData" :options="chartOptions" />
                             <p v-else class="text-center text-gray-500 pt-16">Nenhuma avaliação no período.</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Comentários Recentes</h3>
                        <div class="space-y-4">
                            <p v-if="pesquisas.data.length === 0" class="text-center text-gray-500 pt-16">Nenhum comentário no período.</p>
                            <div v-for="pesquisa in pesquisas.data" :key="pesquisa.id" class="border-b border-gray-200 dark:border-gray-700 pb-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ pesquisa.cidadao.name }}</p>
                                        <p class="text-xs text-gray-500">Serviço: {{ pesquisa.solicitacao_servico.servico.nome }}</p>
                                    </div>
                                    <!-- 3. Usando o componente StarRating importado, que não usa JSX -->
                                    <StarRating :rating="pesquisa.nota" />
                                </div>
                                <p v-if="pesquisa.comentario" class="mt-2 text-sm text-gray-600 dark:text-gray-400 italic">"{{ pesquisa.comentario }}"</p>
                            </div>
                        </div>
                         <Pagination class="mt-6" :links="pesquisas.links" v-if="pesquisas.data.length > 0"/>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<style scoped>
    .kpi-card { @apply bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center; }
    .kpi-title { @apply text-sm font-medium text-gray-600 dark:text-gray-400; }
    .kpi-value { @apply text-3xl font-extrabold text-gray-900 dark:text-white mt-1; }
    .kpi-unit { @apply text-lg font-semibold text-gray-500 dark:text-gray-500; }
</style>

